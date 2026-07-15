<?php

namespace Tests\Feature\Livewire\Suppliers;

use App\Livewire\Suppliers\PurchaseShow;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PurchaseShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_purchase_detail()
    {
        $this->actingAs(User::factory()->create());

        $purchase = Purchase::factory()->create();

        Livewire::test(PurchaseShow::class, ['id' => $purchase->id])
            ->assertSee($purchase->invoice_number);
    }

    public function test_shows_purchase_items()
    {
        $this->actingAs(User::factory()->create());

        $purchase = Purchase::factory()->create();

        Livewire::test(PurchaseShow::class, ['id' => $purchase->id])
            ->assertSet('purchase.id', $purchase->id);
    }

    public function test_404_for_invalid_purchase()
    {
        $this->actingAs(User::factory()->create());

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(PurchaseShow::class, ['id' => 999]);
    }
}
