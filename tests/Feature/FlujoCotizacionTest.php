<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlujoCotizacionTest extends TestCase
{
    use RefreshDatabase;

    private function makeWarehouse(): User
    {
        return $this->makeRoleUser('warehouse');
    }

    private function makeRoleUser(string $roleName): User
    {
        $role = \App\Models\Role::where('name', $roleName)->first();
        if (! $role) {
            $role = \App\Models\Role::create(['name' => $roleName]);
        }

        // Asegurar permisos del rol según el flujo de cotizaciones
        $permNames = match ($roleName) {
            'gerente_administrativo' => ['view quotations', 'approve quotations'],
            'subgerente_administrativo' => ['view quotations', 'pay quotations'],
            'warehouse' => ['create quotations', 'view quotations'],
            default => [],
        };
        foreach ($permNames as $pn) {
            $perm = \App\Models\Permission::firstOrCreate(['name' => $pn]);
            $role->givePermissionTo($perm);
        }

        $user = User::factory()->create();
        $user->syncRoles([$role->name]);

        return $user;
    }

    private function createQuotationViaForm(User $creator, Branch $branch): Quotation
    {
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create(['current_stock' => 0, 'average_cost' => 0]);

        \Livewire\Livewire::actingAs($creator)
            ->test(\App\Livewire\Bodega\QuotationCreate::class)
            ->call('selectSupplier', $supplier->id)
            ->call('selectProduct', $product->id)
            ->set('currentQuantity', 10)
            ->set('currentUnitCost', 19.99)
            ->call('addItem')
            ->call('save')
            ->assertSet('confirmingSave', true)
            ->call('confirmSave')
            ->assertRedirect(route('bodega.quotations.index'));

        return Quotation::first();
    }

    public function test_flujo_completo_cotizacion_hasta_recibir()
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $warehouse = $this->makeWarehouse();
        $warehouse->update(['branch_id' => $branch->id]);
        $gerente = $this->makeRoleUser('gerente_administrativo');
        $subgerente = $this->makeRoleUser('subgerente_administrativo');

        // 1. Bodeguero crea cotización → queda pendiente
        $quotation = $this->createQuotationViaForm($warehouse, $branch);
        $this->assertEquals('pending', $quotation->status);

        // 2. Gerente aprueba (con confirmación)
        \Livewire\Livewire::actingAs($gerente)
            ->test(\App\Livewire\Bodega\QuotationIndex::class)
            ->call('confirmApprove', $quotation->id)
            ->call('executeConfirmedAction')
            ->assertDispatched('show-toast', type: 'success');

        $quotation->refresh();
        $this->assertEquals('approved', $quotation->status);
        $this->assertNotNull($quotation->approved_at);

        // 3. Subgerente paga (con confirmación)
        \Livewire\Livewire::actingAs($subgerente)
            ->test(\App\Livewire\Bodega\QuotationIndex::class)
            ->call('confirmPay', $quotation->id)
            ->call('executeConfirmedAction')
            ->assertDispatched('show-toast', type: 'success');

        $quotation->refresh();
        $this->assertEquals('paid', $quotation->status);
        $this->assertNotNull($quotation->paid_at);

        // 4. Bodeguero recibe (con confirmación) → genera compra y entra stock
        \Livewire\Livewire::actingAs($warehouse)
            ->test(\App\Livewire\Bodega\QuotationIndex::class)
            ->call('confirmReceive', $quotation->id)
            ->call('executeConfirmedAction');

        $quotation->refresh();
        $this->assertEquals('received', $quotation->status);
        $this->assertNotNull($quotation->purchase_id);

        $this->assertDatabaseHas('purchases', ['id' => $quotation->purchase_id]);
        $this->assertDatabaseHas('movements', [
            'product_id' => $quotation->items()->first()->product_id,
            'type' => 'entry',
            'quantity' => 10,
            'reference_type' => 'purchase',
            'reference_id' => $quotation->purchase_id,
        ]);
    }

    public function test_no_puede_aprobar_sin_permiso()
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $warehouse = $this->makeWarehouse();
        $warehouse->update(['branch_id' => $branch->id]);

        $quotation = $this->createQuotationViaForm($warehouse, $branch);

        // Un warehouse no puede aprobar (solo gerente): la confirmación valida el permiso
        \Livewire\Livewire::actingAs($warehouse)
            ->test(\App\Livewire\Bodega\QuotationIndex::class)
            ->call('confirmApprove', $quotation->id)
            ->assertDispatched('show-toast', type: 'error');

        $this->assertEquals('pending', $quotation->fresh()->status);
    }

    public function test_no_se_puede_recibir_sin_estar_pagada()
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $warehouse = $this->makeWarehouse();
        $warehouse->update(['branch_id' => $branch->id]);

        $quotation = $this->createQuotationViaForm($warehouse, $branch);

        \Livewire\Livewire::actingAs($warehouse)
            ->test(\App\Livewire\Bodega\QuotationIndex::class)
            ->call('confirmReceive', $quotation->id)
            ->assertDispatched('show-toast', type: 'error');

        $this->assertEquals('pending', $quotation->fresh()->status);
        $this->assertNull($quotation->fresh()->purchase_id);
    }
}
