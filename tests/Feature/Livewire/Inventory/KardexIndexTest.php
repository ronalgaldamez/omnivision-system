<?php

namespace Tests\Feature\Livewire\Inventory;

use App\Livewire\Inventory\KardexIndex;
use App\Models\Movement;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class KardexIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_kardex()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(KardexIndex::class)
            ->assertSee('Tarjeta de Control');
    }

    public function test_shows_movements()
    {
        $this->actingAs(User::factory()->create());

        $product = Product::factory()->create(['current_stock' => 10]);
        Movement::factory()->entry()->create([
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        Livewire::test(KardexIndex::class)
            ->assertSee('Tarjeta de Control');
    }

    public function test_product_search()
    {
        $this->actingAs(User::factory()->create());

        Product::factory()->create(['name' => 'Router']);

        Livewire::test(KardexIndex::class)
            ->set('productSearch', 'Rout')
            ->assertCount('productResults', 1);
    }

    public function test_filter_by_product()
    {
        $this->actingAs(User::factory()->create());

        $p1 = Product::factory()->create();
        $p2 = Product::factory()->create();
        Movement::factory()->entry()->create(['product_id' => $p1->id]);
        Movement::factory()->entry()->create(['product_id' => $p2->id]);

        Livewire::test(KardexIndex::class)
            ->call('selectProduct', $p1->id, $p1->name)
            ->assertSet('product_id', $p1->id);
    }

    public function test_clear_product_filter()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(KardexIndex::class)
            ->call('selectProduct', 1, 'Test')
            ->call('clearProduct')
            ->assertSet('product_id', '')
            ->assertSet('productSearch', '');
    }

    public function test_open_and_close_product_modal()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(KardexIndex::class)
            ->call('openProductModal')
            ->assertSet('showProductModal', true)
            ->call('closeProductModal')
            ->assertSet('showProductModal', false);
    }

    public function test_filter_by_type()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(KardexIndex::class)
            ->set('type', 'entry')
            ->assertSet('type', 'entry');
    }
}
