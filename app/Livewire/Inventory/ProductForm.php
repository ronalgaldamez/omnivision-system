<?php

namespace App\Livewire\Inventory;

use App\Models\Product;
use App\Models\ProductModel;
use App\Traits\HasFormPersistence;
use App\Traits\ManagesProductPackaging;
use Livewire\Component;

class ProductForm extends Component
{
    use HasFormPersistence;
    use ManagesProductPackaging;

    public $editingId = null;

    public $productList = [];

    public bool $hasUnsavedChanges = false;

    // Campos del formulario actual
    public $currentName = '';

    public $currentUnit = 'unidad';

    public $currentMeasureValue = null;

    public $currentStockMin = 0;

    public $currentStockMax = null;

    public $currentDescription = '';

    public $currentBrandId = '';

    public $currentModelId = '';

    public $currentCategoryId = '';

    // Modal de búsqueda de modelo
    public $showModelModal = false;

    public $modelSearchTerm = '';

    public $modelSearchResults = [];

    public $selectedModelDisplay = '';

    // Búsqueda de categoría
    public $categorySearch = '';

    public $categoryResults = [];

    public $showCategoryModal = false;

    public $categoryList = [];

    public $categoryListSearch = '';

    // Búsqueda de marca
    public $brandSearch = '';

    public $brandResults = [];

    public $showBrandModal = false;

    public $brandList = [];

    public $brandListSearch = '';

    // Modal de confirmación de acciones (editar/eliminar)
    public $showConfirmModal = false;

    public $modalAction = null;

    public $modalItemIndex = null;

    public $modalMessage = '';

    // Modal de confirmación de guardado (similar a compras)
    public $showSaveConfirmModal = false;

    protected $rules = [
        'currentName' => 'required|string|max:255',
        'currentStockMin' => 'required|integer|min:0',
        'currentStockMax' => 'nullable|integer|min:0',
        'currentDescription' => 'nullable|string',
        'currentBrandId' => 'nullable|exists:brands,id',
        'currentModelId' => 'nullable|exists:product_models,id',
        'currentCategoryId' => 'nullable|exists:categories,id',
    ];

    protected function persistableProperties(): array
    {
        return [
            'currentName',
            'currentStockMin', 'currentStockMax', 'currentDescription',
            'currentBrandId', 'currentModelId', 'currentCategoryId',
            'selectedModelDisplay', 'productList',
            'categorySearch', 'brandSearch',
        ];
    }

    protected function detectUnsavedChanges(): bool
    {
        if ($this->editingId) {
            return false;
        }
        return ! empty($this->productList) || $this->currentName !== '';
    }

    public function updated($name, $value): void
    {
        if (in_array($name, $this->persistableProperties(), true)) {
            $this->persistState();
        }
    }

    public function mount($id = null)
    {
        if ($id) {
            $this->editingId = $id;
            $product = Product::findOrFail($id);
            $this->currentName = $product->name;
            $this->currentStockMin = intval($product->stock_min ?? 0);
            $this->currentStockMax = intval($product->stock_max ?? 0);
            $this->currentDescription = $product->description;
            $this->currentBrandId = $product->brand_id;
            $this->currentModelId = $product->model_id;
            $this->currentCategoryId = $product->category_id;
            if ($this->currentModelId) {
                $model = ProductModel::with('brand', 'category')->find($this->currentModelId);
                if ($model) {
                    $this->selectedModelDisplay = "{$model->brand->name} - {$model->name} - {$model->category->name}";
                }
            }
            $this->currentProductId = $product->id;
            $this->loadPackagingsForProduct($product->id);
            $this->initPackagingState();
        } else {
            $this->productList = [];
            $this->resetCurrent();
            if (session()->has($this->persistenceKey())) {
                $this->restorePersistedState();
            }
        }
    }

    public function resetCurrent()
    {
        $this->currentName = '';
        $this->currentStockMin = 0;
        $this->currentStockMax = null;
        $this->currentDescription = '';
        $this->currentBrandId = '';
        $this->currentModelId = '';
        $this->currentCategoryId = '';
        $this->selectedModelDisplay = '';
        $this->modelSearchTerm = '';
        $this->modelSearchResults = [];
    }

    // Modal de búsqueda de modelos
    public function openModelModal()
    {
        $this->showModelModal = true;
        $this->modelSearchTerm = '';
        $this->modelSearchResults = [];
    }

    public function updatedModelSearchTerm()
    {
        if (strlen($this->modelSearchTerm) >= 2) {
            $this->modelSearchResults = ProductModel::with('brand', 'category')
                ->where('name', 'like', '%'.$this->modelSearchTerm.'%')
                ->orWhereHas('brand', fn ($q) => $q->where('name', 'like', '%'.$this->modelSearchTerm.'%'))
                ->orWhereHas('category', fn ($q) => $q->where('name', 'like', '%'.$this->modelSearchTerm.'%'))
                ->limit(10)
                ->get();
        } else {
            $this->modelSearchResults = [];
        }
    }

    public function selectModel($id)
    {
        $model = ProductModel::with('brand', 'category')->find($id);
        if ($model) {
            $this->currentModelId = $model->id;
            $this->currentBrandId = $model->brand_id;
            $this->currentCategoryId = $model->category_id;
            $this->selectedModelDisplay = "{$model->brand->name} - {$model->name} - {$model->category->name}";
            $this->showModelModal = false;
            $this->persistState();
        }
    }

    public function clearModelSelection()
    {
        $this->currentModelId = '';
        $this->currentBrandId = '';
        $this->currentCategoryId = '';
        $this->selectedModelDisplay = '';
        $this->persistState();
    }

    // ==================== CATEGORÍA ====================
    public function updatedCategorySearch()
    {
        if (strlen($this->categorySearch) >= 2) {
            $this->categoryResults = \App\Models\Category::where('name', 'like', '%'.$this->categorySearch.'%')
                ->orderBy('name')->limit(10)->get();
        } else {
            $this->categoryResults = [];
        }
    }

    public function selectCategory($id)
    {
        $cat = \App\Models\Category::find($id);
        if ($cat) {
            $this->currentCategoryId = $cat->id;
            $this->categorySearch = $cat->name;
            $this->categoryResults = [];
            $this->showCategoryModal = false;
            $this->persistState();
        }
    }

    public function clearCategory()
    {
        $this->currentCategoryId = '';
        $this->categorySearch = '';
        $this->categoryResults = [];
    }

    public function openCategoryModal()
    {
        $this->categoryListSearch = '';
        $this->categoryList = \App\Models\Category::orderBy('name')->take(50)->get();
        $this->showCategoryModal = true;
    }

    public function closeCategoryModal()
    {
        $this->showCategoryModal = false;
        $this->categoryListSearch = '';
        $this->categoryList = [];
    }

    public function updatedCategoryListSearch()
    {
        if (strlen($this->categoryListSearch) >= 2) {
            $this->categoryList = \App\Models\Category::where('name', 'like', '%'.$this->categoryListSearch.'%')
                ->orderBy('name')->take(50)->get();
        } else {
            $this->categoryList = \App\Models\Category::orderBy('name')->take(50)->get();
        }
    }

    // ==================== MARCA ====================
    public function updatedBrandSearch()
    {
        if (strlen($this->brandSearch) >= 2) {
            $this->brandResults = \App\Models\Brand::where('name', 'like', '%'.$this->brandSearch.'%')
                ->orderBy('name')->limit(10)->get();
        } else {
            $this->brandResults = [];
        }
    }

    public function selectBrand($id)
    {
        $brand = \App\Models\Brand::find($id);
        if ($brand) {
            $this->currentBrandId = $brand->id;
            $this->brandSearch = $brand->name;
            $this->brandResults = [];
            $this->showBrandModal = false;
            $this->persistState();
        }
    }

    public function clearBrand()
    {
        $this->currentBrandId = '';
        $this->brandSearch = '';
        $this->brandResults = [];
    }

    public function openBrandModal()
    {
        $this->brandListSearch = '';
        $this->brandList = \App\Models\Brand::orderBy('name')->take(50)->get();
        $this->showBrandModal = true;
    }

    public function closeBrandModal()
    {
        $this->showBrandModal = false;
        $this->brandListSearch = '';
        $this->brandList = [];
    }

    public function updatedBrandListSearch()
    {
        if (strlen($this->brandListSearch) >= 2) {
            $this->brandList = \App\Models\Brand::where('name', 'like', '%'.$this->brandListSearch.'%')
                ->orderBy('name')->take(50)->get();
        } else {
            $this->brandList = \App\Models\Brand::orderBy('name')->take(50)->get();
        }
    }

    public function addToList()
    {
        $this->validate();

        $this->productList[] = [
            'name' => $this->currentName,
            'unit_of_measure' => $this->currentUnit,
            'measure_value' => $this->currentMeasureValue,
            'stock_min' => $this->currentStockMin,
            'stock_max' => $this->currentStockMax,
            'description' => $this->currentDescription,
            'brand_id' => $this->currentBrandId,
            'model_id' => $this->currentModelId,
            'category_id' => $this->currentCategoryId,
        ];

        $this->resetCurrent();
        $this->persistState();
        $this->dispatch('productAdded');
    }

    public function confirmAction($action, $index)
    {
        $this->modalAction = $action;
        $this->modalItemIndex = $index;
        $this->modalMessage = $action === 'edit'
            ? '¿Editar este producto? Los datos se cargarán en el formulario para modificarlos.'
            : '¿Eliminar este producto de la lista?';
        $this->showConfirmModal = true;
    }

    public function executeAction()
    {
        if ($this->modalAction === 'delete') {
            $this->removeFromList($this->modalItemIndex);
            $this->dispatch('showToast', ['type' => 'success', 'message' => 'Producto eliminado de la lista.']);
        } elseif ($this->modalAction === 'edit') {
            $this->editItem($this->modalItemIndex);
            $this->dispatch('showToast', ['type' => 'info', 'message' => 'Producto cargado para edición.']);
        }
        $this->closeModal();
    }

    public function closeModal()
    {
        $this->showConfirmModal = false;
        $this->modalAction = null;
        $this->modalItemIndex = null;
        $this->modalMessage = '';
    }

    public function editItem($index)
    {
        $item = $this->productList[$index];
        $this->currentName = $item['name'];
        $this->currentUnit = $item['unit_of_measure'];
        $this->currentMeasureValue = $item['measure_value'];
        $this->currentStockMin = $item['stock_min'];
        $this->currentStockMax = $item['stock_max'];
        $this->currentDescription = $item['description'];
        $this->currentBrandId = $item['brand_id'];
        $this->currentModelId = $item['model_id'];
        $this->currentCategoryId = $item['category_id'];
        if ($this->currentModelId) {
            $model = ProductModel::with('brand', 'category')->find($this->currentModelId);
            if ($model) {
                $this->selectedModelDisplay = "{$model->brand->name} - {$model->name} - {$model->category->name}";
            }
        }
        $this->removeFromList($index, false);
    }

    public function removeFromList($index, $showMessage = true)
    {
        unset($this->productList[$index]);
        $this->productList = array_values($this->productList);
        $this->persistState();
    }

    // Confirmación para guardar múltiples productos
    public function confirmSaveAll()
    {
        if (empty($this->productList)) {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'Agrega al menos un producto antes de guardar.']);

            return;
        }
        $this->showSaveConfirmModal = true;
    }

    public function saveAll()
    {
        foreach ($this->productList as $prod) {
            Product::create($prod);
        }
        $this->clearPersistedState();
        $this->dispatch('showToast', ['type' => 'success', 'message' => count($this->productList).' producto(s) creado(s) correctamente.']);
        $this->redirectRoute('products.index');
    }

    // Confirmación para actualizar un producto (edición)
    public function confirmUpdate()
    {
        $this->validate();
        $this->showSaveConfirmModal = true;
    }

    public function update()
    {
        $product = Product::findOrFail($this->editingId);
        $product->update([
            'name' => $this->currentName,
            'unit_of_measure' => $this->currentUnit,
            'measure_value' => $this->currentMeasureValue,
            'stock_min' => $this->currentStockMin,
            'stock_max' => $this->currentStockMax,
            'description' => $this->currentDescription,
            'brand_id' => $this->currentBrandId,
            'model_id' => $this->currentModelId,
            'category_id' => $this->currentCategoryId,
        ]);
        $this->clearPersistedState();
        $this->dispatch('showToast', ['type' => 'success', 'message' => 'Producto actualizado correctamente.']);
        $this->redirectRoute('products.index');
    }

    public function render()
    {
        return view('livewire.inventory.products.form')->layout('components.layouts.app');
    }
}
