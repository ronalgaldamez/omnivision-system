<?php

namespace App\Livewire\Inventory;

use App\Models\Branch;
use App\Models\BranchInventory;
use App\Models\Movement;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DistributionForm extends Component
{
    public $productSearch = '';

    public $productSearchResults = [];

    public $selectedProductId = null;

    public $selectedProduct = null;

    public $globalStock = 0;

    public $alreadyAllocated = 0;

    public $available = 0;

    public $allocations = [];

    public $activeBranchId = null;

    public function updatedProductSearch()
    {
        if (strlen($this->productSearch) >= 2) {
            $this->productSearchResults = Product::where('name', 'like', '%'.$this->productSearch.'%')
                ->orWhere('sku', 'like', '%'.$this->productSearch.'%')
                ->limit(10)->get();
        } else {
            $this->productSearchResults = [];
        }
    }

    public function selectProduct($id)
    {
        $product = Product::find($id);
        if (! $product) {
            return;
        }

        $this->selectedProductId = $product->id;
        $this->selectedProduct = $product;
        $this->productSearch = $product->name.' ('.$product->sku.')';
        $this->productSearchResults = [];

        $this->loadProductData();
    }

    public function clearProduct()
    {
        $this->selectedProductId = null;
        $this->selectedProduct = null;
        $this->productSearch = '';
        $this->globalStock = 0;
        $this->alreadyAllocated = 0;
        $this->available = 0;
        $this->allocations = [];
    }

    private function loadProductData()
    {
        $this->globalStock = (float) $this->selectedProduct->current_stock;
        $this->activeBranchId = auth()->user()->activeBranchId();

        $inventories = BranchInventory::where('product_id', $this->selectedProductId)
            ->pluck('allocated_quantity', 'branch_id');

        $this->alreadyAllocated = (float) $inventories->sum();
        $this->available = $this->globalStock - $this->alreadyAllocated;

        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        $this->allocations = [];
        foreach ($branches as $branch) {
            $current = (float) ($inventories[$branch->id] ?? 0);
            $this->allocations[] = [
                'branch_id' => $branch->id,
                'branch_name' => $branch->name,
                'current_allocated' => $current,
                'new_quantity' => 0,
            ];
        }
    }

    public function save()
    {
        $sum = array_sum(array_map('floatval', array_column($this->allocations, 'new_quantity')));

        if ($sum <= 0) {
            $this->dispatch('show-toast', type: 'error', message: 'Ingresá al menos una cantidad para repartir.');

            return;
        }

        if ($sum > $this->available) {
            $this->dispatch('show-toast', type: 'error', message: "La suma a repartir ({$sum}) supera el disponible ({$this->available}).");

            return;
        }

        foreach ($this->allocations as $alloc) {
            if ($alloc['new_quantity'] > 0) {
                $existing = BranchInventory::where('branch_id', $alloc['branch_id'])
                    ->where('product_id', $this->selectedProductId)
                    ->first();

                if ($existing) {
                    $existing->increment('allocated_quantity', $alloc['new_quantity']);
                } else {
                    BranchInventory::create([
                        'branch_id' => $alloc['branch_id'],
                        'product_id' => $this->selectedProductId,
                        'allocated_quantity' => $alloc['new_quantity'],
                    ]);
                }

                Movement::create([
                    'product_id' => $this->selectedProductId,
                    'type' => 'branch_allocation',
                    'quantity' => $alloc['new_quantity'],
                    'unit_cost' => $this->selectedProduct->average_cost ?? 0,
                    'total_value' => ($alloc['new_quantity'] * ($this->selectedProduct->average_cost ?? 0)),
                    'description' => 'Repartición a '.$alloc['branch_name'],
                    'user_id' => Auth::id(),
                    'branch_id' => $alloc['branch_id'],
                    'reference_type' => 'distribution',
                    'reference_id' => $alloc['branch_id'],
                ]);
            }
        }

        $this->loadProductData();
        $this->dispatch('show-toast', type: 'success', message: 'Repartición guardada correctamente.');
    }

    public function render()
    {
        return view('livewire.inventory.distribution-form')->layout('components.layouts.app');
    }
}
