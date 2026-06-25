<?php

namespace App\Livewire\Sla;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Ticket;
use App\Services\TimelineService;
use Illuminate\Support\Facades\Auth;

class TicketTimeline extends Component
{
    public $ticket;
    public $timeline;

    public function mount($id)
    {
        $this->ticket = Ticket::with([
            'client', 'createdBy', 'resolvedBy', 'slaGoal',
            'zone.supervisors', 'zone.parent.supervisors', 'zone.parent.parent.supervisors',
            'workOrder.technician', 'workOrder.createdBy', 'workOrder.pauses',
        ])->findOrFail($id);

        if (Auth::user()->cannot('view any tickets')) {
            abort(403);
        }

        $this->refreshTimeline();
    }

    #[On('refresh-timeline')]
    public function refreshTimeline()
    {
        $this->ticket = Ticket::with([
            'client', 'createdBy', 'resolvedBy', 'slaGoal',
            'zone.supervisors', 'zone.parent.supervisors', 'zone.parent.parent.supervisors',
            'workOrder.technician', 'workOrder.createdBy', 'workOrder.pauses',
        ])->find($this->ticket->id);

        $this->timeline = app(TimelineService::class)->buildFromTicket($this->ticket);
    }

    public function render()
    {
        return view('livewire.sla.ticket-timeline', [
            'timeline' => $this->timeline,
        ])->layout('components.layouts.app');
    }
}
