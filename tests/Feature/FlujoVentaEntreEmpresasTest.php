<?php

namespace Tests\Feature;

use App\Livewire\Bodega\IntercompanySaleCreate;
use App\Livewire\Bodega\IntercompanySaleShow;
use App\Models\Branch;
use App\Models\BranchInventory;
use App\Models\Company;
use App\Models\CompanyProductInventory;
use App\Models\IntercompanySale;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FlujoVentaEntreEmpresasTest extends TestCase
{
    use RefreshDatabase;

    private function createPendingSale(float $qty = 40, float $cost = 19.99): array
    {
        $sellerCompany = Company::factory()->create();
        $buyerCompany = Company::factory()->create();
        $seller = Branch::factory()->create(['company_id' => $sellerCompany->id, 'name' => 'Amayo']);
        $buyer = Branch::factory()->create(['company_id' => $buyerCompany->id, 'name' => 'Aguilares']);

        $product = Product::factory()->create([
            'current_stock' => 100,
            'average_cost' => $cost,
            'total_value' => 100 * $cost,
        ]);

        BranchInventory::factory()->create([
            'branch_id' => $seller->id,
            'product_id' => $product->id,
            'allocated_quantity' => 100,
        ]);

        CompanyProductInventory::create([
            'company_id' => $sellerCompany->id,
            'product_id' => $product->id,
            'quantity' => 100,
            'average_cost' => $cost,
            'total_value' => 100 * $cost,
        ]);

        Livewire::test(IntercompanySaleCreate::class)
            ->set('sellerBranchId', $seller->id)
            ->set('buyerBranchId', $buyer->id)
            ->call('selectProduct', $product->id)
            ->set('quantity', $qty)
            ->call('save')
            ->assertRedirect(route('bodega.intercompany-sales.index'));

        return [$seller, $buyer, $product, $sellerCompany, $buyerCompany];
    }

    public function test_crear_venta_no_mueve_stock_y_queda_pendiente()
    {
        $this->actingAs(User::factory()->create());

        [$seller, $buyer, $product] = $this->createPendingSale();

        // La venta queda pending
        $sale = IntercompanySale::first();
        $this->assertNotNull($sale);
        $this->assertEquals('pending', $sale->status);

        // El stock NO se movió al crear
        $sellerInv = BranchInventory::where('branch_id', $seller->id)->where('product_id', $product->id)->first();
        $this->assertEquals(100, (float) $sellerInv->allocated_quantity);

        $buyerInv = BranchInventory::where('branch_id', $buyer->id)->where('product_id', $product->id)->first();
        $this->assertNull($buyerInv);

        // Sin movimientos de Kardex todavía
        $this->assertDatabaseMissing('movements', [
            'reference_type' => 'intercompany_sale',
            'reference_id' => $sale->id,
        ]);
    }

    public function test_confirmar_venta_descueanta_vendedora_y_suma_compradora()
    {
        $this->actingAs(User::factory()->create());

        [$seller, $buyer, $product] = $this->createPendingSale(40);

        $sale = IntercompanySale::first();
        $this->assertEquals('pending', $sale->status);

        // Avanzar estados hasta delivered y confirmar
        $component = Livewire::test(IntercompanySaleShow::class, ['id' => $sale->id]);
        $component->call('markInTransit');
        $component->call('markDelivered');
        $component->call('confirm');

        $sale->refresh();
        $this->assertEquals('confirmed', $sale->status);

        // Vendedora baja de 100 a 60
        $sellerInv = BranchInventory::where('branch_id', $seller->id)->where('product_id', $product->id)->first();
        $this->assertEquals(60, (float) $sellerInv->allocated_quantity);

        // Compradora sube a 40
        $buyerInv = BranchInventory::where('branch_id', $buyer->id)->where('product_id', $product->id)->first();
        $this->assertNotNull($buyerInv);
        $this->assertEquals(40, (float) $buyerInv->allocated_quantity);

        // Movimientos creados
        $this->assertDatabaseHas('movements', [
            'product_id' => $product->id,
            'type' => 'exit',
            'branch_id' => $seller->id,
            'quantity' => 40,
            'reference_type' => 'intercompany_sale',
            'reference_id' => $sale->id,
        ]);
        $this->assertDatabaseHas('movements', [
            'product_id' => $product->id,
            'type' => 'entry',
            'branch_id' => $buyer->id,
            'quantity' => 40,
            'reference_type' => 'intercompany_sale',
            'reference_id' => $sale->id,
        ]);
    }

    public function test_no_se_puede_confirmar_sin_estar_entregado()
    {
        $this->actingAs(User::factory()->create());

        [$seller, $buyer, $product] = $this->createPendingSale(40);

        $sale = IntercompanySale::first();

        Livewire::test(IntercompanySaleShow::class, ['id' => $sale->id])
            ->call('confirm')
            ->assertDispatched('show-toast', type: 'error');

        // Stock intacto
        $sellerInv = BranchInventory::where('branch_id', $seller->id)->where('product_id', $product->id)->first();
        $this->assertEquals(100, (float) $sellerInv->allocated_quantity);
    }

    public function test_venta_entre_sucursales_de_la_misma_empresa_se_rechaza()
    {
        $this->actingAs(User::factory()->create());

        $company = Company::factory()->create();
        $branchA = Branch::factory()->create(['company_id' => $company->id]);
        $branchB = Branch::factory()->create(['company_id' => $company->id]);

        $product = Product::factory()->create(['current_stock' => 50]);

        BranchInventory::factory()->create([
            'branch_id' => $branchA->id,
            'product_id' => $product->id,
            'allocated_quantity' => 50,
        ]);

        Livewire::test(IntercompanySaleCreate::class)
            ->set('sellerBranchId', $branchA->id)
            ->set('buyerBranchId', $branchB->id)
            ->call('selectProduct', $product->id)
            ->set('quantity', 10)
            ->call('save')
            ->assertDispatched('show-toast', type: 'error');

        $this->assertNull(IntercompanySale::first());
    }

    public function test_venta_rechaza_stock_insuficiente_en_vendedora()
    {
        $this->actingAs(User::factory()->create());

        $sellerCompany = Company::factory()->create();
        $buyerCompany = Company::factory()->create();
        $seller = Branch::factory()->create(['company_id' => $sellerCompany->id]);
        $buyer = Branch::factory()->create(['company_id' => $buyerCompany->id]);

        $product = Product::factory()->create(['current_stock' => 10]);

        BranchInventory::factory()->create([
            'branch_id' => $seller->id,
            'product_id' => $product->id,
            'allocated_quantity' => 5,
        ]);

        Livewire::test(IntercompanySaleCreate::class)
            ->set('sellerBranchId', $seller->id)
            ->set('buyerBranchId', $buyer->id)
            ->call('selectProduct', $product->id)
            ->set('quantity', 40)
            ->call('save')
            ->assertDispatched('show-toast', type: 'error');

        $this->assertNull(IntercompanySale::first());
    }

    public function test_confirmar_actualiza_inventario_a_nivel_de_empresas()
    {
        $this->actingAs(User::factory()->create());

        [$seller, $buyer, $product, $sellerCompany, $buyerCompany] = $this->createPendingSale(50, 20);

        // La empresa compradora ya tenía 50 a $10
        CompanyProductInventory::create([
            'company_id' => $buyerCompany->id,
            'product_id' => $product->id,
            'quantity' => 50,
            'average_cost' => 10,
            'total_value' => 500,
        ]);

        $sale = IntercompanySale::first();

        Livewire::test(IntercompanySaleShow::class, ['id' => $sale->id])
            ->call('markInTransit')
            ->call('markDelivered')
            ->call('confirm');

        // Vendedora: 100 - 50 = 50
        $sellerCompanyInv = CompanyProductInventory::where('company_id', $sellerCompany->id)->where('product_id', $product->id)->first();
        $this->assertEquals(50, (float) $sellerCompanyInv->quantity);

        // Compradora: 50 a $10 + 50 a $20 = promedio $15
        $buyerCompanyInv = CompanyProductInventory::where('company_id', $buyerCompany->id)->where('product_id', $product->id)->first();
        $this->assertEquals(100, (float) $buyerCompanyInv->quantity);
        $this->assertEquals(15.0, (float) $buyerCompanyInv->average_cost);
    }

    public function test_no_se_puede_confirmar_dos_veces_la_misma_venta()
    {
        $this->actingAs(User::factory()->create());

        [$seller, $buyer, $product] = $this->createPendingSale(40);

        $sale = IntercompanySale::first();

        // Primera confirmación: exitosa
        $component = Livewire::test(IntercompanySaleShow::class, ['id' => $sale->id]);
        $component->call('markInTransit');
        $component->call('markDelivered');
        $component->call('confirm');

        $sale->refresh();
        $this->assertEquals('confirmed', $sale->status);

        // Intentar confirmar de nuevo: debe fallar y NO mover stock otra vez
        $component->call('confirm')
            ->assertDispatched('show-toast', type: 'error');

        $sellerInv = BranchInventory::where('branch_id', $seller->id)->where('product_id', $product->id)->first();
        $this->assertEquals(60, (float) $sellerInv->allocated_quantity);

        $buyerInv = BranchInventory::where('branch_id', $buyer->id)->where('product_id', $product->id)->first();
        $this->assertEquals(40, (float) $buyerInv->allocated_quantity);

        // Solo 1 movimiento de salida y 1 de entrada
        $this->assertDatabaseCount('movements', 2);
    }
}
