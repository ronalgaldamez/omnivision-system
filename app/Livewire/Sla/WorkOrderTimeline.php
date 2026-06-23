<?php

namespace App\Livewire\Sla;

use Livewire\Component;
use App\Models\WorkOrder;
use App\Services\TimelineService;
use Illuminate\Support\Facades\Auth;

class WorkOrderTimeline extends Component
{
    public $workOrder;
    public $timeline;

    public function mount($id)
    {
        $this->workOrder = WorkOrder::with([
            'technician',
            'client',
            'ticket',
            'pauses',
        ])->findOrFail($id);

        if (!$this->workOrder->ticket) {
            // Solo para OTs puras
            if (Auth::user()->cannot('view work_orders')) {
                abort(403);
            }
            $this->timeline = app(TimelineService::class)->buildFromWorkOrder($this->workOrder);
        } else {
            // Redirigir al timeline del ticket si tiene ticket asociado
            return redirect()->route('sla.ticket-timeline', $this->workOrder->ticket_id);
        }
    }

    public function render()
    {
        return view('livewire.sla.work-order-timeline', [
            'timeline' => $this->timeline,
        ])->layout('components.layouts.app');
    }
}
