<?php

namespace App\Livewire\Contracts;

use Livewire\Component;
use App\Models\Ticket;
use App\Models\Branch;
use Illuminate\Support\Facades\Auth;

class ContractInbox extends Component
{
    public $activeTab = 'pending';
    public $tickets;

    public $showDetailModal = false;
    public $selectedTicket = null;

    public $confirmingReject = null;
    public $confirmingAccept = null;

    // ─── Filtro por sucursal ───
    public $branchFilter = '';
    public $availableBranches = [];

    // Sincronizar tab activo con query string ?tab=pending
    protected $queryString = ['activeTab' => ['as' => 'tab']];

    public function mount()
    {
        if (Auth::user()->cannot('access_contracts_inbox')) {
            abort(403);
        }

        $this->loadBranches();
        $this->loadTickets();

        if ($ticketId = request()->get('ticket_id')) {
            $this->viewDetail($ticketId);
        }
    }

    /**
     * Carga las sucursales disponibles según permisos del usuario.
     */
    private function loadBranches(): void
    {
        $user = Auth::user();

        if ($user->can('access_all_branches')) {
            // Admin: ve todas las sucursales
            $this->availableBranches = Branch::orderBy('name')->get();
        } else {
            // Perfil limitado: solo su sucursal asignada
            $userBranchId = $user->branch_id;
            if ($userBranchId) {
                $this->availableBranches = Branch::where('id', $userBranchId)->orderBy('name')->get();
                $this->branchFilter = (string) $userBranchId;
            } else {
                $this->availableBranches = collect();
            }
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
        $query = Ticket::where('requires_contract', true)
            ->with('client.branch', 'client.zone.branch', 'createdBy');

        // ─── Filtro por sucursal ───
        if (!empty($this->branchFilter)) {
            $query->where(function ($q) {
                $q->whereHas('client', function ($cq) {
                    $cq->where('branch_id', $this->branchFilter)
                        ->orWhereHas('zone', function ($zq) {
                            $zq->where('branch_id', $this->branchFilter);
                        });
                });
            });
        }

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

    public function updatedBranchFilter()
    {
        $this->loadTickets();
    }

    public function viewDetail($ticketId)
    {
        $this->selectedTicket = Ticket::with('client', 'createdBy')->find($ticketId);
        $this->showDetailModal = true;
    }

    // ─── Aceptar con confirmación ───
    public function promptAccept($ticketId)
    {
        $this->confirmingAccept = $ticketId;
    }

    public function executeAccept()
    {
        $ticket = Ticket::find($this->confirmingAccept);
        if ($ticket && is_null($ticket->contracts_started_at)) {
            $ticket->update([
                'contracts_started_at' => now(),
            ]);
        }
        $this->confirmingAccept = null;
        $this->loadTickets();
        $this->closeModal();
    }

    public function cancelAccept()
    {
        $this->confirmingAccept = null;
    }

    // ─── Rechazar ───
    public function acceptTicket($ticketId)
    {
        // Este método se mantiene por compatibilidad, pero ahora se usa promptAccept
        $this->promptAccept($ticketId);
    }

    public function promptReject($ticketId)
    {
        $this->confirmingReject = $ticketId;
    }

    public function cancelConfirmation()
    {
        $this->confirmingReject = null;
    }

    public function rejectTicket()
    {
        $ticket = Ticket::find($this->confirmingReject);
        if ($ticket) {
            $ticket->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancellation_reason' => 'Rechazado por Contratos',
            ]);
        }
        $this->confirmingReject = null;
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
