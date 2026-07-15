<?php

namespace Tests\Feature\Livewire\Suppliers;

use App\Livewire\Suppliers\PurchaseForm;
use App\Models\Category;
use App\Models\PackagingType;
use App\Models\Product;
use App\Models\Supplier;
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
        $product = Product::factory()->create(['current_stock' => 0]);

        Livewire::test(PurchaseForm::class)
            ->call('selectSupplier', $supplier->id)
            ->set('invoice_number', 'FAC-TEST-001')
            ->set('purchase_date', now()->format('Y-m-d'))
            ->call('selectProduct', $product->id)
            ->set('currentQuantity', 10)
            ->set('currentUnitCost', 50)
            ->call('addItem')
            ->call('confirmSave');

        $this->assertDatabaseHas('purchases', ['invoice_number' => 'FAC-TEST-001']);
    }
}
