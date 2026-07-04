<?php

namespace App\Livewire\Inventory;

use App\Models\Product;
use App\Models\Branch;
use App\Traits\ManagesProductPackaging;
use Livewire\Component;

class ProductShow extends Component
{
    use ManagesProductPackaging;

    public $product;

    public function mount($id)
    {
        $this->product = Product::with('branchInventories')->findOrFail($id);
        $this->currentProductId = $this->product->id;
        $this->loadPackagingsForProduct($this->product->id);
        $this->initPackagingState();
    }

    public function render()
    {
        $activeBranchId = auth()->user()->activeBranchId();
        $activeBranch = $activeBranchId ? Branch::find($activeBranchId) : null;
        $branchAllocation = $activeBranch
            ? $this->product->branchInventories->firstWhere('branch_id', $activeBranch->id)?->allocated_quantity ?? 0
            : null;

        $movements = $this->product->movements()
            ->when($activeBranchId, function ($q) use ($activeBranchId) {
                $q->where('branch_id', $activeBranchId);
            })
            ->latest()
            ->take(5)
            ->get();

        $movementsCount = $this->product->movements()
            ->when($activeBranchId, function ($q) use ($activeBranchId) {
                $q->where('branch_id', $activeBranchId);
            })
            ->count();

        return view('livewire.inventory.products.show', compact('activeBranch', 'branchAllocation', 'movements', 'movementsCount'))->layout('components.layouts.app');
    }
}
