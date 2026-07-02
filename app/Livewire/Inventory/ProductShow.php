<?php

namespace App\Livewire\Inventory;

use App\Models\Product;
use App\Traits\ManagesProductPackaging;
use Livewire\Component;

class ProductShow extends Component
{
    use ManagesProductPackaging;

    public $product;

    public function mount($id)
    {
        $this->product = Product::with('movements')->findOrFail($id);
        $this->currentProductId = $this->product->id;
        $this->loadPackagingsForProduct($this->product->id);
        $this->initPackagingState();
    }

    public function render()
    {
        return view('livewire.inventory.products.show')->layout('components.layouts.app');
    }
}
