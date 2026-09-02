<?php

namespace Tests\Feature\Livewire\Suppliers;

use App\Livewire\Suppliers\PurchaseForm;
use App\Models\Category;
use App\Models\PackagingType;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\UnitOfMeasure;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PurchaseFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_form()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(PurchaseForm::class)
            ->assertSee('Compra');
    }

    public function test_supplier_search()
    {
        $this->actingAs(User::factory()->create());

        Supplier::factory()->create(['name' => 'Mikrotik SV']);

        Livewire::test(PurchaseForm::class)
            ->set('supplierSearch', 'Mikro')
            ->assertCount('supplierResults', 1);
    }

    public function test_select_supplier()
    {
        $this->actingAs(User::factory()->create());

        $supplier = Supplier::factory()->create();

        Livewire::test(PurchaseForm::class)
            ->call('selectSupplier', $supplier->id)
            ->assertSet('supplier_id', $supplier->id);
    }

    public function test_product_search()
    {
        $this->actingAs(User::factory()->create());

        Product::factory()->create(['name' => 'Router']);

        Livewire::test(PurchaseForm::class)
            ->set('currentProductSearch', 'Rout')
            ->assertCount('productSearchResults', 1);
    }

    public function test_select_product()
    {
        $this->actingAs(User::factory()->create());

        $product = Product::factory()->create();

        Livewire::test(PurchaseForm::class)
            ->call('selectProduct', $product->id)
            ->assertSet('currentProductId', $product->id);
    }

    public function test_add_item_to_list()
    {
        $this->actingAs(User::factory()->create());

        $product = Product::factory()->create(['current_stock' => 0]);

        Livewire::test(PurchaseForm::class)
            ->call('selectProduct', $product->id)
            ->set('currentQuantity', 10)
            ->set('currentUnitCost', 25)
            ->call('addItem')
            ->assertCount('items', 1);
    }

    public function test_requires_supplier_and_items()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(PurchaseForm::class)
            ->set('supplier_id', '')
            ->call('save')
            ->assertHasErrors(['supplier_id', 'items']);
    }

    public function test_requires_target_branch()
    {
        $this->actingAs(User::factory()->create());

        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create(['current_stock' => 0]);

        Livewire::test(PurchaseForm::class)
            ->call('selectSupplier', $supplier->id)
            ->set('invoice_number', 'FAC-TEST-002')
            ->set('purchase_date', now()->format('Y-m-d'))
            ->call('selectProduct', $product->id)
            ->set('currentQuantity', 10)
            ->set('currentUnitCost', 50)
            ->call('addItem')
            ->call('save')
            ->assertHasErrors(['targetBranchId'])
            ->assertDispatched('show-toasts');
    }

    public function test_validate_dispatches_toast_and_no_inline_text()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(PurchaseForm::class)
            ->set('invoice_number', '')
            ->set('targetBranchId', '')
            ->set('purchase_date', '')
            ->call('save')
            ->assertHasErrors(['supplier_id', 'targetBranchId', 'invoice_number', 'items'])
            ->assertDispatched('show-toasts')
            ->assertDontSee('The target branch id field is required.');
    }

    public function test_calculates_totals()
    {
        $this->actingAs(User::factory()->create());

        $product = Product::factory()->create();

        Livewire::test(PurchaseForm::class)
            ->call('selectProduct', $product->id)
            ->set('currentQuantity', 10)
            ->set('currentUnitCost', 25)
            ->call('addItem')
            ->assertSet('subtotal', 250);
    }

    public function test_calculates_iva()
    {
        $this->actingAs(User::factory()->create());

        $product = Product::factory()->create();

        Livewire::test(PurchaseForm::class)
            ->call('selectProduct', $product->id)
            ->set('currentQuantity', 10)
            ->set('currentUnitCost', 100)
            ->call('addItem')
            ->set('includeIva', true)
            ->assertSet('ivaAmount', 130);
    }

    public function test_edit_item_in_list()
    {
        $this->actingAs(User::factory()->create());

        $product = Product::factory()->create();

        Livewire::test(PurchaseForm::class)
            ->call('selectProduct', $product->id)
            ->set('currentQuantity', 10)
            ->set('currentUnitCost', 25)
            ->call('addItem')
            ->assertCount('items', 1)
            ->call('editItem', 0)
            ->assertCount('items', 0);
    }

    public function test_remove_item_from_list()
    {
        $this->actingAs(User::factory()->create());

        $product = Product::factory()->create();

        Livewire::test(PurchaseForm::class)
            ->call('selectProduct', $product->id)
            ->set('currentQuantity', 10)
            ->set('currentUnitCost', 25)
            ->call('addItem')
            ->assertCount('items', 1)
            ->call('confirmAction', 'delete', 0)
            ->call('executeAction')
            ->assertCount('items', 0);
    }

    public function test_saves_purchase()
    {
        $this->actingAs(User::factory()->create());
        PackagingType::factory()->create();

        $supplier = Supplier::factory()->create();
        $branch = \App\Models\Branch::factory()->create();
        $product = Product::factory()->create(['current_stock' => 0]);

        Livewire::test(PurchaseForm::class)
            ->call('selectSupplier', $supplier->id)
            ->set('targetBranchId', $branch->id)
            ->set('invoice_number', 'FAC-TEST-001')
            ->set('purchase_date', now()->format('Y-m-d'))
            ->call('selectProduct', $product->id)
            ->set('currentQuantity', 10)
            ->set('currentUnitCost', 50)
            ->call('addItem')
            ->call('confirmSave');

        $this->assertDatabaseHas('purchases', [
            'invoice_number' => 'FAC-TEST-001',
            'branch_id' => $branch->id,
        ]);

        $this->assertDatabaseHas('branch_inventories', [
            'branch_id' => $branch->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_purchase_entry_movement_carries_branch()
    {
        $this->actingAs(User::factory()->create());
        PackagingType::factory()->create();

        $supplier = Supplier::factory()->create();
        $branch = \App\Models\Branch::factory()->create();
        $product = Product::factory()->create(['current_stock' => 0]);

        Livewire::test(PurchaseForm::class)
            ->call('selectSupplier', $supplier->id)
            ->set('targetBranchId', $branch->id)
            ->set('invoice_number', 'FAC-TEST-003')
            ->set('purchase_date', now()->format('Y-m-d'))
            ->call('selectProduct', $product->id)
            ->set('currentQuantity', 10)
            ->set('currentUnitCost', 50)
            ->call('addItem')
            ->call('confirmSave');

        $purchase = \App\Models\Purchase::where('invoice_number', 'FAC-TEST-003')->firstOrFail();

        $this->assertDatabaseHas('movements', [
            'product_id' => $product->id,
            'type' => 'entry',
            'branch_id' => $branch->id,
            'reference_type' => 'purchase',
            'reference_id' => $purchase->id,
        ]);
    }

    public function test_create_new_product_inline_requires_unit()
    {
        $this->actingAs(User::factory()->create());

        $category = Category::factory()->create();

        Livewire::test(PurchaseForm::class)
            ->call('activateCreateMode')
            ->set('newProductName', 'Cable Nuevo')
            ->set('newProductCategoryId', $category->id)
            ->set('newProductUnit', '')
            ->call('createProduct')
            ->assertHasErrors('newProductUnit');
    }

    public function test_create_new_product_inline_with_unit_and_packaging()
    {
        $this->actingAs(User::factory()->create());

        UnitOfMeasure::create(['code' => 'metro', 'name' => 'Metro', 'symbol' => 'm', 'is_whole' => false, 'is_active' => true]);
        $category = Category::factory()->create();
        $bobina = PackagingType::create(['name' => 'Bobina', 'unit_of_measure' => 'metro']);

        $component = Livewire::test(PurchaseForm::class)
            ->call('activateCreateMode')
            ->set('newProductName', 'Fibra Optica')
            ->set('newProductCategoryId', $category->id)
            ->set('newProductUnit', 'metro')
            ->set('newProductPackagingTypeId', $bobina->id)
            ->set('newProductPackagingQuantity', 5000)
            ->call('createProduct')
            ->assertDispatched('show-toast')
            ->assertSet('createMode', false)
            ->assertCount('currentPackagings', 1);

        $product = Product::where('name', 'Fibra Optica')->firstOrFail();
        $component->assertSet('currentProductId', $product->id);
        $this->assertEquals('metro', $product->unit_of_measure);

        $this->assertDatabaseHas('product_packagings', [
            'product_id' => $product->id,
            'packaging_type_id' => $bobina->id,
            'quantity_in_base_unit' => 5000,
            'is_default_for_purchase' => true,
        ]);
    }

    public function test_create_new_product_inline_without_packaging_has_no_packaging()
    {
        $this->actingAs(User::factory()->create());

        UnitOfMeasure::create(['code' => 'unidad', 'name' => 'Unidad', 'symbol' => null, 'is_whole' => true, 'is_active' => true]);
        $category = Category::factory()->create();

        Livewire::test(PurchaseForm::class)
            ->call('activateCreateMode')
            ->set('newProductName', 'Router Simple')
            ->set('newProductCategoryId', $category->id)
            ->set('newProductUnit', 'unidad')
            ->call('createProduct')
            ->assertDispatched('show-toast');

        $product = Product::where('name', 'Router Simple')->firstOrFail();
        $this->assertEquals('unidad', $product->unit_of_measure);
        $this->assertCount(0, $product->packagings);
    }
}
