<?php

namespace App\Livewire\Contracts;

use Livewire\Component;
use App\Models\Contract;
use App\Services\ContractDeliveryService;
use App\Services\WorkOrderService;

class ContractIndex extends Component
{
    public $search = '';
    public $confirmingCreateOt = null;
    public $confirmingSend = null;
    public $sentWhatsAppLink = null;

    public function createWorkOrder($contractId)
    {
        $contract = Contract::with('client')->findOrFail($contractId);

        $workOrder = app(WorkOrderService::class)->createFromContract($contract);

        $this->dispatch('show-toast', type: 'success', message: 'OT #' . $workOrder->id . ' creada desde contrato.');
        $this->confirmingCreateOt = null;
    }

    public function promptSend($contractId)
    {
        $contract = Contract::with('client')->findOrFail($contractId);
        if ($contract->status === 'active') {
            $this->dispatch('show-toast', type: 'info', message: 'El contrato ya fue enviado y está activo.');
            return;
        }
        $this->confirmingSend = $contractId;
        $this->sentWhatsAppLink = null;
    }

    public function cancelSend()
    {
        $this->confirmingSend = null;
        $this->sentWhatsAppLink = null;
    }

    public function sendContract($contractId)
    {
        $contract = Contract::with('client')->findOrFail($contractId);

        $result = app(ContractDeliveryService::class)->send($contract);

        if ($result['channel'] === 'whatsapp') {
            $this->sentWhatsAppLink = app(ContractDeliveryService::class)->whatsAppShareUrl($contract);
            $this->dispatch('show-toast', type: 'success', message: 'Contrato listo. Compartilo por WhatsApp.');
        } elseif ($result['channel'] === 'email') {
            $this->dispatch('show-toast', type: 'success', message: 'Contrato enviado por correo y activado.');
            $this->confirmingSend = null;
        } else {
            $this->dispatch('show-toast', type: 'success', message: 'Contrato activado sin envío (cliente sin preferencia).');
            $this->confirmingSend = null;
        }
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
