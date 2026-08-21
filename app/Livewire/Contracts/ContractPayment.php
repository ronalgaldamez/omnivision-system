<?php

namespace App\Livewire\Contracts;

use Livewire\Component;
use App\Models\Contract;
use App\Services\ContractPaymentService;

class ContractPayment extends Component
{
    public $contract_id = null;
    public $search = '';
    public $contracts;
    public $selectedContract = null;
    public $abono = null;
    public $charges = [];

    public function mount()
    {
        $this->loadContracts();
    }

    public function loadContracts()
    {
        $this->contracts = Contract::with('client', 'plan')
            ->whereIn('status', ['active', 'ready_to_send'])
            ->when($this->search, fn($q) => $q->whereHas('client', fn($q) => $q->where('name', 'like', "%{$this->search}%")))
            ->orderByDesc('created_at')
            ->get();
    }

    public function updatedSearch()
    {
        $this->loadContracts();
    }

    public function selectContract($id)
    {
        $this->contract_id = $id;
        $contract = Contract::with('client', 'plan', 'charges')->find($id);
        $this->selectedContract = $contract;
        $this->loadCharges();
        $this->abono = null;
    }

    public function loadCharges()
    {
        if ($this->contract_id) {
            $this->charges = Contract::find($this->contract_id)->charges()->orderByDesc('applied_at')->get();
        } else {
            $this->charges = [];
        }
    }

    public function calculateAbono()
    {
        if (!$this->contract_id) return;
        $contract = Contract::find($this->contract_id);
        $service = app(ContractPaymentService::class);
        $this->abono = $service->calculateAbono($contract);
    }

    public function registerAbono()
    {
        if (!$this->contract_id) return;
        $contract = Contract::find($this->contract_id);
        $service = app(ContractPaymentService::class);
        $service->registerAbono($contract);
        $this->dispatch('show-toast', type: 'success', message: 'Abono registrado.');
        $this->loadCharges();
        $this->abono = null;
    }

    public function registerCuota()
    {
        if (!$this->contract_id) return;
        $contract = Contract::find($this->contract_id);
        $service = app(ContractPaymentService::class);
        $service->registerCuota($contract);
        $this->dispatch('show-toast', type: 'success', message: 'Cuota registrada.');
        $this->loadCharges();
    }

    public function render()
    {
        return view('livewire.contracts.contract-payment')->layout('components.layouts.app');
    }
}
