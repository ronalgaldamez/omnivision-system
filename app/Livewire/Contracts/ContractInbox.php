<?php

namespace App\Livewire\Contracts;

use Livewire\Component;
use App\Models\Ticket;
use App\Models\Contract;
use App\Models\WorkOrder;
use App\Services\SlaService;
use Illuminate\Support\Facades\Auth;

class ContractInbox extends Component
{
    public $activeTab = 'pending';
    public $tickets;

    public $showDetailModal = false;
    public $selectedTicket = null;

    public $confirmingAction = null;
    public $confirmingTicketId = null;

    public function mount()
    {
        if (Auth::user()->cannot('access_contracts_inbox')) {
            abort(403);
        }

        $this->loadTickets();

        if ($ticketId = request()->get('ticket_id')) {
            $this->viewDetail($ticketId);
        }
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
        $this->closeModal();
        $this->cancelConfirmation();
        $this->loadTickets();
    }

    public function loadTickets()
    {
        $query = Ticket::where('requires_contract', true)->with('client', 'createdBy');

        switch ($this->activeTab) {
            case 'pending':
                $query->whereNull('contracts_started_at')->whereNotIn('status', ['resolved', 'cancelled']);
                break;
            case 'in_progress':
                $query->whereNotNull('contracts_started_at')->whereNull('contracts_ended_at');
                break;
            case 'completed':
                $query->whereNotNull('contracts_ended_at');
                break;
        }

        $this->tickets = $query->orderByRaw("CASE priority WHEN 'P1' THEN 1 WHEN 'P2' THEN 2 WHEN 'P3' THEN 3 WHEN 'P4' THEN 4 ELSE 5 END ASC")
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function viewDetail($ticketId)
    {
        $this->selectedTicket = Ticket::with('client', 'createdBy')->find($ticketId);
        $this->showDetailModal = true;
    }

    public function acceptTicket($ticketId)
    {
        $ticket = Ticket::find($ticketId);
        if ($ticket && is_null($ticket->contracts_started_at)) {
            $ticket->update([
                'contracts_started_at' => now(),
            ]);
        }
        $this->loadTickets();
        $this->closeModal();
    }

    public function promptGenerateContract($ticketId)
    {
        $this->confirmingAction = 'generate';
        $this->confirmingTicketId = $ticketId;
    }

    public function promptReject($ticketId)
    {
        $this->confirmingAction = 'reject';
        $this->confirmingTicketId = $ticketId;
    }

    public function executeConfirmedAction()
    {
        if ($this->confirmingAction === 'generate') {
            $this->generateContract($this->confirmingTicketId);
        } elseif ($this->confirmingAction === 'reject') {
            $this->rejectTicket($this->confirmingTicketId);
        }
        $this->confirmingAction = null;
        $this->confirmingTicketId = null;
    }

    public function cancelConfirmation()
    {
        $this->confirmingAction = null;
        $this->confirmingTicketId = null;
    }

    public function generateContract($ticketId)
    {
        $ticket = Ticket::with('client')->find($ticketId);
        if ($ticket && $ticket->requires_contract) {
            $client = $ticket->client;
            $serviceName = $ticket->service_type;

            Contract::create([
                'client_id' => $ticket->client_id,
                'plan_id' => $ticket->plan_id,
                'zone_id' => $ticket->zone_id,
                'service_type' => $serviceName,
                'price' => null,
                'installation_address' => $client?->installation_address,
                'latitude' => $client?->latitude,
                'longitude' => $client?->longitude,
                'contract_date' => now(),
                'status' => 'active',
            ]);

            WorkOrder::create([
                'ticket_id' => $ticket->id,
                'client_id' => $ticket->client_id,
                'description' => $ticket->description,
                'service_type' => $serviceName,
                'status' => 'pending',
                'created_by' => Auth::id(),
            ]);

            $ticket->contracts_ended_at = now();
            $ticket->status = 'in_progress';
            $ticket->save();

            app(SlaService::class)->evaluateSla($ticket);
        }
        $this->loadTickets();
        $this->closeModal();
    }

    public function rejectTicket($ticketId)
    {
        $ticket = Ticket::find($ticketId);
        if ($ticket) {
            $ticket->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancellation_reason' => 'Rechazado por Contratos',
            ]);
        }
        $this->loadTickets();
        $this->closeModal();
    }

    public function closeModal()
    {
        $this->showDetailModal = false;
        $this->selectedTicket = null;
    }

    public function render()
    {
        return view('livewire.contracts.contract-inbox')->layout('components.layouts.app');
    }
}
