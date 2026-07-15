<?php

namespace Tests\Feature\Livewire\Suppliers;

use App\Livewire\Suppliers\PurchaseIndex;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PurchaseIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_purchase_list()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(PurchaseIndex::class)
            ->assertSee('Compras');
    }

    public function test_shows_purchases()
    {
        $this->actingAs(User::factory()->create());

        Purchase::factory()->count(2)->create();

        Livewire::test(PurchaseIndex::class)
            ->assertSee('Compras');
    }

    public function test_search_by_invoice()
    {
        $this->actingAs(User::factory()->create());

        Purchase::factory()->create(['invoice_number' => 'FAC-001']);
        Purchase::factory()->create(['invoice_number' => 'FAC-002']);

        Livewire::test(PurchaseIndex::class)
            ->set('search', 'FAC-001')
            ->assertSet('search', 'FAC-001');
    }

    public function test_search_by_supplier_name()
    {
        $this->actingAs(User::factory()->create());

        $supplier = Supplier::factory()->create(['name' => 'Mikrotik']);
        Purchase::factory()->create(['supplier_id' => $supplier->id]);

        Livewire::test(PurchaseIndex::class)
            ->set('search', 'Mikrotik')
            ->assertSet('search', 'Mikrotik');
    }

    public function test_filters_by_date()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(PurchaseIndex::class)
            ->set('dateFrom', '2026-01-01')
            ->set('dateTo', '2026-12-31')
            ->assertSet('dateFrom', '2026-01-01')
            ->assertSet('dateTo', '2026-12-31');
    }
}
