<?php

namespace App\Livewire\Inventory;

use Livewire\Component;
use App\Models\Product;

class ProductShow extends Component
{
    public $product;

    public function mount($id)
    {
        $this->product = Product::with('movements')->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.inventory.products.show')->layout('components.layouts.app');
    }
}