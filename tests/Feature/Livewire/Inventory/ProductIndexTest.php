<?php

namespace Tests\Feature\Livewire\Inventory;

use App\Livewire\Inventory\ProductIndex;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_product_list()
    {
        $this->actingAs(User::factory()->create());

        Product::factory()->count(3)->create();

        Livewire::test(ProductIndex::class)
            ->assertSee('Productos');
    }

    public function test_search_filters_products()
    {
        $this->actingAs(User::factory()->create());

        Product::factory()->create(['name' => 'Router']);
        Product::factory()->create(['name' => 'Switch']);
        Product::factory()->create(['name' => 'Cable']);

        Livewire::test(ProductIndex::class)
            ->set('search', 'Router')
            ->assertSee('Router')
            ->assertDontSee('Switch');
    }

    public function test_confirm_delete_opens_modal()
    {
        $this->actingAs(User::factory()->create());

        $product = Product::factory()->create(['current_stock' => 0]);

        Livewire::test(ProductIndex::class)
            ->call('confirmDelete', $product->id)
            ->assertSet('showDeleteModal', true)
            ->assertSet('productIdToDelete', $product->id);
    }

    public function test_delete_product_with_zero_stock()
    {
        $this->actingAs(User::factory()->create());

        $product = Product::factory()->create(['current_stock' => 0]);

        Livewire::test(ProductIndex::class)
            ->call('confirmDelete', $product->id)
            ->call('deleteProduct')
            ->assertSet('showDeleteModal', false)
            ->assertDispatched('showToast');

        $this->assertNull(Product::find($product->id));
    }

    public function test_cannot_delete_product_with_stock()
    {
        $this->actingAs(User::factory()->create());

        $product = Product::factory()->create(['current_stock' => 10]);

        Livewire::test(ProductIndex::class)
            ->call('confirmDelete', $product->id)
            ->call('deleteProduct')
            ->assertDispatched('showToast');

        $this->assertNotNull(Product::find($product->id));
    }

    public function test_close_delete_modal()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(ProductIndex::class)
            ->call('closeDeleteModal')
            ->assertSet('showDeleteModal', false)
            ->assertSet('productIdToDelete', null);
    }
}
