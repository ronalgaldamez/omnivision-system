<?php

namespace App\Livewire\Noc;

use Livewire\Component;
use App\Models\Ticket;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\Auth;

class NocPanel extends Component
{
    public $tickets;
    public $selectedTicket = null;
    public $showDetailModal = false;

    public function mount()
    {
        $this->loadTickets();
    }

    public function loadTickets()
    {
        $this->tickets = Ticket::with('client')
            ->where('requires_noc', true)
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function viewDetail($ticketId)
    {
        $this->selectedTicket = Ticket::with('client')->find($ticketId);
        $this->showDetailModal = true;
    }

    public function closeModal()
    {
        $this->showDetailModal = false;
        $this->selectedTicket = null;
    }

    public function resolveRemote($ticketId)
    {
        $ticket = Ticket::find($ticketId);
        $ticket->status = 'resolved';
        $ticket->resolved_by = Auth::id();
        $ticket->resolved_at = now();
        $ticket->save();
        $this->loadTickets();
        $this->dispatch('showToast', ['type' => 'success', 'message' => 'Ticket resuelto remotamente.']);
    }

    public function createWorkOrder($ticketId)
    {
        $ticket = Ticket::find($ticketId);
        $workOrder = WorkOrder::create([
            'ticket_id' => $ticket->id,
            'client_id' => $ticket->client_id,
            'description' => $ticket->description,
            'service_type' => $ticket->service_type,
            'status' => 'pending',
            'notes' => $ticket->description,
        ]);
        $ticket->status = 'in_progress';
        $ticket->save();
        $this->loadTickets();
        $this->dispatch('showToast', ['type' => 'success', 'message' => 'OT creada a partir del ticket.']);
    }

    public function render()
    {
        return view('livewire.noc.noc-panel')->layout('components.layouts.app');
    }
}