<?php

namespace App\Livewire\Inventory;

use Livewire\Component;
use App\Models\Product;
use App\Models\Movement;
use App\Models\Branch;

class KardexIndex extends Component
{
    public $product_id = '';
    public $type = '';
    public $date_from = '';
    public $date_to = '';

    public $productSearch = '';
    public $productResults = [];
    public $showProductModal = false;
    public $productList = [];
    public $productListSearch = '';

    protected $queryString = ['product_id', 'type', 'date_from', 'date_to'];

    public function updatedProductSearch()
    {
        if (strlen($this->productSearch) >= 2) {
            $this->productResults = Product::where('name', 'like', '%' . $this->productSearch . '%')
                ->orWhere('sku', 'like', '%' . $this->productSearch . '%')
                ->limit(10)
                ->get();
        } else {
            $this->productResults = [];
        }
    }

    public function selectProduct($id, $name)
    {
        $this->product_id = $id;
        $this->productSearch = $name;
        $this->productResults = [];
    }

    public function clearProduct()
    {
        $this->product_id = '';
        $this->productSearch = '';
        $this->productResults = [];
    }

    public function openProductModal()
    {
        $this->productListSearch = '';
        $this->productList = Product::orderBy('name')->take(50)->get();
        $this->showProductModal = true;
    }

    public function closeProductModal()
    {
        $this->showProductModal = false;
        $this->productListSearch = '';
        $this->productList = [];
    }

    public function updatedProductListSearch()
    {
        if (strlen($this->productListSearch) >= 2) {
            $this->productList = Product::where('name', 'like', '%' . $this->productListSearch . '%')
                ->orWhere('sku', 'like', '%' . $this->productListSearch . '%')
                ->orderBy('name')->take(50)->get();
        } else {
            $this->productList = Product::orderBy('name')->take(50)->get();
        }
    }

    public function selectProductFromList($id)
    {
        $product = Product::find($id);
        if ($product) {
            $this->selectProduct($product->id, $product->name . ' (' . $product->sku . ')');
            $this->closeProductModal();
        }
    }

    public function render()
    {
        $activeBranchId = auth()->user()->activeBranchId();
        $activeBranch = $activeBranchId ? Branch::find($activeBranchId) : null;

        $movements = Movement::with('product', 'user', 'branch')
            ->when($this->product_id, fn($q) => $q->where('product_id', $this->product_id))
            ->when($this->type, fn($q) => $q->where('type', $this->type))
            ->when($this->date_from, fn($q) => $q->whereDate('created_at', '>=', $this->date_from))
            ->when($this->date_to, fn($q) => $q->whereDate('created_at', '<=', $this->date_to))
            ->when($activeBranchId, fn($q) => $q->where('branch_id', $activeBranchId))
            ->orderBy('created_at', 'asc')
            ->get();

        $balanceQty = 0;
        $balanceValue = 0;
        $balanceAvgCost = 0;
        $items = [];

        foreach ($movements as $index => $mov) {
            $item = clone $mov;
            $item->line_number = $index + 1;

            $isEntry = in_array($mov->type, ['entry', 'technician_return']);
            $isExit = in_array($mov->type, ['exit', 'technician_out', 'damage', 'return_to_supplier', 'requisition_out']);
            $isAllocation = $mov->type === 'branch_allocation';

            if ($isEntry) {
                $entryCost = $mov->unit_cost ?: 0;
                $newTotalQty = $balanceQty + $mov->quantity;
                $newTotalValue = $balanceValue + ($entryCost * $mov->quantity);
                $newAvgCost = ($newTotalQty > 0) ? $newTotalValue / $newTotalQty : 0;

                $item->entry_qty = $mov->quantity;
                $item->entry_cost = $entryCost;
                $item->entry_total = $entryCost * $mov->quantity;
                $item->exit_qty = null;
                $item->exit_cost = null;
                $item->exit_total = null;

                $balanceQty = $newTotalQty;
                $balanceValue = $newTotalValue;
                $balanceAvgCost = $newAvgCost;
            } elseif ($isAllocation) {
                if ($activeBranchId) {
                    $entryCost = $mov->unit_cost ?: 0;
                    $newTotalQty = $balanceQty + $mov->quantity;
                    $newTotalValue = $balanceValue + ($entryCost * $mov->quantity);
                    $newAvgCost = ($newTotalQty > 0) ? $newTotalValue / $newTotalQty : 0;

                    $item->entry_qty = $mov->quantity;
                    $item->entry_cost = $entryCost;
                    $item->entry_total = $entryCost * $mov->quantity;
                    $item->exit_qty = null;
                    $item->exit_cost = null;
                    $item->exit_total = null;

                    $balanceQty = $newTotalQty;
                    $balanceValue = $newTotalValue;
                    $balanceAvgCost = $newAvgCost;
                } else {
                    $exitCost = $balanceAvgCost;
                    $exitTotal = $exitCost * $mov->quantity;

                    $item->entry_qty = null;
                    $item->entry_cost = null;
                    $item->entry_total = null;
                    $item->exit_qty = $mov->quantity;
                    $item->exit_cost = $exitCost;
                    $item->exit_total = $exitTotal;

                    $balanceQty -= $mov->quantity;
                    $balanceValue -= $exitTotal;
                    $balanceAvgCost = ($balanceQty > 0) ? $balanceValue / $balanceQty : 0;
                }
            } elseif ($isExit) {
                $exitCost = $balanceAvgCost;
                $exitTotal = $exitCost * $mov->quantity;

                $item->entry_qty = null;
                $item->entry_cost = null;
                $item->entry_total = null;
                $item->exit_qty = $mov->quantity;
                $item->exit_cost = $exitCost;
                $item->exit_total = $exitTotal;

                $balanceQty -= $mov->quantity;
                $balanceValue -= $exitTotal;
                $balanceAvgCost = ($balanceQty > 0) ? $balanceValue / $balanceQty : 0;
            } else {
                $item->entry_qty = null;
                $item->entry_cost = null;
                $item->entry_total = null;
                $item->exit_qty = null;
                $item->exit_cost = null;
                $item->exit_total = null;
            }

            $item->balance_qty = $balanceQty;
            $item->balance_cost = $balanceAvgCost;
            $item->balance_total = $balanceValue;

            $items[] = $item;
        }

        return view('livewire.inventory.kardex.index', compact('items', 'activeBranch'))
            ->layout('components.layouts.app');
    }
}
