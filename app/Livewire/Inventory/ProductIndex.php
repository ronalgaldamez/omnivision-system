<?php

namespace App\Livewire\Inventory;

use Livewire\Component;
use App\Models\Product;
use App\Models\Branch;
use Livewire\WithPagination;

class ProductIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $showDeleteModal = false;
    public $productIdToDelete = null;

    public function render()
    {
        $activeBranchId = auth()->user()->activeBranchId();
        $activeBranch = $activeBranchId ? Branch::find($activeBranchId) : null;

        $products = Product::with(['branchInventories' => function ($q) use ($activeBranchId) {
            if ($activeBranchId) {
                $q->where('branch_id', $activeBranchId);
            }
        }])
            ->when($activeBranchId, function ($q) use ($activeBranchId) {
                $q->whereHas('branchInventories', function ($q) use ($activeBranchId) {
                    $q->where('branch_id', $activeBranchId)->where('allocated_quantity', '>', 0);
                });
            })
            ->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('sku', 'like', '%' . $this->search . '%');
            })
            ->paginate(10);

        return view('livewire.inventory.products.index', compact('products', 'activeBranch'));
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
