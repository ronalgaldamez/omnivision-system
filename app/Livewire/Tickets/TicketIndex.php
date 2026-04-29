<?php

namespace App\Livewire\Tickets;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Ticket;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\Auth;

class TicketIndex extends Component
{
    use WithPagination;

    public $statusFilter = '';
    public $search = '';

    // Para el modal de detalle
    public $showDetailModal = false;
    public $selectedTicket = null;

    public function render()
    {
        $user = Auth::user();
        $query = Ticket::with('client', 'createdBy', 'workOrder');

        // Permisos
        if ($user->can('view any tickets')) {
            // todos
        } elseif ($user->can('view own tickets')) {
            $query->where('created_by', $user->id);
        } else {
            $tickets = collect();
            return view('livewire.tickets.ticket-index', compact('tickets'))->layout('components.layouts.app');
        }

        // Filtro NOC especial (si tiene 'view pending noc tickets' y no 'view any tickets')
        if ($user->can('view pending noc tickets') && !$user->can('view any tickets')) {
            $query->where('requires_noc', true);
        }

        // Filtros de estado y búsqueda (incluye código)
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }
        if ($this->search) {
            $query->where(function ($q) {
                $q->whereHas('client', fn($c) => $c->where('name', 'like', '%' . $this->search . '%'))
                    ->orWhere('description', 'like', '%' . $this->search . '%')
                    ->orWhere('ticket_code', 'like', '%' . $this->search . '%');
            });
        }

        $tickets = $query->orderBy('created_at', 'desc')->paginate(15);
        return view('livewire.tickets.ticket-index', compact('tickets'))->layout('components.layouts.app');
    }

    // Abrir modal de detalle
    public function viewDetail($ticketId)
    {
        $this->selectedTicket = Ticket::with('client')->find($ticketId);
        $this->showDetailModal = true;
    }

    // Cerrar modal
    public function closeModal()
    {
        $this->showDetailModal = false;
        $this->selectedTicket = null;
    }

    // Resolver remotamente (desde el modal)
    public function resolveRemote($ticketId)
    {
        $user = Auth::user();
        if (!$user->can('access noc panel') && !$user->can('update tickets')) {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'No tienes permiso para resolver.']);
            return;
        }

        $ticket = Ticket::find($ticketId);
        if ($ticket && $ticket->requires_noc) {
            $ticket->status = 'resolved';
            $ticket->resolved_by = Auth::id();
            $ticket->resolved_at = now();
            $ticket->save();
            $this->dispatch('showToast', ['type' => 'success', 'message' => 'Ticket resuelto remotamente.']);
        }
        $this->closeModal();
        $this->render(); // refrescar
    }

    // Crear OT desde el modal (permiso create work_orders)
    public function createWorkOrder($ticketId)
    {
        $user = Auth::user();
        if (!$user->can('create work_orders')) {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'No tienes permiso para crear órdenes de trabajo.']);
            return;
        }

        $ticket = Ticket::find($ticketId);
        if ($ticket && $ticket->requires_noc) {
            WorkOrder::create([
                'ticket_id' => $ticket->id,
                'client_id' => $ticket->client_id,
                'description' => $ticket->description,
                'service_type' => $ticket->service_type,
                'status' => 'pending',
            ]);
            $ticket->status = 'in_progress';
            $ticket->save();
            $this->dispatch('showToast', ['type' => 'success', 'message' => 'OT creada correctamente.']);
        }
        $this->closeModal();
        $this->render(); // refrescar
    }
}