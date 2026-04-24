<?php

namespace App\Livewire\TechnicianReturns;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\TechnicianReturn;

class ReturnList extends Component
{
    use WithPagination;

    public $typeFilter = '';
    public $search = '';

    public function render()
    {
        $returns = TechnicianReturn::with('product', 'user', 'request')
            ->when($this->typeFilter, fn($q) => $q->where('type', $this->typeFilter))
            ->when($this->search, fn($q) => $q->where('notes', 'like', '%' . $this->search . '%')
                ->orWhereHas('product', fn($q2) => $q2->where('name', 'like', '%' . $this->search . '%')))
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.technician-returns.return-list', compact('returns'));
    }
}