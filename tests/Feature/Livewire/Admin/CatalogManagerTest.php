<?php

namespace Tests\Feature\Livewire\Admin;

use App\Livewire\Admin\Catalog\CatalogManager;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ProductModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CatalogManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_catalog()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(CatalogManager::class)
            ->assertSee('Catálogo');
    }

    public function test_switches_tab()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(CatalogManager::class)
            ->set('activeTab', 'brands')
            ->assertSet('activeTab', 'brands');
    }

    public function test_creates_brand()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(CatalogManager::class)
            ->set('activeTab', 'brands')
            ->call('openBrandModal')
            ->assertSet('showModal', true)
            ->set('brandName', 'Mikrotik')
            ->call('confirmSaveBrand')
            ->call('executeConfirmedAction');

        $this->assertDatabaseHas('brands', ['name' => 'Mikrotik']);
    }

    public function test_creates_category()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(CatalogManager::class)
            ->set('activeTab', 'categories')
            ->call('openCategoryModal')
            ->set('categoryName', 'Equipos Activos')
            ->call('confirmSaveCategory')
            ->call('executeConfirmedAction');

        $this->assertDatabaseHas('categories', ['name' => 'Equipos Activos']);
    }

    public function test_creates_model()
    {
        $this->actingAs(User::factory()->create());

        $brand = Brand::factory()->create();
        $category = Category::factory()->create();

        Livewire::test(CatalogManager::class)
            ->call('openModelModal')
            ->set('modelName', 'SXTsq')
            ->set('modelBrandId', $brand->id)
            ->set('modelCategoryId', $category->id)
            ->call('confirmSaveModel')
            ->call('executeConfirmedAction');

        $this->assertDatabaseHas('product_models', ['name' => 'SXTsq']);
    }

    public function test_delete_brand()
    {
        $this->actingAs(User::factory()->create());

        $brand = Brand::factory()->create();

        Livewire::test(CatalogManager::class)
            ->set('activeTab', 'brands')
            ->call('confirmDeleteBrand', $brand->id)
            ->call('deleteBrand');

        $this->assertNull(Brand::find($brand->id));
    }

    public function test_delete_category()
    {
        $this->actingAs(User::factory()->create());

        $category = Category::factory()->create();

        Livewire::test(CatalogManager::class)
            ->set('activeTab', 'categories')
            ->call('confirmDeleteCategory', $category->id)
            ->call('deleteCategory');

        $this->assertNull(Category::find($category->id));
    }
}
