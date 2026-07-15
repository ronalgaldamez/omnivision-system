<?php

namespace Tests\Feature\Livewire\Inventory;

use App\Livewire\Inventory\MovementForm;
use App\Models\Movement;
use App\Models\Product;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MovementFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_form()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(MovementForm::class)
            ->assertSee('Movimiento');
    }

    public function test_product_search()
    {
        $this->actingAs(User::factory()->create());

        Product::factory()->create(['name' => 'Router']);

        Livewire::test(MovementForm::class)
            ->set('currentProductSearch', 'Rout')
            ->assertCount('searchResults', 1);
    }

    public function test_select_product()
    {
        $this->actingAs(User::factory()->create());

        $product = Product::factory()->create(['name' => 'Router']);

        Livewire::test(MovementForm::class)
            ->call('selectProduct', $product->id)
            ->assertSet('currentProductId', $product->id);
    }

    public function test_add_entry_movement_to_list()
    {
        $this->actingAs(User::factory()->create());

        $product = Product::factory()->create(['current_stock' => 10]);

        Livewire::test(MovementForm::class)
            ->call('selectProduct', $product->id)
            ->set('currentType', 'entry')
            ->set('currentQuantity', 5)
            ->set('currentUnitCost', 25)
            ->call('addToList')
            ->assertCount('movementList', 1);
    }

    public function test_add_exit_movement_with_sufficient_stock()
    {
        $this->actingAs(User::factory()->create());

        $product = Product::factory()->create(['current_stock' => 10]);

        Livewire::test(MovementForm::class)
            ->call('selectProduct', $product->id)
            ->set('currentType', 'exit')
            ->set('currentQuantity', 5)
            ->call('addToList')
            ->assertCount('movementList', 1);
    }

    public function test_add_exit_movement_with_insufficient_stock()
    {
        $this->actingAs(User::factory()->create());

        $product = Product::factory()->create(['current_stock' => 2]);

        Livewire::test(MovementForm::class)
            ->call('selectProduct', $product->id)
            ->set('currentType', 'exit')
            ->set('currentQuantity', 5)
            ->call('addToList')
            ->assertHasErrors('currentQuantity');
    }

    public function test_requires_product()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(MovementForm::class)
            ->set('currentProductId', '')
            ->call('addToList')
            ->assertHasErrors('currentProductId');
    }

    public function test_remove_from_list()
    {
        $this->actingAs(User::factory()->create());

        $product = Product::factory()->create(['current_stock' => 10]);

        Livewire::test(MovementForm::class)
            ->call('selectProduct', $product->id)
            ->set('currentQuantity', 5)
            ->call('addToList')
            ->assertCount('movementList', 1)
            ->call('confirmDelete', 0)
            ->call('executeModalAction')
            ->assertCount('movementList', 0);
    }

    public function test_saves_movements()
    {
        $this->actingAs(User::factory()->create());

        $product = Product::factory()->create([
            'current_stock' => 10,
            'average_cost' => 20,
        ]);

        Livewire::test(MovementForm::class)
            ->call('selectProduct', $product->id)
            ->set('currentType', 'entry')
            ->set('currentQuantity', 5)
            ->set('currentUnitCost', 30)
            ->call('addToList')
            ->call('confirmSaveAll')
            ->call('executeModalAction')
            ->assertRedirect(route('movements.index'));

        $this->assertDatabaseHas('movements', [
            'product_id' => $product->id,
            'type' => 'entry',
            'quantity' => 5,
        ]);
    }

    public function test_edit_movement_in_list()
    {
        $this->actingAs(User::factory()->create());

        $product = Product::factory()->create(['current_stock' => 10]);

        Livewire::test(MovementForm::class)
            ->call('selectProduct', $product->id)
            ->set('currentQuantity', 5)
            ->call('addToList')
            ->call('confirmEdit', 0)
            ->call('executeModalAction')
            ->assertSet('editingIndex', 0);
    }
}
