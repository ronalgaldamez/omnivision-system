<?php

namespace App\Livewire\Inventory;

use Livewire\Component;
use App\Models\Product;
use App\Models\Movement;

class KardexIndex extends Component
{
    public $product_id = '';
    public $type = '';
    public $date_from = '';
    public $date_to = '';

    protected $queryString = ['product_id', 'type', 'date_from', 'date_to'];

    public function render()
    {
        $products = Product::orderBy('name')->get();

        $movements = Movement::with('product', 'user')
            ->when($this->product_id, fn($q) => $q->where('product_id', $this->product_id))
            ->when($this->type, fn($q) => $q->where('type', $this->type))
            ->when($this->date_from, fn($q) => $q->whereDate('created_at', '>=', $this->date_from))
            ->when($this->date_to, fn($q) => $q->whereDate('created_at', '<=', $this->date_to))
            ->orderBy('created_at', 'asc')
            ->get();

        $balanceQty = 0;
        $balanceValue = 0;
        $balanceAvgCost = 0;
        $items = [];

        foreach ($movements as $index => $mov) {
            $item = clone $mov;  // clonamos para no modificar el original
            $item->line_number = $index + 1;

            $isEntry = in_array($mov->type, ['entry', 'technician_return']);
            $isExit = in_array($mov->type, ['exit', 'technician_out', 'damage', 'return_to_supplier']);

            if ($isEntry) {
                $newTotalQty = $balanceQty + $mov->quantity;
                $newTotalValue = $balanceValue + ($mov->unit_cost * $mov->quantity);
                $newAvgCost = ($newTotalQty > 0) ? $newTotalValue / $newTotalQty : 0;

                $item->entry_qty = $mov->quantity;
                $item->entry_cost = $mov->unit_cost;
                $item->entry_total = $mov->unit_cost * $mov->quantity;
                $item->exit_qty = null;
                $item->exit_cost = null;
                $item->exit_total = null;

                $balanceQty = $newTotalQty;
                $balanceValue = $newTotalValue;
                $balanceAvgCost = $newAvgCost;
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

        return view('livewire.inventory.kardex.index', compact('products', 'items'))
            ->layout('components.layouts.app');
    }
}