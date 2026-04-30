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

    // Nuevas propiedades para la confirmación
    public $confirmingAction = null;   // 'resolve' o 'create_ot'
    public $confirmingTicketId = null;

    public function mount()
    {
        if (Auth::user()->cannot('access noc panel')) {
            abort(403, 'No tienes acceso al panel NOC.');
        }

        $this->tickets = Ticket::where('requires_noc', true)
            ->where('status', 'pending')
            ->get();

        // Si se recibe ?ticket_id=X en la URL, abrir ese ticket automáticamente
        if ($ticketId = request()->get('ticket_id')) {
            $this->viewDetail($ticketId);
        }
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

    // Métodos que solo PREPARAN la acción (no la ejecutan aún)
    public function promptResolveRemote($ticketId)
    {
        $this->confirmingAction = 'resolve';
        $this->confirmingTicketId = $ticketId;
    }

    public function promptCreateWorkOrder($ticketId)
    {
        $this->confirmingAction = 'create_ot';
        $this->confirmingTicketId = $ticketId;
    }

    // Método que ejecuta la acción confirmada
    public function executeConfirmedAction()
    {
        if ($this->confirmingAction === 'resolve') {
            $this->resolveRemote($this->confirmingTicketId);
        } elseif ($this->confirmingAction === 'create_ot') {
            $this->createWorkOrder($this->confirmingTicketId);
        }

        // Limpiar confirmación
        $this->confirmingAction = null;
        $this->confirmingTicketId = null;
    }

    // Cancelar la confirmación
    public function cancelConfirmation()
    {
        $this->confirmingAction = null;
        $this->confirmingTicketId = null;
    }

    // TUS MÉTODOS ORIGINALES (sin cambios en su lógica)
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