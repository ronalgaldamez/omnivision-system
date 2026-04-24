<?php

namespace App\Livewire\Mobile;

use Livewire\Component;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\Auth;

class WorkOrderShow extends Component
{
    public $workOrder;

    public function mount($id)
    {
        $this->workOrder = WorkOrder::with('technician', 'products.product', 'client')
            ->where('technician_id', Auth::id())
            ->findOrFail($id);
    }

    public function completeWorkOrder()
    {
        if (!Auth::user()->can('complete work_orders')) {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'No tienes permiso para completar esta orden.']);
            return;
        }

        if ($this->workOrder->status === 'completed') {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'Esta orden ya está completada.']);
            return;
        }

        $this->workOrder->status = 'completed';
        $this->workOrder->completed_date = now();
        $this->workOrder->save();

        $this->dispatch('showToast', ['type' => 'success', 'message' => 'Orden marcada como completada.']);
        return redirect()->route('mobile.work-orders.list');
    }

    public function render()
    {
        return view('livewire.mobile.work-order-show')->layout('components.layouts.app');
    }
}