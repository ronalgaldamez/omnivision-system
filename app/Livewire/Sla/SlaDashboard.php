<?php

namespace App\Livewire\Sla;

use Livewire\Component;
use App\Models\Ticket;
use App\Services\SlaService;
use Illuminate\Support\Facades\Auth;

class SlaDashboard extends Component
{
    public $filterPriority = '';
    public $filterStatus = '';

    public function render()
    {
        if (Auth::user()->cannot('view sla dashboard')) {
            abort(403);
        }

        $slaService = app(SlaService::class);
        $stats = $slaService->getStats();
        $atRiskTickets = $slaService->getAtRiskTickets(30);
        $overdueTickets = $slaService->getOverdueTickets();

        $tickets = Ticket::with('client', 'createdBy', 'slaGoal')
            ->whereNotNull('sla_goal_id')
            ->when($this->filterPriority, fn($q) => $q->where('priority', $this->filterPriority))
            ->when($this->filterStatus, function ($q) {
                if ($this->filterStatus === 'met') $q->where('sla_met', true);
                elseif ($this->filterStatus === 'not_met') $q->where('sla_met', false);
                elseif ($this->filterStatus === 'pending') $q->whereNull('sla_evaluated_at');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('livewire.sla.sla-dashboard', compact('stats', 'atRiskTickets', 'overdueTickets', 'tickets'))
            ->layout('components.layouts.app');
    }
}
