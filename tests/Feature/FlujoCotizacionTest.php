<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\BranchInventory;
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

    public function test_producto_propuesto_se_materializa_al_recibir()
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $warehouse = $this->makeWarehouse();
        $warehouse->update(['branch_id' => $branch->id]);
        $gerente = $this->makeRoleUser('gerente_administrativo');
        $subgerente = $this->makeRoleUser('subgerente_administrativo');

        $supplier = Supplier::factory()->create();
        $category = \App\Models\Category::factory()->create();

        // Crear cotización con producto propuesto (nuevo)
        \Livewire\Livewire::actingAs($warehouse)
            ->test(\App\Livewire\Bodega\QuotationCreate::class)
            ->call('selectSupplier', $supplier->id)
            ->call('activateCreateMode')
            ->set('newProductName', 'Antena Nova')
            ->set('newProductUnit', 'unidad')
            ->set('newProductCategoryId', $category->id)
            ->set('currentQuantity', 5)
            ->set('currentUnitCost', 30)
            ->call('addItem')
            ->call('save')
            ->assertSet('confirmingSave', true)
            ->call('confirmSave')
            ->assertRedirect(route('bodega.quotations.index'));

        $quotation = Quotation::first();
        $item = $quotation->items()->first();

        // Antes de recibir, el producto NO existe y el item es propuesto
        $this->assertTrue($item->isPending());
        $this->assertDatabaseMissing('products', ['name' => 'Antena Nova']);

        // Avanzar hasta recibir
        $comp = \Livewire\Livewire::actingAs($gerente)
            ->test(\App\Livewire\Bodega\QuotationIndex::class)
            ->call('confirmApprove', $quotation->id)
            ->call('executeConfirmedAction');

        \Livewire\Livewire::actingAs($subgerente)
            ->test(\App\Livewire\Bodega\QuotationIndex::class)
            ->call('confirmPay', $quotation->id)
            ->call('executeConfirmedAction');

        \Livewire\Livewire::actingAs($warehouse)
            ->test(\App\Livewire\Bodega\QuotationIndex::class)
            ->call('confirmReceive', $quotation->id)
            ->call('executeConfirmedAction');

        // Al recibir, el producto propuesto se materializó con su stock
        $this->assertDatabaseHas('products', ['name' => 'Antena Nova', 'unit_of_measure' => 'unidad']);

        $product = \App\Models\Product::where('name', 'Antena Nova')->first();
        $this->assertEquals(5, (float) $product->current_stock);

        $bi = BranchInventory::where('branch_id', $branch->id)->where('product_id', $product->id)->first();
        $this->assertEquals(5, (float) $bi->allocated_quantity);
    }

    public function test_cotizacion_multiple_agrupa_por_proveedor()
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $warehouse = $this->makeWarehouse();
        $warehouse->update(['branch_id' => $branch->id]);

        $supplierA = Supplier::factory()->create(['name' => 'Proveedor A']);
        $supplierB = Supplier::factory()->create(['name' => 'Proveedor B']);
        $product = Product::factory()->create(['current_stock' => 0, 'average_cost' => 0]);

        \Livewire\Livewire::actingAs($warehouse)
            ->test(\App\Livewire\Bodega\QuotationCreate::class)
            ->set('mode', 'multiple')
            // Proveedor A: producto 10 unidades
            ->call('selectSupplier', $supplierA->id)
            ->call('selectProduct', $product->id)
            ->set('currentQuantity', 10)
            ->set('currentUnitCost', 5)
            ->call('addItem')
            // Proveedor B: producto 20 unidades
            ->call('selectSupplier', $supplierB->id)
            ->call('selectProduct', $product->id)
            ->set('currentQuantity', 20)
            ->set('currentUnitCost', 7)
            ->call('addItem')
            ->call('save')
            ->assertSet('confirmingSave', true)
            ->call('confirmSave')
            ->assertRedirect(route('bodega.quotations.index'));

        // Se crearon 2 cotizaciones separadas
        $this->assertEquals(2, Quotation::count());

        $quotationA = Quotation::where('supplier_id', $supplierA->id)->first();
        $quotationB = Quotation::where('supplier_id', $supplierB->id)->first();

        $this->assertNotNull($quotationA);
        $this->assertNotNull($quotationB);

        // Cada una con su total correcto
        $this->assertEquals(1, $quotationA->items()->count());
        $this->assertEquals(10, (float) $quotationA->items()->first()->quantity);
        $this->assertEquals(1, $quotationB->items()->count());
        $this->assertEquals(20, (float) $quotationB->items()->first()->quantity);

        // Subtotales distintos por proveedor
        $this->assertEquals(50, (float) $quotationA->subtotal);  // 10 * 5
        $this->assertEquals(140, (float) $quotationB->subtotal); // 20 * 7
    }

    public function test_modo_individual_con_items_de_varios_proveedores_genera_una_sola_cotizacion()
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $warehouse = $this->makeWarehouse();
        $warehouse->update(['branch_id' => $branch->id]);

        $supplierA = Supplier::factory()->create(['name' => 'Proveedor A']);
        $supplierB = Supplier::factory()->create(['name' => 'Proveedor B']);
        $product = Product::factory()->create(['current_stock' => 0, 'average_cost' => 0]);

        \Livewire\Livewire::actingAs($warehouse)
            ->test(\App\Livewire\Bodega\QuotationCreate::class)
            // Primer item con proveedor A
            ->call('selectSupplier', $supplierA->id)
            ->call('selectProduct', $product->id)
            ->set('currentQuantity', 10)
            ->set('currentUnitCost', 5)
            ->call('addItem')
            // Cambia el proveedor a B y agrega otro item (aún en modo individual)
            ->call('selectSupplier', $supplierB->id)
            ->call('selectProduct', $product->id)
            ->set('currentQuantity', 20)
            ->set('currentUnitCost', 7)
            ->call('addItem')
            ->call('save')
            ->assertSet('confirmingSave', true)
            ->call('confirmSave')
            ->assertRedirect(route('bodega.quotations.index'));

        // Modo individual => UNA cotización al proveedor del encabezado (B), con ambos items
        $this->assertEquals(1, Quotation::count());
        $quotation = Quotation::first();
        $this->assertEquals($supplierB->id, $quotation->supplier_id);
        $this->assertEquals(2, $quotation->items()->count());
        $this->assertEquals(190, (float) $quotation->subtotal); // 10*5 + 20*7
    }

    public function test_editar_item_pendiente_actualiza_en_su_lugar()
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $warehouse = $this->makeWarehouse();
        $warehouse->update(['branch_id' => $branch->id]);

        $supplier = Supplier::factory()->create();
        $category = \App\Models\Category::factory()->create();

        $component = \Livewire\Livewire::actingAs($warehouse)
            ->test(\App\Livewire\Bodega\QuotationCreate::class)
            ->call('selectSupplier', $supplier->id)
            ->call('activateCreateMode')
            ->set('newProductName', 'Antena Nova')
            ->set('newProductUnit', 'unidad')
            ->set('newProductCategoryId', $category->id)
            ->set('currentQuantity', 5)
            ->set('currentUnitCost', 30)
            ->call('addItem');

        $this->assertCount(1, $component->get('items'));

        // Editar reabre el panel de producto nuevo con los datos precargados
        $component->call('editItem', 0)
            ->assertSet('createMode', true)
            ->assertSet('newProductName', 'Antena Nova')
            ->assertSet('currentQuantity', 5)
            ->set('currentQuantity', 8)
            ->set('currentUnitCost', 32)
            ->call('addItem');

        $items = $component->get('items');
        $this->assertCount(1, $items);
        $this->assertEquals(8, (float) $items[0]['quantity']);
        $this->assertEquals(32, (float) $items[0]['unit_cost']);
        $this->assertEquals('Antena Nova', $items[0]['pending_name']);
        $this->assertEquals('unidad', $items[0]['pending_unit']);
        $this->assertEquals($category->id, (int) $items[0]['pending_category_id']);
        $this->assertNull($component->get('editingIndex'));
        $this->assertFalse($component->get('createMode'));
    }

    public function test_editar_item_del_medio_no_pisa_los_siguientes()
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $warehouse = $this->makeWarehouse();
        $warehouse->update(['branch_id' => $branch->id]);

        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create(['current_stock' => 0, 'average_cost' => 0]);

        $component = \Livewire\Livewire::actingAs($warehouse)
            ->test(\App\Livewire\Bodega\QuotationCreate::class)
            ->call('selectSupplier', $supplier->id)
            ->call('selectProduct', $product->id)
            ->set('currentQuantity', 1)->set('currentUnitCost', 10)->call('addItem')
            ->call('selectProduct', $product->id)
            ->set('currentQuantity', 2)->set('currentUnitCost', 10)->call('addItem')
            ->call('selectProduct', $product->id)
            ->set('currentQuantity', 3)->set('currentUnitCost', 10)->call('addItem');

        $this->assertCount(3, $component->get('items'));

        // Editar el item del medio (índice 1) y actualizar su cantidad
        $component->call('editItem', 1)
            ->set('currentQuantity', 99)
            ->call('addItem');

        $items = $component->get('items');
        $this->assertCount(3, $items);
        $this->assertEquals(99, (float) $items[1]['quantity']);
        $this->assertEquals(3, (float) $items[2]['quantity']); // el siguiente NO se pierde
    }

    public function test_editar_en_modo_multiple_conserva_el_proveedor_del_item()
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $warehouse = $this->makeWarehouse();
        $warehouse->update(['branch_id' => $branch->id]);

        $supplierA = Supplier::factory()->create(['name' => 'Proveedor A']);
        $supplierB = Supplier::factory()->create(['name' => 'Proveedor B']);
        $product = Product::factory()->create(['current_stock' => 0, 'average_cost' => 0]);

        $component = \Livewire\Livewire::actingAs($warehouse)
            ->test(\App\Livewire\Bodega\QuotationCreate::class)
            ->set('mode', 'multiple')
            // Proveedor A: producto 10 unidades
            ->call('selectSupplier', $supplierA->id)
            ->call('selectProduct', $product->id)
            ->set('currentQuantity', 10)->set('currentUnitCost', 5)->call('addItem')
            // Proveedor B: producto 20 unidades (el encabezado queda en B)
            ->call('selectSupplier', $supplierB->id)
            ->call('selectProduct', $product->id)
            ->set('currentQuantity', 20)->set('currentUnitCost', 7)->call('addItem');

        // Editar el primer item (proveedor A) mientras el encabezado está en B
        $component->call('editItem', 0)
            ->set('currentQuantity', 12)
            ->call('addItem');

        $items = $component->get('items');
        $this->assertCount(2, $items);
        $this->assertEquals($supplierA->id, (int) $items[0]['supplier_id']);
        $this->assertEquals($supplierB->id, (int) $items[1]['supplier_id']);
        $this->assertEquals(12, (float) $items[0]['quantity']);
    }

    public function test_modo_multiple_no_agrega_sin_proveedor()
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $warehouse = $this->makeWarehouse();
        $warehouse->update(['branch_id' => $branch->id]);

        $product = Product::factory()->create(['current_stock' => 0, 'average_cost' => 0]);

        $component = \Livewire\Livewire::actingAs($warehouse)
            ->test(\App\Livewire\Bodega\QuotationCreate::class)
            ->set('mode', 'multiple')
            ->call('selectProduct', $product->id)
            ->set('currentQuantity', 10)
            ->set('currentUnitCost', 5)
            ->call('addItem')
            ->assertDispatched('show-toast', type: 'error');

        $this->assertCount(0, $component->get('items'));
    }

    public function test_no_puede_cambiar_a_individual_con_productos_de_varios_proveedores()
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $warehouse = $this->makeWarehouse();
        $warehouse->update(['branch_id' => $branch->id]);

        $supplierA = Supplier::factory()->create(['name' => 'Proveedor A']);
        $supplierB = Supplier::factory()->create(['name' => 'Proveedor B']);
        $product = Product::factory()->create(['current_stock' => 0, 'average_cost' => 0]);

        $component = \Livewire\Livewire::actingAs($warehouse)
            ->test(\App\Livewire\Bodega\QuotationCreate::class)
            ->set('mode', 'multiple')
            // Proveedor A: 5 unidades
            ->call('selectSupplier', $supplierA->id)
            ->call('selectProduct', $product->id)
            ->set('currentQuantity', 5)->set('currentUnitCost', 10)->call('addItem')
            // Proveedor B: 3 unidades
            ->call('selectSupplier', $supplierB->id)
            ->call('selectProduct', $product->id)
            ->set('currentQuantity', 3)->set('currentUnitCost', 10)->call('addItem');

        $this->assertCount(2, $component->get('items'));

        // Intentar pasar a individual debe bloquearse y NO perder items
        $component->call('switchMode', 'single')
            ->assertSet('mode', 'multiple')
            ->assertDispatched('show-toast', type: 'error');

        $this->assertCount(2, $component->get('items'));
    }

    public function test_cambiar_a_individual_con_un_solo_proveedor_conserva_ese_proveedor()
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $warehouse = $this->makeWarehouse();
        $warehouse->update(['branch_id' => $branch->id]);

        $supplierA = Supplier::factory()->create(['name' => 'Proveedor A']);
        $supplierB = Supplier::factory()->create(['name' => 'Proveedor B']);
        $product = Product::factory()->create(['current_stock' => 0, 'average_cost' => 0]);

        $component = \Livewire\Livewire::actingAs($warehouse)
            ->test(\App\Livewire\Bodega\QuotationCreate::class)
            ->set('mode', 'multiple')
            // Solo proveedor A (dos productos)
            ->call('selectSupplier', $supplierA->id)
            ->call('selectProduct', $product->id)
            ->set('currentQuantity', 2)->set('currentUnitCost', 10)->call('addItem')
            ->call('selectProduct', $product->id)
            ->set('currentQuantity', 3)->set('currentUnitCost', 10)->call('addItem');

        // Limpiar el encabezado para forzar la auto-selección del único proveedor
        $component->call('clearSupplier');

        $component->call('switchMode', 'single')
            ->assertSet('mode', 'single')
            ->assertSet('supplier_id', $supplierA->id);

        $items = $component->get('items');
        $this->assertCount(2, $items);
        foreach ($items as $item) {
            $this->assertEquals($supplierA->id, (int) $item['supplier_id']);
        }
    }

    public function test_cambiar_proveedor_en_individual_reasigna_todos_los_items()
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $warehouse = $this->makeWarehouse();
        $warehouse->update(['branch_id' => $branch->id]);

        $supplierA = Supplier::factory()->create(['name' => 'Proveedor A']);
        $supplierB = Supplier::factory()->create(['name' => 'Proveedor B']);
        $product = Product::factory()->create(['current_stock' => 0, 'average_cost' => 0]);

        $component = \Livewire\Livewire::actingAs($warehouse)
            ->test(\App\Livewire\Bodega\QuotationCreate::class)
            // Modo individual por defecto: proveedor A
            ->call('selectSupplier', $supplierA->id)
            ->call('selectProduct', $product->id)
            ->set('currentQuantity', 1)->set('currentUnitCost', 10)->call('addItem')
            ->call('selectProduct', $product->id)
            ->set('currentQuantity', 2)->set('currentUnitCost', 10)->call('addItem');

        // Cambiar el proveedor en individual => todos los items se re-asignan a B
        $component->call('selectSupplier', $supplierB->id)
            ->assertSet('supplier_id', $supplierB->id);

        $items = $component->get('items');
        $this->assertCount(2, $items);
        foreach ($items as $item) {
            $this->assertEquals($supplierB->id, (int) $item['supplier_id']);
        }
    }

    public function test_ver_detalle_de_cotizacion()
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $warehouse = $this->makeWarehouse();
        $warehouse->update(['branch_id' => $branch->id]);

        $quotation = $this->createQuotationViaForm($warehouse, $branch);

        $this->actingAs($warehouse)
            ->get(route('bodega.quotations.show', $quotation->id))
            ->assertOk()
            ->assertSee($quotation->code)
            ->assertSee('Pendiente');
    }

    private function createDraftViaForm(User $creator, Branch $branch): Quotation
    {
        $product = Product::factory()->create(['current_stock' => 0, 'average_cost' => 0]);

        \Livewire\Livewire::actingAs($creator)
            ->test(\App\Livewire\Bodega\QuotationCreate::class)
            ->call('selectProduct', $product->id)
            ->set('currentQuantity', 5)
            ->set('currentUnitCost', 12)
            ->call('addItem')
            ->set('notes', 'Esperando confirmación del proveedor')
            ->call('saveDraft')
            ->assertDispatched('show-toast', type: 'success');

        return Quotation::first();
    }

    public function test_guardar_borrador_sin_proveedor()
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $warehouse = $this->makeWarehouse();
        $warehouse->update(['branch_id' => $branch->id]);

        $draft = $this->createDraftViaForm($warehouse, $branch);

        $this->assertEquals('draft', $draft->status);
        $this->assertNull($draft->supplier_id);
        $this->assertEquals(1, $draft->items()->count());
        $this->assertEquals('Esperando confirmación del proveedor', $draft->notes);
    }

    public function test_no_guarda_borrador_multiple_con_varios_proveedores()
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $warehouse = $this->makeWarehouse();
        $warehouse->update(['branch_id' => $branch->id]);

        $supplierA = Supplier::factory()->create(['name' => 'Proveedor A']);
        $supplierB = Supplier::factory()->create(['name' => 'Proveedor B']);
        $product = Product::factory()->create(['current_stock' => 0, 'average_cost' => 0]);

        $component = \Livewire\Livewire::actingAs($warehouse)
            ->test(\App\Livewire\Bodega\QuotationCreate::class)
            ->set('mode', 'multiple')
            ->call('selectSupplier', $supplierA->id)
            ->call('selectProduct', $product->id)
            ->set('currentQuantity', 2)->set('currentUnitCost', 10)->call('addItem')
            ->call('selectSupplier', $supplierB->id)
            ->call('selectProduct', $product->id)
            ->set('currentQuantity', 3)->set('currentUnitCost', 10)->call('addItem')
            ->call('saveDraft')
            ->assertDispatched('show-toast', type: 'error');

        $this->assertEquals(0, Quotation::count());
    }

    public function test_abrir_borrador_en_edicion()
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $warehouse = $this->makeWarehouse();
        $warehouse->update(['branch_id' => $branch->id]);

        $draft = $this->createDraftViaForm($warehouse, $branch);

        $this->actingAs($warehouse)
            ->get(route('bodega.quotations.edit', $draft->id))
            ->assertOk()
            ->assertSee($draft->code)
            ->assertSee('Editar borrador');
    }

    public function test_enviar_borrador_a_aprobacion()
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $warehouse = $this->makeWarehouse();
        $warehouse->update(['branch_id' => $branch->id]);

        $product = Product::factory()->create(['current_stock' => 0, 'average_cost' => 0]);
        $supplier = Supplier::factory()->create();

        $component = \Livewire\Livewire::actingAs($warehouse)
            ->test(\App\Livewire\Bodega\QuotationCreate::class)
            ->call('selectProduct', $product->id)
            ->set('currentQuantity', 5)
            ->set('currentUnitCost', 12)
            ->call('addItem')
            ->set('notes', 'Esperando confirmación del proveedor')
            ->call('saveDraft')
            ->assertDispatched('show-toast', type: 'success');

        $draftId = $component->get('draftId');

        // El proveedor confirma: se completa el borrador y se envía a aprobación
        $component->call('selectSupplier', $supplier->id)
            ->set('notes', 'Proveedor confirmado')
            ->call('save')
            ->assertSet('confirmingSave', true)
            ->call('confirmSave')
            ->assertRedirect(route('bodega.quotations.index'));

        $quotation = Quotation::find($draftId);
        $this->assertEquals('pending', $quotation->status);
        $this->assertEquals($supplier->id, $quotation->supplier_id);
        $this->assertEquals(1, $quotation->items()->count());
        $this->assertEquals('Proveedor confirmado', $quotation->notes);
    }

    public function test_eliminar_borrador()
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $warehouse = $this->makeWarehouse();
        $warehouse->update(['branch_id' => $branch->id]);

        $draft = $this->createDraftViaForm($warehouse, $branch);

        \Livewire\Livewire::actingAs($warehouse)
            ->test(\App\Livewire\Bodega\QuotationIndex::class)
            ->call('askDeleteDraft', $draft->id)
            ->call('executeConfirmedAction')
            ->assertDispatched('show-toast', type: 'success');

        $this->assertDatabaseMissing('quotations', ['id' => $draft->id]);
        $this->assertDatabaseMissing('quotation_items', ['quotation_id' => $draft->id]);
    }

    public function test_borradores_solo_visibles_para_su_creador()
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $warehouse = $this->makeWarehouse();
        $warehouse->update(['branch_id' => $branch->id]);

        $draft = $this->createDraftViaForm($warehouse, $branch);

        // Otro rol con acceso a cotizaciones NO ve el borrador en el index
        $gerente = $this->makeRoleUser('gerente_administrativo');
        \Livewire\Livewire::actingAs($gerente)
            ->test(\App\Livewire\Bodega\QuotationIndex::class)
            ->assertDontSee($draft->code);

        // El creador sí lo ve en "Mis borradores"
        \Livewire\Livewire::actingAs($warehouse)
            ->test(\App\Livewire\Bodega\QuotationIndex::class)
            ->assertSee($draft->code);
    }
}
