<?php

namespace App\Livewire\Contracts;

use Livewire\Component;
use App\Models\Contract;
use App\Services\WorkOrderService;

class ContractIndex extends Component
{
    public $search = '';
    public $confirmingCreateOt = null;

    public function createWorkOrder($contractId)
    {
        $contract = Contract::with('client')->findOrFail($contractId);

        $workOrder = app(WorkOrderService::class)->createFromContract($contract);

        $this->dispatch('show-toast', type: 'success', message: 'OT #' . $workOrder->id . ' creada desde contrato.');
        $this->confirmingCreateOt = null;
    }

    public function render()
    {
        $contracts = Contract::with(['client', 'plan', 'zone'])
            ->when($this->search, fn($q) => $q->whereHas('client', fn($q) => $q->where('name', 'like', "%{$this->search}%")))
            ->orderBy('created_at', 'desc')
            ->get();

        return view('livewire.contracts.contract-index', compact('contracts'))
            ->layout('components.layouts.app');
    }
}
