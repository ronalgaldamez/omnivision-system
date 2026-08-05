<?php

namespace App\Livewire\Noc;

use Livewire\Component;
use App\Models\Ticket;
use App\Models\WorkOrder;
use App\Services\SlaService;
use App\Services\WorkOrderService;
use Illuminate\Support\Facades\Auth;

class NocInbox extends Component
{
    public $activeTab = 'pending';
    public $tickets;
    public $search = '';
    public $priorityFilter = '';
    public $viewMode = 'cards'; // 'cards' | 'table'

    protected $queryString = [
        'activeTab' => ['except' => 'pending'],
        'search' => ['except' => ''],
        'priorityFilter' => ['except' => ''],
        'viewMode' => ['except' => 'cards'],
    ];

    public $showDetailModal = false;
    public $selectedTicket = null;

    public $confirmingAction = null;
    public $confirmingTicketId = null;
    public $createReason = '';

    public function mount()
    {
        if (Auth::user()->cannot('access noc panel')) {
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

    public function setViewMode($mode)
    {
        $this->viewMode = $mode;
    }

    public function loadTickets()
    {
        $query = Ticket::where('requires_noc', true)->with('client', 'createdBy');

        switch ($this->activeTab) {
            case 'pending':
                $query->whereNull('l2_started_at')->whereNotIn('status', ['resolved', 'cancelled']);
                break;
            case 'in_progress':
                $query->whereNotNull('l2_started_at')->whereNull('l2_ended_at');
                break;
            case 'completed':
                $query->whereNotNull('l2_ended_at');
                break;
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->whereHas('client', fn($c) => $c->where('name', 'like', '%' . $this->search . '%'))
                    ->orWhere('ticket_code', 'like', '%' . $this->search . '%');
            });
        }
        if ($this->priorityFilter) {
            $query->where('priority', $this->priorityFilter);
        }

        $this->tickets = $query->orderByRaw("CASE priority WHEN 'P1' THEN 1 WHEN 'P2' THEN 2 WHEN 'P3' THEN 3 WHEN 'P4' THEN 4 ELSE 5 END ASC")
            ->orderBy('created_at', 'desc')
            ->get();
    }

    protected function getKpis(): array
    {
        $base = Ticket::where('requires_noc', true);

        return [
            'pending' => (clone $base)->whereNull('l2_started_at')->whereNotIn('status', ['resolved', 'cancelled'])->count(),
            'in_progress' => (clone $base)->whereNotNull('l2_started_at')->whereNull('l2_ended_at')->count(),
            'completed' => (clone $base)->whereNotNull('l2_ended_at')->count(),
            'with_ot' => (clone $base)->whereHas('workOrder')->count(),
        ];
    }

    public function viewDetail($ticketId)
    {
        $this->selectedTicket = Ticket::with('client', 'createdBy')->find($ticketId);
        $this->showDetailModal = true;
    }

    public function acceptTicket($ticketId)
    {
        $ticket = Ticket::find($ticketId);
        if ($ticket && is_null($ticket->l2_started_at)) {
            $ticket->update([
                'l2_started_at' => now(),
                'resolved_by' => Auth::id(),
            ]);
        }
        $this->loadTickets();
        $this->closeModal();
    }

    public function promptResolveRemote($ticketId)
    {
        $this->confirmingAction = 'resolve';
        $this->confirmingTicketId = $ticketId;
    }

    public function promptCreateWorkOrder($ticketId)
    {
        $this->confirmingAction = 'create_ot';
        $this->confirmingTicketId = $ticketId;
        $this->createReason = '';
    }

    public function promptAccept($ticketId)
    {
        $this->confirmingAction = 'accept';
        $this->confirmingTicketId = $ticketId;
    }

    public function executeConfirmedAction()
    {
        if ($this->confirmingAction === 'resolve') {
            $this->resolveRemote($this->confirmingTicketId);
        } elseif ($this->confirmingAction === 'create_ot') {
            $this->createWorkOrder($this->confirmingTicketId);
        } elseif ($this->confirmingAction === 'accept') {
            $this->acceptTicket($this->confirmingTicketId);
        }
        $this->confirmingAction = null;
        $this->confirmingTicketId = null;
    }

    public function cancelConfirmation()
    {
        $this->confirmingAction = null;
        $this->confirmingTicketId = null;
    }

    public function resolveRemote($ticketId)
    {
        $ticket = Ticket::find($ticketId);
        if ($ticket && $ticket->requires_noc) {
            $ticket->status = 'resolved';
            $ticket->resolved_by = Auth::id();
            $ticket->resolved_at = now();
            $ticket->l2_started_at = $ticket->l2_started_at ?? now();
            $ticket->l2_ended_at = now();
            $ticket->save();
            app(SlaService::class)->evaluateSla($ticket);
        }
        $this->loadTickets();
        $this->closeModal();
    }

    public function createWorkOrder($ticketId)
    {
        if (empty(trim($this->createReason))) {
            $this->dispatch('show-toast', type: 'error', message: 'Debés escribir el motivo de la creación de la OT.');
            return;
        }

        $ticket = Ticket::with('client')->find($ticketId);
        if ($ticket) {
            app(WorkOrderService::class)->createFromTicket($ticket, [
                'notes' => 'Motivo NOC: ' . trim($this->createReason),
            ]);

            $ticket->status = 'in_progress';
            $ticket->l2_ended_at = now();
            $ticket->l2_started_at = $ticket->l2_started_at ?? now();
            $ticket->resolved_by = $ticket->resolved_by ?? Auth::id();
            $ticket->save();
        }
        $this->createReason = '';
        $this->loadTickets();
        $this->closeModal();
    }

    public function closeModal()
    {
        $this->showDetailModal = false;
        $this->selectedTicket = null;
        $this->js("history.replaceState(null, '', window.location.pathname + window.location.hash)");
    }

    public function render()
    {
        $kpis = $this->getKpis();
        return view('livewire.noc.noc-inbox', compact('kpis'))->layout('components.layouts.app');
    }
}
