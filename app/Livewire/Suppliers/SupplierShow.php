<?php

namespace App\Livewire\Suppliers;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Supplier;
use App\Models\Purchase;
use App\Models\Movement;

class SupplierShow extends Component
{
    use WithPagination;

    public $supplier;
    public $purchaseModalOpen = false;
    public $selectedPurchase = null;

    public function mount($id)
    {
        $this->supplier = Supplier::with('purchases')->findOrFail($id);
    }

    public function showPurchaseDetail($purchaseId)
    {
        $this->selectedPurchase = Purchase::with('items.product')->findOrFail($purchaseId);
        $this->purchaseModalOpen = true;
    }

    public function closeModal()
    {
        $this->purchaseModalOpen = false;
        $this->selectedPurchase = null;
    }

    public function render()
    {
        $purchases = $this->supplier->purchases()->orderBy('purchase_date', 'desc')->paginate(10, ['*'], 'purchasesPage');
        $movements = Movement::whereHas('purchase', function ($q) {
            $q->where('supplier_id', $this->supplier->id);
        })->with('product', 'user')->orderBy('created_at', 'desc')->paginate(10, ['*'], 'movementsPage');

        return view('livewire.suppliers.supplier-show', [
            'purchases' => $purchases,
            'movements' => $movements,
        ])->layout('components.layouts.app');
    }
}