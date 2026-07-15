<?php

namespace Tests\Feature\Livewire\Suppliers;

use App\Livewire\Suppliers\SupplierIndex;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Purchase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SupplierIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_supplier_list()
    {
        $this->actingAs(User::factory()->create());

        Supplier::factory()->count(3)->create();

        Livewire::test(SupplierIndex::class)
            ->assertSee('Proveedores');
    }

    public function test_resets_search()
    {
        $this->actingAs(User::factory()->create());

        Supplier::factory()->create(['name' => 'Mikrotik SV']);

        Livewire::test(SupplierIndex::class)
            ->assertSee('Mikrotik')
            ->assertSet('search', '');
    }

    public function test_confirm_delete_shows_modal()
    {
        $this->actingAs(User::factory()->create());

        $supplier = Supplier::factory()->create();

        Livewire::test(SupplierIndex::class)
            ->call('confirmDelete', $supplier->id)
            ->assertSet('showDeleteModal', true)
            ->assertSet('deleteId', $supplier->id);
    }

    public function test_delete_supplier_without_purchases()
    {
        $this->actingAs(User::factory()->create());

        $supplier = Supplier::factory()->create();

        Livewire::test(SupplierIndex::class)
            ->call('confirmDelete', $supplier->id)
            ->call('delete');

        $this->assertNull(Supplier::find($supplier->id));
    }

    public function test_cannot_delete_supplier_with_purchases()
    {
        $this->actingAs(User::factory()->create());

        $supplier = Supplier::factory()->create();
        Purchase::factory()->create(['supplier_id' => $supplier->id]);

        Livewire::test(SupplierIndex::class)
            ->call('confirmDelete', $supplier->id)
            ->call('delete');

        $this->assertNotNull(Supplier::find($supplier->id));
    }
}
