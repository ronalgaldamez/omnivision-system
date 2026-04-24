<?php

namespace App\Livewire\Admin\Catalog;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Brand;
use App\Models\ProductModel;
use App\Models\Category;

class CatalogManager extends Component
{
    use WithPagination;

    public $activeTab = 'models';

    public $showModal = false;          // modal para crear/editar
    public $modalType = '';
    public $editingId = null;

    // Campos para modelo
    public $modelBrandId = '';
    public $modelName = '';
    public $modelCategoryId = '';

    // Campos para marca
    public $brandName = '';
    public $brandDescription = '';

    // Campos para categoría
    public $categoryName = '';
    public $categoryDescription = '';

    // Búsquedas
    public $searchModels = '';
    public $searchBrands = '';
    public $searchCategories = '';

    // Confirmaciones
    public $showConfirmModal = false;
    public $confirmAction = null;   // 'deleteBrand', 'deleteCategory', 'deleteModel', 'saveBrand', 'saveCategory', 'saveModel'
    public $confirmId = null;
    public $confirmMessage = '';

    protected $rules = [
        'modelBrandId' => 'required|exists:brands,id',
        'modelName' => 'required|string|max:255',
        'modelCategoryId' => 'required|exists:categories,id',
    ];

    public function updatedActiveTab($value)
    {
        $this->resetPage();
        $this->resetValidation();
    }

    // ==================== MODELOS ====================
    public function openModelModal($id = null)
    {
        $this->resetValidation();
        $this->modalType = 'model';
        $this->editingId = $id;
        if ($id) {
            $model = ProductModel::findOrFail($id);
            $this->modelBrandId = $model->brand_id;
            $this->modelName = $model->name;
            $this->modelCategoryId = $model->category_id;
        } else {
            $this->modelBrandId = '';
            $this->modelName = '';
            $this->modelCategoryId = '';
        }
        $this->showModal = true;
    }

    public function confirmSaveModel()
    {
        $this->validate([
            'modelBrandId' => 'required|exists:brands,id',
            'modelName' => 'required|string|max:255',
            'modelCategoryId' => 'required|exists:categories,id',
        ]);
        $this->confirmAction = 'saveModel';
        $this->confirmMessage = $this->editingId ? '¿Actualizar este modelo?' : '¿Crear este modelo?';
        $this->showConfirmModal = true;
    }

    public function saveModel()
    {
        ProductModel::updateOrCreate(['id' => $this->editingId], [
            'brand_id' => $this->modelBrandId,
            'name' => $this->modelName,
            'category_id' => $this->modelCategoryId,
        ]);
        $this->showModal = false;
        $this->dispatch('showToast', ['type' => 'success', 'message' => 'Modelo guardado correctamente.']);
        $this->reset(['modelBrandId', 'modelName', 'modelCategoryId', 'editingId']);
        $this->showConfirmModal = false;
    }

    public function confirmDeleteModel($id)
    {
        $this->confirmAction = 'deleteModel';
        $this->confirmId = $id;
        $this->confirmMessage = '¿Eliminar este modelo? Los productos asociados perderán la referencia.';
        $this->showConfirmModal = true;
    }

    public function deleteModel()
    {
        $model = ProductModel::findOrFail($this->confirmId);
        if ($model->products()->count() > 0) {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'No se puede eliminar un modelo con productos asociados.']);
        } else {
            $model->delete();
            $this->dispatch('showToast', ['type' => 'success', 'message' => 'Modelo eliminado.']);
        }
        $this->showConfirmModal = false;
        $this->confirmId = null;
        $this->confirmAction = null;
    }

    // ==================== MARCAS ====================
    public function openBrandModal($id = null)
    {
        $this->resetValidation();
        $this->modalType = 'brand';
        $this->editingId = $id;
        if ($id) {
            $brand = Brand::findOrFail($id);
            $this->brandName = $brand->name;
            $this->brandDescription = $brand->description;
        } else {
            $this->brandName = '';
            $this->brandDescription = '';
        }
        $this->showModal = true;
    }

    public function confirmSaveBrand()
    {
        $rules = ['brandName' => 'required|string|max:255'];
        if (!$this->editingId) {
            $rules['brandName'] .= '|unique:brands,name';
        } else {
            $rules['brandName'] .= '|unique:brands,name,' . $this->editingId;
        }
        $this->validate($rules);
        $this->confirmAction = 'saveBrand';
        $this->confirmMessage = $this->editingId ? '¿Actualizar esta marca?' : '¿Crear esta marca?';
        $this->showConfirmModal = true;
    }

    public function saveBrand()
    {
        Brand::updateOrCreate(['id' => $this->editingId], [
            'name' => $this->brandName,
            'description' => $this->brandDescription,
        ]);
        $this->showModal = false;
        $this->dispatch('showToast', ['type' => 'success', 'message' => 'Marca guardada correctamente.']);
        $this->reset(['brandName', 'brandDescription', 'editingId']);
        $this->showConfirmModal = false;
    }

    public function confirmDeleteBrand($id)
    {
        $this->confirmAction = 'deleteBrand';
        $this->confirmId = $id;
        $this->confirmMessage = '¿Eliminar esta marca? Los modelos asociados se perderán.';
        $this->showConfirmModal = true;
    }

    public function deleteBrand()
    {
        $brand = Brand::findOrFail($this->confirmId);
        if ($brand->productModels()->count() > 0) {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'No se puede eliminar una marca con modelos asociados.']);
        } else {
            $brand->delete();
            $this->dispatch('showToast', ['type' => 'success', 'message' => 'Marca eliminada.']);
        }
        $this->showConfirmModal = false;
        $this->confirmId = null;
        $this->confirmAction = null;
    }

    // ==================== CATEGORÍAS ====================
    public function openCategoryModal($id = null)
    {
        $this->resetValidation();
        $this->modalType = 'category';
        $this->editingId = $id;
        if ($id) {
            $cat = Category::findOrFail($id);
            $this->categoryName = $cat->name;
            $this->categoryDescription = $cat->description;
        } else {
            $this->categoryName = '';
            $this->categoryDescription = '';
        }
        $this->showModal = true;
    }

    public function confirmSaveCategory()
    {
        $rules = ['categoryName' => 'required|string|max:255'];
        if (!$this->editingId) {
            $rules['categoryName'] .= '|unique:categories,name';
        } else {
            $rules['categoryName'] .= '|unique:categories,name,' . $this->editingId;
        }
        $this->validate($rules);
        $this->confirmAction = 'saveCategory';
        $this->confirmMessage = $this->editingId ? '¿Actualizar esta categoría?' : '¿Crear esta categoría?';
        $this->showConfirmModal = true;
    }

    public function saveCategory()
    {
        Category::updateOrCreate(['id' => $this->editingId], [
            'name' => $this->categoryName,
            'description' => $this->categoryDescription,
        ]);
        $this->showModal = false;
        $this->dispatch('showToast', ['type' => 'success', 'message' => 'Categoría guardada correctamente.']);
        $this->reset(['categoryName', 'categoryDescription', 'editingId']);
        $this->showConfirmModal = false;
    }

    public function confirmDeleteCategory($id)
    {
        $this->confirmAction = 'deleteCategory';
        $this->confirmId = $id;
        $this->confirmMessage = '¿Eliminar esta categoría? Los productos asociados perderán la referencia.';
        $this->showConfirmModal = true;
    }

    public function deleteCategory()
    {
        $cat = Category::findOrFail($this->confirmId);
        if ($cat->products()->count() > 0) {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'No se puede eliminar una categoría con productos asociados.']);
        } else {
            $cat->delete();
            $this->dispatch('showToast', ['type' => 'success', 'message' => 'Categoría eliminada.']);
        }
        $this->showConfirmModal = false;
        $this->confirmId = null;
        $this->confirmAction = null;
    }

    public function executeConfirmedAction()
    {
        if ($this->confirmAction === 'saveModel')
            $this->saveModel();
        elseif ($this->confirmAction === 'deleteModel')
            $this->deleteModel();
        elseif ($this->confirmAction === 'saveBrand')
            $this->saveBrand();
        elseif ($this->confirmAction === 'deleteBrand')
            $this->deleteBrand();
        elseif ($this->confirmAction === 'saveCategory')
            $this->saveCategory();
        elseif ($this->confirmAction === 'deleteCategory')
            $this->deleteCategory();
        $this->showConfirmModal = false;
        $this->confirmAction = null;
    }

    public function render()
    {
        $models = ProductModel::with('brand', 'category')
            ->when($this->searchModels, fn($q) => $q->where('name', 'like', '%' . $this->searchModels . '%')
                ->orWhereHas('brand', fn($q2) => $q2->where('name', 'like', '%' . $this->searchModels . '%'))
                ->orWhereHas('category', fn($q3) => $q3->where('name', 'like', '%' . $this->searchModels . '%')))
            ->orderBy('name')
            ->paginate(10);

        $brands = Brand::when($this->searchBrands, fn($q) => $q->where('name', 'like', '%' . $this->searchBrands . '%'))
            ->orderBy('name')
            ->paginate(10);

        $categories = Category::when($this->searchCategories, fn($q) => $q->where('name', 'like', '%' . $this->searchCategories . '%'))
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.admin.catalog.catalog-manager', compact('models', 'brands', 'categories'))->layout('components.layouts.app');
    }
}