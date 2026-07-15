<?php

namespace Tests\Feature\Livewire\Inventory;

use App\Livewire\Inventory\ProductShow;
use App\Models\Movement;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_product_detail()
    {
        $this->actingAs(User::factory()->create());

        $product = Product::factory()->create();

        Livewire::test(ProductShow::class, ['id' => $product->id])
            ->assertSee($product->name);
    }

    public function test_shows_movements()
    {
        $this->actingAs(User::factory()->create());

        $product = Product::factory()->create();
        Movement::factory()->count(3)->create(['product_id' => $product->id]);

        Livewire::test(ProductShow::class, ['id' => $product->id])
            ->assertSet('product.id', $product->id);
    }

    public function test_404_for_invalid_product()
    {
        $this->actingAs(User::factory()->create());

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(ProductShow::class, ['id' => 999]);
    }
}
