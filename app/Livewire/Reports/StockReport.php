<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\Product;

class StockReport extends Component
{
    public function render()
    {
        $products = Product::whereColumn('current_stock', '<=', 'stock_min')
            ->orderBy('current_stock', 'asc')
            ->get();
        return view('livewire.reports.stock-report', compact('products'))->layout('components.layouts.app');
    }
}   