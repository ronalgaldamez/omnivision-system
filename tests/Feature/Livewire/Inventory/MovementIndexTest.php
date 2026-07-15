<?php

namespace Tests\Feature\Livewire\Inventory;

use App\Livewire\Inventory\MovementIndex;
use App\Models\Movement;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MovementIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_movement_list()
    {
        $this->actingAs(User::factory()->create());

        Movement::factory()->count(2)->create();

        Livewire::test(MovementIndex::class)
            ->assertSee('Movimientos');
    }

    public function test_filters_by_type()
    {
        $this->actingAs(User::factory()->create());

        Movement::factory()->entry()->create();
        Movement::factory()->exit()->create();

        Livewire::test(MovementIndex::class)
            ->set('typeFilter', 'entry')
            ->assertSet('typeFilter', 'entry');
    }

    public function test_search_by_product_name()
    {
        $this->actingAs(User::factory()->create());

        $product = Product::factory()->create(['name' => 'Router']);
        Movement::factory()->create(['product_id' => $product->id]);

        Livewire::test(MovementIndex::class)
            ->set('search', 'Router')
            ->assertSet('search', 'Router');
    }

    public function test_resets_filters()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(MovementIndex::class)
            ->set('typeFilter', 'exit')
            ->set('search', 'test')
            ->set('typeFilter', '')
            ->set('search', '')
            ->assertSet('typeFilter', '')
            ->assertSet('search', '');
    }
}
