<?php

namespace App\Livewire\Inventory;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Movement;

class MovementIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $typeFilter = '';
    public $dateFrom = '';
    public $dateTo = '';

    public function render()
    {
        $movements = Movement::with('product', 'user')
            ->when($this->search, function ($q) {
                $q->whereHas('product', function ($q2) {
                    $q2->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('sku', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->typeFilter, fn($q) => $q->where('type', $this->typeFilter))
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('livewire.inventory.movements.index', compact('movements'))->layout('components.layouts.app');
    }
}