<?php

namespace Tests\Feature\Livewire\Suppliers;

use App\Livewire\Suppliers\QuickProductForm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class QuickProductFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_form()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(QuickProductForm::class)
            ->assertSet('name', '');
    }

    public function test_requires_name()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(QuickProductForm::class)
            ->set('name', '')
            ->call('save')
            ->assertHasErrors('name');
    }

    public function test_creates_product()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(QuickProductForm::class)
            ->set('name', 'Producto Rápido')
            ->set('current_stock', 10)
            ->call('save')
            ->assertDispatched('productCreated');

        $this->assertDatabaseHas('products', ['name' => 'Producto Rápido']);
    }
}
