<?php

namespace Tests\Feature\Livewire\Inventory;

use App\Livewire\Inventory\ProductForm;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_create_form()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(ProductForm::class)
            ->assertSee('Producto');
    }

    public function test_renders_edit_form_with_product_data()
    {
        $this->actingAs(User::factory()->create());

        $product = Product::factory()->create();

        Livewire::test(ProductForm::class, ['id' => $product->id])
            ->assertSet('editingId', $product->id)
            ->assertSet('currentName', $product->name);
    }

    public function test_adds_product_to_list()
    {
        $this->actingAs(User::factory()->create());
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();

        Livewire::test(ProductForm::class)
            ->set('currentName', 'Router Nuevo')
            ->set('currentBrandId', $brand->id)
            ->set('currentCategoryId', $category->id)
            ->set('currentStockMin', 2)
            ->call('addToList')
            ->assertCount('productList', 1)
            ->assertDispatched('productAdded');
    }

    public function test_requires_name_to_add_to_list()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(ProductForm::class)
            ->set('currentName', '')
            ->call('addToList')
            ->assertHasErrors('currentName');
    }

    public function test_saves_all_products_from_list()
    {
        $this->actingAs(User::factory()->create());

        $brand = Brand::factory()->create();
        $category = Category::factory()->create();

        Livewire::test(ProductForm::class)
            ->set('currentName', 'Router')
            ->set('currentBrandId', $brand->id)
            ->set('currentCategoryId', $category->id)
            ->set('currentStockMin', 1)
            ->call('addToList')
            ->call('confirmSaveAll')
            ->call('saveAll')
            ->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('products', ['name' => 'Router']);
    }

    public function test_shows_error_when_saving_empty_list()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(ProductForm::class)
            ->call('confirmSaveAll')
            ->assertDispatched('showToast');
    }

    public function test_updates_existing_product()
    {
        $this->actingAs(User::factory()->create());

        $product = Product::factory()->create(['name' => 'Old Name']);

        Livewire::test(ProductForm::class, ['id' => $product->id])
            ->set('currentName', 'Updated Name')
            ->call('confirmUpdate')
            ->call('update')
            ->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('products', ['name' => 'Updated Name']);
    }

    public function test_remove_from_list()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(ProductForm::class)
            ->set('currentName', 'Router')
            ->call('addToList')
            ->assertCount('productList', 1)
            ->call('confirmAction', 'delete', 0)
            ->call('executeAction')
            ->assertCount('productList', 0);
    }

    public function test_category_search()
    {
        $this->actingAs(User::factory()->create());

        Category::factory()->create(['name' => 'Equipos Activos']);

        Livewire::test(ProductForm::class)
            ->set('categorySearch', 'Equi')
            ->assertCount('categoryResults', 1);
    }

    public function test_brand_search()
    {
        $this->actingAs(User::factory()->create());

        Brand::factory()->create(['name' => 'Mikrotik']);

        Livewire::test(ProductForm::class)
            ->set('brandSearch', 'Mikro')
            ->assertCount('brandResults', 1);
    }
}
