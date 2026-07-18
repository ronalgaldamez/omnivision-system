<?php

namespace App\Livewire\Tickets;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Ticket;
use App\Models\WorkOrder;
use App\Services\WorkOrderService;
use Illuminate\Support\Facades\Auth;

class TicketIndex extends Component
{
    use WithPagination;

    public $activeTab = 'all';
    public $statusFilter = '';
    public $search = '';

    public $showDetailModal = false;
    public $selectedTicket = null;

    // Propiedades para la confirmación
    public $confirmingAction = null;   // 'create_ot'
    public $confirmingTicketId = null;

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();
        $query = Ticket::with('client', 'createdBy', 'workOrder');

        if ($user->can('view any tickets')) {
            // todos
        } elseif ($user->can('view own tickets')) {
            $query->where('created_by', $user->id);
        } else {
            $tickets = Ticket::query()->whereKey(0)->paginate(15);
            return view('livewire.tickets.ticket-index', compact('tickets'))->layout('components.layouts.app');
        }

        if ($this->activeTab === 'ot') {
            $query->where('create_ot', true);
        } elseif ($this->activeTab === 'noc') {
            $query->where('requires_noc', true);
        }

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

    // Redirige al panel NOC
    public function goToNocPanel()
    {
        return redirect()->route('noc.panel');
    }

    // Prepara la confirmación para crear OT
    public function promptCreateWorkOrder($ticketId)
    {
        // Verificar permiso antes de mostrar el modal
        if (!Auth::user()->can('create work_orders')) {
            $this->dispatch('show-toast', type: 'error', message: 'No tienes permiso para crear órdenes de trabajo.');
            return;
        }

        $this->confirmingAction = 'create_ot';
        $this->confirmingTicketId = $ticketId;
    }

    // Ejecuta la acción confirmada
    public function executeConfirmedAction()
    {
        if ($this->confirmingAction === 'create_ot') {
            $this->createWorkOrder($this->confirmingTicketId);
        }

        $this->confirmingAction = null;
        $this->confirmingTicketId = null;
    }

    // Cancela la confirmación
    public function cancelConfirmation()
    {
        $this->confirmingAction = null;
        $this->confirmingTicketId = null;
    }

    // Método createWorkOrder original, ahora solo se llama después de confirmar
    public function createWorkOrder($ticketId)
    {
        $user = Auth::user();
        if (!$user->can('create work_orders')) {
            $this->dispatch('show-toast', type: 'error', message: 'No tienes permiso para crear órdenes de trabajo.');
            return;
        }

        $ticket = Ticket::with('client')->find($ticketId);
        if ($ticket && $ticket->requires_noc) {
            app(WorkOrderService::class)->createFromTicket($ticket, [
                'started_at' => now(),
            ]);
            $ticket->status = 'in_progress';
            $ticket->l2_ended_at = now();
            $ticket->l2_started_at = $ticket->l2_started_at ?? now();
            $ticket->save();
            $this->dispatch('show-toast', type: 'success', message: 'OT creada correctamente.');
        }
        $this->closeModal();
    }
}