<?php

namespace App\Livewire\Noc;

use Livewire\Component;
use App\Models\Ticket;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\Auth;

class NocPanel extends Component
{
    public $tickets;
    public $showDetailModal = false;
    public $selectedTicket = null;

    public function mount()
    {
        // Autorización con el nuevo permiso específico
        if (Auth::user()->cannot('access noc panel')) {
            abort(403, 'No tienes acceso al panel NOC.');
        }

        $this->tickets = Ticket::where('requires_noc', true)
            ->where('status', 'pending')
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
        if ($ticket && $ticket->requires_noc) {
            $ticket->status = 'resolved';
            $ticket->resolved_by = Auth::id();
            $ticket->resolved_at = now();
            $ticket->save();
            session()->flash('message', 'Ticket resuelto remotamente.');
        }
        $this->mount(); // refrescar la lista
        $this->closeModal(); // cerrar modal si estaba abierto
    }

    public function createWorkOrder($ticketId)
    {
        $ticket = Ticket::with('client')->find($ticketId);
        if ($ticket) {
            $workOrder = WorkOrder::create([
                'ticket_id' => $ticket->id,
                'client_id' => $ticket->client_id,
                'description' => $ticket->description,
                'service_type' => $ticket->service_type,
                'status' => 'pending',
            ]);
            $ticket->status = 'in_progress';
            $ticket->save();
            session()->flash('message', 'OT creada a partir del ticket.');
        }
        $this->mount(); // refrescar lista
        $this->closeModal(); // cerrar modal
    }

    public function render()
    {
        return view('livewire.noc.noc-panel')->layout('components.layouts.app');
    }
}