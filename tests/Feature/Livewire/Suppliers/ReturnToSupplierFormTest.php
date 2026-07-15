<?php

namespace Tests\Feature\Livewire\Suppliers;

use App\Livewire\Suppliers\ReturnToSupplierForm;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReturnToSupplierFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_form()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(ReturnToSupplierForm::class)
            ->assertSee('Devolución');
    }

    public function test_supplier_search()
    {
        $this->actingAs(User::factory()->create());

        Supplier::factory()->create(['name' => 'Mikrotik SV']);

        Livewire::test(ReturnToSupplierForm::class)
            ->set('supplierSearch', 'Mikro')
            ->assertCount('supplierResults', 1);
    }

    public function test_select_supplier_loads_purchases()
    {
        $this->actingAs(User::factory()->create());

        $supplier = Supplier::factory()->create();
        Purchase::factory()->create(['supplier_id' => $supplier->id]);

        Livewire::test(ReturnToSupplierForm::class)
            ->call('selectSupplier', $supplier->id)
            ->assertSet('supplier_id', $supplier->id);
    }

    public function test_select_purchase_loads_items()
    {
        $this->actingAs(User::factory()->create());

        $supplier = Supplier::factory()->create();
        $purchase = Purchase::factory()->create(['supplier_id' => $supplier->id]);
        $product = Product::factory()->create(['current_stock' => 100]);
        PurchaseItem::factory()->create([
            'purchase_id' => $purchase->id,
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        Livewire::test(ReturnToSupplierForm::class)
            ->call('selectSupplier', $supplier->id)
            ->set('purchase_id', $purchase->id)
            ->assertCount('items', 1);
    }

    public function test_requires_supplier_and_purchase()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(ReturnToSupplierForm::class)
            ->call('confirmReturn')
            ->assertHasErrors(['supplier_id', 'purchase_id']);
    }
}
