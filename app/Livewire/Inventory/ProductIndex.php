<?php

namespace App\Livewire\Inventory;

use Livewire\Component;
use App\Models\Product;
use Livewire\WithPagination;

class ProductIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $showDeleteModal = false;
    public $productIdToDelete = null;

    public function render()
    {
        $products = Product::where('name', 'like', '%' . $this->search . '%')
            ->orWhere('sku', 'like', '%' . $this->search . '%')
            ->paginate(10);

        return view('livewire.inventory.products.index', compact('products'));
    }

    public function confirmDelete($id)
    {
        $this->productIdToDelete = $id;
        $this->showDeleteModal = true;
    }

    public function deleteProduct()
    {
        $product = Product::findOrFail($this->productIdToDelete);
        if ($product->current_stock == 0) {
            $product->delete();
            $this->dispatch('showToast', ['type' => 'success', 'message' => 'Producto eliminado.']);
        } else {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'No se puede eliminar producto con stock > 0.']);
        }
        $this->showDeleteModal = false;
        $this->productIdToDelete = null;
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->productIdToDelete = null;
    }
}