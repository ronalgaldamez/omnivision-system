<?php

namespace Tests\Feature\Livewire\Inventory;

use App\Livewire\Inventory\ProductForm;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\UnitOfMeasure;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
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

    public function test_does_not_add_duplicate_product_to_list()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(ProductForm::class)
            ->set('currentName', 'Router')
            ->call('addToList')
            ->assertCount('productList', 1)
            ->set('currentName', 'Router')
            ->call('addToList')
            ->assertCount('productList', 1)
            ->assertDispatched('show-toast');
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
            ->assertDispatched('show-toast');
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

    public function test_clears_product_list()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(ProductForm::class)
            ->set('currentName', 'Router')
            ->call('addToList')
            ->set('currentName', 'Antena')
            ->call('addToList')
            ->assertCount('productList', 2)
            ->call('confirmAction', 'clear_list')
            ->assertSet('showConfirmModal', true)
            ->call('executeAction')
            ->assertCount('productList', 0)
            ->assertDispatched('show-toast');
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

    public function test_imports_products_from_csv_url()
    {
        $this->actingAs(User::factory()->create());

        UnitOfMeasure::create(['code' => 'metro', 'name' => 'Metro', 'symbol' => 'm', 'is_whole' => false, 'is_active' => true]);

        Http::fake([
            'docs.google.com/*' => Http::response(
                "nombre,sku,unidad,descripcion\nCable RG6,CB-001,metro,Cable coaxial\nFibra optica,FO-001,m,Fibra monomodo",
                200
            ),
        ]);

        $component = Livewire::test(ProductForm::class)
            ->set('importUrl', 'https://docs.google.com/spreadsheets/d/xxx/pub?output=csv')
            ->call('importFromUrl')
            ->assertCount('importPreview', 2)
            ->assertSet('showImportPreview', true)
            ->assertCount('productList', 0);

        $preview = $component->get('importPreview');
        $this->assertEquals('Cable RG6', $preview[0]['name']);
        $this->assertEquals('metro', $preview[0]['unit_of_measure']);
        $this->assertEquals('CB-001', $preview[0]['sku']);
        $this->assertEquals('metro', $preview[1]['unit_of_measure']);
    }

    public function test_import_preview_confirm_adds_to_list()
    {
        $this->actingAs(User::factory()->create());

        Http::fake([
            'docs.google.com/*' => Http::response("nombre,unidad\nCable A,metro\nCable B,unidad", 200),
        ]);

        Livewire::test(ProductForm::class)
            ->set('importUrl', 'https://docs.google.com/spreadsheets/d/xxx/pub?output=csv')
            ->call('importFromUrl')
            ->call('confirmImport')
            ->assertCount('productList', 2)
            ->assertSet('showImportPreview', false)
            ->assertDispatched('show-toast');
    }

    public function test_import_preview_cancel_discards()
    {
        $this->actingAs(User::factory()->create());

        Http::fake([
            'docs.google.com/*' => Http::response("nombre,unidad\nCable A,metro", 200),
        ]);

        Livewire::test(ProductForm::class)
            ->set('importUrl', 'https://docs.google.com/spreadsheets/d/xxx/pub?output=csv')
            ->call('importFromUrl')
            ->call('cancelImport')
            ->assertCount('productList', 0)
            ->assertSet('showImportPreview', false);
    }

    public function test_import_preview_refresh_refetches()
    {
        $this->actingAs(User::factory()->create());

        $responses = [
            "nombre\nProducto Uno",
            "nombre\nProducto Uno\nProducto Dos",
        ];

        Http::fake([
            'docs.google.com/*' => function () use (&$responses) {
                return Http::response(array_shift($responses), 200);
            },
        ]);

        Livewire::test(ProductForm::class)
            ->set('importUrl', 'https://docs.google.com/spreadsheets/d/xxx/pub?output=csv')
            ->call('importFromUrl')
            ->assertCount('importPreview', 1)
            ->call('refreshImport')
            ->assertCount('importPreview', 2);
    }

    public function test_import_skips_existing_products()
    {
        $this->actingAs(User::factory()->create());

        Product::factory()->create(['name' => 'Existente', 'sku' => 'EX-001']);

        Http::fake([
            'docs.google.com/*' => Http::response(
                "nombre,sku\nExistente,EX-001\nNuevo,NV-001",
                200
            ),
        ]);

        $component = Livewire::test(ProductForm::class)
            ->set('importUrl', 'https://docs.google.com/spreadsheets/d/xxx/pub?output=csv')
            ->call('importFromUrl')
            ->assertCount('importPreview', 2);

        $preview = $component->get('importPreview');
        $this->assertEquals('existing', $preview[0]['status']);
        $this->assertEquals('new', $preview[1]['status']);

        $component->call('confirmImport')
            ->assertCount('productList', 1);

        $list = $component->get('productList');
        $this->assertEquals('Nuevo', $list[0]['name']);
    }

    public function test_import_skips_products_already_in_pending_list()
    {
        $this->actingAs(User::factory()->create());

        Http::fake([
            'docs.google.com/*' => Http::response("nombre\nCable RG6\nFibra optica", 200),
        ]);

        $component = Livewire::test(ProductForm::class)
            ->set('currentName', 'Cable RG6')
            ->call('addToList')
            ->assertCount('productList', 1);

        $component
            ->set('importUrl', 'https://docs.google.com/spreadsheets/d/xxx/pub?output=csv')
            ->call('importFromUrl')
            ->assertCount('importPreview', 2);

        $preview = $component->get('importPreview');
        $this->assertEquals('existing', $preview[0]['status']);
        $this->assertEquals('new', $preview[1]['status']);

        $component->call('confirmImport')
            ->assertCount('productList', 2);
    }

    public function test_import_skips_duplicates_within_same_sheet()
    {
        $this->actingAs(User::factory()->create());

        Http::fake([
            'docs.google.com/*' => Http::response("nombre\nCable A\nCable A\nCable B", 200),
        ]);

        $component = Livewire::test(ProductForm::class)
            ->set('importUrl', 'https://docs.google.com/spreadsheets/d/xxx/pub?output=csv')
            ->call('importFromUrl')
            ->assertCount('importPreview', 3);

        $preview = $component->get('importPreview');
        $this->assertEquals('new', $preview[0]['status']);
        $this->assertEquals('existing', $preview[1]['status']);
        $this->assertEquals('new', $preview[2]['status']);

        $component->call('confirmImport')
            ->assertCount('productList', 2);
    }

    public function test_import_falls_back_to_unidad_when_unit_unknown()
    {
        $this->actingAs(User::factory()->create());

        Http::fake([
            'docs.google.com/*' => Http::response("nombre,unidad\nProducto X,galon", 200),
        ]);

        $component = Livewire::test(ProductForm::class)
            ->set('importUrl', 'https://docs.google.com/spreadsheets/d/xxx/pub?output=csv')
            ->call('importFromUrl')
            ->assertCount('importPreview', 1);

        $preview = $component->get('importPreview');
        $this->assertEquals('unidad', $preview[0]['unit_of_measure']);
    }

    public function test_import_requires_url()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(ProductForm::class)
            ->set('importUrl', '')
            ->call('importFromUrl')
            ->assertHasErrors('importUrl');
    }

    public function test_import_shows_error_when_url_fails()
    {
        $this->actingAs(User::factory()->create());

        Http::fake([
            'docs.google.com/*' => Http::response('', 404),
        ]);

        Livewire::test(ProductForm::class)
            ->set('importUrl', 'https://docs.google.com/spreadsheets/d/xxx/pub?output=csv')
            ->call('importFromUrl')
            ->assertCount('importPreview', 0)
            ->assertSet('showImportPreview', false);
    }

    public function test_import_creates_initial_stock()
    {
        $this->actingAs(User::factory()->create());

        UnitOfMeasure::create(['code' => 'metro', 'name' => 'Metro', 'symbol' => 'm', 'is_whole' => false, 'is_active' => true]);

        Http::fake([
            'docs.google.com/*' => Http::response("nombre,unidad,stock,costo\nCable RG6,metro,25000,0.0998", 200),
        ]);

        Livewire::test(ProductForm::class)
            ->set('importUrl', 'https://docs.google.com/spreadsheets/d/xxx/pub?output=csv')
            ->call('importFromUrl')
            ->call('confirmImport')
            ->call('confirmSaveAll')
            ->call('saveAll')
            ->assertRedirect(route('products.index'));

        $product = Product::where('name', 'Cable RG6')->first();
        $this->assertNotNull($product);
        $this->assertEquals(25000, (float) $product->current_stock);
        $this->assertEquals(0.0998, (float) $product->average_cost);

        $this->assertDatabaseHas('movements', [
            'product_id' => $product->id,
            'type' => 'entry',
            'quantity' => 25000,
        ]);
    }

    public function test_import_converts_stock_with_packaging()
    {
        $this->actingAs(User::factory()->create());

        UnitOfMeasure::create(['code' => 'metro', 'name' => 'Metro', 'symbol' => 'm', 'is_whole' => false, 'is_active' => true]);
        $bobina = \App\Models\PackagingType::create(['name' => 'Bobina', 'unit_of_measure' => 'm']);

        Http::fake([
            'docs.google.com/*' => Http::response("nombre,unidad,stock,costo\nCable RG6,metro,5,0.0998", 200),
        ]);

        Livewire::test(ProductForm::class)
            ->set('importUrl', 'https://docs.google.com/spreadsheets/d/xxx/pub?output=csv')
            ->call('importFromUrl')
            ->set('importPreview.0.packaging_type_id', $bobina->id)
            ->set('importPreview.0.packaging_quantity', 5000)
            ->call('confirmImport')
            ->call('confirmSaveAll')
            ->call('saveAll')
            ->assertRedirect(route('products.index'));

        $product = Product::where('name', 'Cable RG6')->first();
        $this->assertNotNull($product);
        $this->assertEquals(25000, (float) $product->current_stock);

        $this->assertDatabaseHas('product_packagings', [
            'product_id' => $product->id,
            'quantity_in_base_unit' => 5000,
        ]);
    }

    public function test_import_reads_packaging_from_sheet()
    {
        $this->actingAs(User::factory()->create());

        UnitOfMeasure::create(['code' => 'metro', 'name' => 'Metro', 'symbol' => 'm', 'is_whole' => false, 'is_active' => true]);
        \App\Models\PackagingType::create(['name' => 'Bobina', 'unit_of_measure' => 'm']);

        Http::fake([
            'docs.google.com/*' => Http::response("nombre,unidad,empaque,cant_por_empaque,stock,costo\nCable RG6,metro,Bobina,5000,5,499", 200),
        ]);

        Livewire::test(ProductForm::class)
            ->set('importUrl', 'https://docs.google.com/spreadsheets/d/xxx/pub?output=csv')
            ->call('importFromUrl')
            ->call('confirmImport')
            ->call('confirmSaveAll')
            ->call('saveAll')
            ->assertRedirect(route('products.index'));

        $product = Product::where('name', 'Cable RG6')->first();
        $this->assertNotNull($product);
        $this->assertEquals(25000, (float) $product->current_stock);
        $this->assertEquals(0.0998, (float) $product->average_cost);

        $this->assertDatabaseHas('product_packagings', [
            'product_id' => $product->id,
            'quantity_in_base_unit' => 5000,
        ]);
    }

    public function test_import_full_sheet_with_empty_cells_produces_correct_stock()
    {
        $this->actingAs(User::factory()->create());

        UnitOfMeasure::create(['code' => 'unidad', 'name' => 'Unidad', 'is_whole' => true, 'is_active' => true]);
        UnitOfMeasure::create(['code' => 'metro', 'name' => 'Metro', 'symbol' => 'm', 'is_whole' => false, 'is_active' => true]);
        \App\Models\PackagingType::create(['name' => 'Bobina', 'unit_of_measure' => 'm']);
        \App\Models\PackagingType::create(['name' => 'Rollo', 'unit_of_measure' => 'm']);

        $csv = "nombre,sku,unidad,descripcion,stock_min,stock_max,empaque,cant_por_empaque,stock,costo\n"
            ."Fibra óptica,,metro,Fibra monomodo,0,50000,Bobina,5000,5,499\n"
            ."Cable RG6,,metro,Cable coaxial,100,5000,Bobina,1000,12,100\n"
            ."Conector RJ45,,unidad,Conector de red,200,2000,,500,0.15,\n"
            ."Cinta aislante,,metro,Cinta eléctrica,50,500,Rollo,100,8,\n"
            ."Router Mikrotik,,unidad,Router de borde,10,100,,45,85.5,\n"
            ."Patch cord 1m,,unidad,Cable de conexión,50,500,,300,1.2,";

        Http::fake([
            'docs.google.com/*' => Http::response($csv, 200),
        ]);

        Livewire::test(ProductForm::class)
            ->set('importUrl', 'https://docs.google.com/spreadsheets/d/xxx/pub?output=csv')
            ->call('importFromUrl')
            ->call('confirmImport')
            ->call('confirmSaveAll')
            ->call('saveAll')
            ->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('products', ['name' => 'Fibra óptica', 'unit_of_measure' => 'metro', 'current_stock' => 25000]);
        $this->assertDatabaseHas('products', ['name' => 'Cable RG6', 'unit_of_measure' => 'metro', 'current_stock' => 12000]);
        $this->assertDatabaseHas('products', ['name' => 'Conector RJ45', 'unit_of_measure' => 'unidad', 'current_stock' => 500]);
        $this->assertDatabaseHas('products', ['name' => 'Cinta aislante', 'unit_of_measure' => 'metro', 'current_stock' => 800]);
        $this->assertDatabaseHas('products', ['name' => 'Router Mikrotik', 'unit_of_measure' => 'unidad', 'current_stock' => 45]);
        $this->assertDatabaseHas('products', ['name' => 'Patch cord 1m', 'unit_of_measure' => 'unidad', 'current_stock' => 300]);
    }
}
