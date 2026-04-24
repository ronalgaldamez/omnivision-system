<?php

namespace App\Livewire\Suppliers;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Purchase;

class PurchaseIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $dateFrom = '';
    public $dateTo = '';

    public function render()
    {
        $purchases = Purchase::with('supplier', 'user')
            ->when($this->search, function ($q) {
                $q->where('invoice_number', 'like', '%' . $this->search . '%')
                    ->orWhereHas('supplier', function ($q2) {
                        $q2->where('name', 'like', '%' . $this->search . '%');
                    });
            })
            ->when($this->dateFrom, fn($q) => $q->whereDate('purchase_date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('purchase_date', '<=', $this->dateTo))
            ->orderBy('purchase_date', 'desc')
            ->paginate(10);

        return view('livewire.suppliers.purchase-index', compact('purchases'))->layout('components.layouts.app');
    }
}