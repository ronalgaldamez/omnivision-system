<?php

namespace App\Livewire\Inventory;

use Livewire\Component;
use App\Models\Product;
use App\Models\Brand;
use App\Models\ProductModel;
use App\Models\Category;

class ProductForm extends Component
{
    public $editingId = null;
    public $productList = [];

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

    // Modal de confirmación de acciones (editar/eliminar)
    public $showConfirmModal = false;
    public $modalAction = null;
    public $modalItemIndex = null;
    public $modalMessage = '';

    // Modal de confirmación de guardado (similar a compras)
    public $showSaveConfirmModal = false;

    protected $rules = [
        'currentName' => 'required|string|max:255',
        'currentUnit' => 'required|string|max:50',
        'currentMeasureValue' => 'nullable|numeric|min:0',
        'currentStockMin' => 'required|integer|min:0',
        'currentStockMax' => 'nullable|integer|min:0',
        'currentDescription' => 'nullable|string',
        'currentBrandId' => 'nullable|exists:brands,id',
        'currentModelId' => 'nullable|exists:product_models,id',
        'currentCategoryId' => 'nullable|exists:categories,id',
    ];

    public function mount($id = null)
    {
        if ($id) {
            $this->editingId = $id;
            $product = Product::findOrFail($id);
            $this->currentName = $product->name;
            $this->currentUnit = $product->unit_of_measure;
            $this->currentMeasureValue = $product->measure_value;
            $this->currentStockMin = $product->stock_min;
            $this->currentStockMax = $product->stock_max;
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
        } else {
            $this->productList = [];
            $this->resetCurrent();
        }
    }

    public function resetCurrent()
    {
        $this->currentName = '';
        $this->currentUnit = 'unidad';
        $this->currentMeasureValue = null;
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
                ->where('name', 'like', '%' . $this->modelSearchTerm . '%')
                ->orWhereHas('brand', fn($q) => $q->where('name', 'like', '%' . $this->modelSearchTerm . '%'))
                ->orWhereHas('category', fn($q) => $q->where('name', 'like', '%' . $this->modelSearchTerm . '%'))
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
        }
    }

    public function clearModelSelection()
    {
        $this->currentModelId = '';
        $this->currentBrandId = '';
        $this->currentCategoryId = '';
        $this->selectedModelDisplay = '';
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
        $this->dispatch('showToast', ['type' => 'success', 'message' => count($this->productList) . ' producto(s) creado(s) correctamente.']);
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
        $this->dispatch('showToast', ['type' => 'success', 'message' => 'Producto actualizado correctamente.']);
        $this->redirectRoute('products.index');
    }

    public function render()
    {
        return view('livewire.inventory.products.form');
    }
}