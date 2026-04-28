<?php

namespace App\Livewire\Tickets;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;

class TicketIndex extends Component
{
    use WithPagination;

    public $statusFilter = '';
    public $search = '';

    public function render()
    {
        $user = Auth::user();
        $query = Ticket::with('client', 'createdBy', 'workOrder');

        // ========== APLICAR FILTRO SEGÚN PERMISOS ==========
        if ($user->can('view any tickets')) {
            // Ver todos los tickets (sin filtrar por creador)
            // No añadimos condición extra
        } elseif ($user->can('view own tickets')) {
            // Ver solo los que el usuario creó
            $query->where('created_by', $user->id);
        } else {
            // Sin permiso para ver tickets, devolvemos vacío
            $tickets = collect();
            return view('livewire.tickets.ticket-index', compact('tickets'))->layout('components.layouts.app');
        }

        // Si el usuario es NOC y tiene permiso 'view pending noc tickets', mostramos solo los que requieren NOC?
        // La lógica original incluía: if ($user->hasRole('noc')) $query->where('requires_noc', true);
        // Para mantener consistencia, si el usuario tiene 'view pending noc tickets' y NO tiene 'view any tickets', aplicamos ese filtro.
        // Pero como NOC ahora tiene 'view any tickets', eso mostraría todos. Para mantener el comportamiento original,
        // podemos añadir una condición: si el usuario tiene 'view pending noc tickets' pero no 'view any tickets', filtrar.
        // O simplemente confiar en que el panel NOC se usará para ver los pendientes de NOC.
        // Para no complicar, mantendré la condición original basada en rol 'noc' (ya que es un caso especial).
        // En una fase posterior se puede refinar.
        if ($user->hasRole('noc') && !$user->can('view any tickets')) {
            $query->where('requires_noc', true);
        }

        // Filtros adicionales (estado y búsqueda)
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }
        if ($this->search) {
            $query->where(function ($q) {
                $q->whereHas('client', fn($c) => $c->where('name', 'like', '%' . $this->search . '%'))
                    ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        $tickets = $query->orderBy('created_at', 'desc')->paginate(15);
        return view('livewire.tickets.ticket-index', compact('tickets'))->layout('components.layouts.app');
    }

    // Método para crear OT desde la tabla (si el NOC tiene permiso)
    public function createOt($ticketId)
    {
        $user = Auth::user();
        // Solo NOC con permiso 'access noc panel' o 'update tickets' puede crear OT desde aquí
        if (!$user->can('access noc panel') && !$user->can('update tickets')) {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'No tienes permiso para crear OT.']);
            return;
        }

        $ticket = Ticket::findOrFail($ticketId);
        if (!$ticket->requires_noc) {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'Este ticket no requiere NOC.']);
            return;
        }

        // Crear OT
        \App\Models\WorkOrder::create([
            'ticket_id' => $ticket->id,
            'client_id' => $ticket->client_id,
            'description' => $ticket->description,
            'service_type' => $ticket->service_type,
            'status' => 'pending',
        ]);

        $this->dispatch('showToast', ['type' => 'success', 'message' => 'OT creada correctamente.']);
        $this->render(); // refrescar
    }
}