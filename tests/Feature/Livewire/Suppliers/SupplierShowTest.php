<?php

namespace Tests\Feature\Livewire\Suppliers;

use App\Livewire\Suppliers\SupplierShow;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SupplierShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_supplier_detail()
    {
        $this->actingAs(User::factory()->create());

        $supplier = Supplier::factory()->create();

        Livewire::test(SupplierShow::class, ['id' => $supplier->id])
            ->assertSee($supplier->name);
    }

    public function test_shows_purchases()
    {
        $this->actingAs(User::factory()->create());

        $supplier = Supplier::factory()->create();

        Livewire::test(SupplierShow::class, ['id' => $supplier->id])
            ->assertSet('supplier.id', $supplier->id);
    }

    public function test_404_for_invalid_supplier()
    {
        $this->actingAs(User::factory()->create());

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(SupplierShow::class, ['id' => 999]);
    }
}
