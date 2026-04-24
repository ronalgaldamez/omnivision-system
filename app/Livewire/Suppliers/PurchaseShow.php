<?php

namespace App\Livewire\Suppliers;

use Livewire\Component;
use App\Models\Purchase;

class PurchaseShow extends Component
{
    public $purchase;

    public function mount($id)
    {
        $this->purchase = Purchase::with('supplier', 'items.product', 'user')->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.suppliers.purchase-show', ['purchase' => $this->purchase])->layout('components.layouts.app');
    }
}