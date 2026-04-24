<?php

namespace App\Livewire\Noc;

use Livewire\Component;
use App\Models\Ticket;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\Auth;

class NocPanel extends Component
{
    public $tickets;

    public function mount()
    {
        $this->tickets = Ticket::where('requires_noc', true)
            ->where('status', 'pending')
            ->get();
    }

    public function resolveRemote($ticketId)
    {
        $ticket = Ticket::find($ticketId);
        $ticket->status = 'resolved';
        $ticket->resolved_by = Auth::id();
        $ticket->resolved_at = now();
        $ticket->save();
        $this->mount();
        session()->flash('message', 'Ticket resuelto remotamente.');
    }

    public function createWorkOrder($ticketId)
    {
        $ticket = Ticket::find($ticketId);
        $workOrder = WorkOrder::create([
            'ticket_id' => $ticket->id,
            'client_name' => $ticket->client->name,
            'client_phone' => $ticket->client->phone,
            'client_address' => $ticket->client->address,
            'status' => 'pending',  // pendiente de asignación
            'notes' => $ticket->description,
        ]);
        $ticket->status = 'in_progress';
        $ticket->save();
        $this->mount();
        session()->flash('message', 'OT creada a partir del ticket.');
    }

    public function render()
    {
        return view('livewire.noc.noc-panel')->layout('components.layouts.app');
    }
}