<?php

namespace App\Livewire\WorkOrders;

use Livewire\Component;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\Auth;

class WorkOrderShow extends Component
{
    public $workOrder;

    public function mount($id)
    {
        $this->workOrder = WorkOrder::with('technician', 'products.product', 'client')->findOrFail($id);
    }

    public function completeWorkOrder()
    {
        if (!Auth::user()->can('complete work_orders')) {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'No tienes permiso.']);
            return;
        }

        if ($this->workOrder->status === 'completed') {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'Ya está completada.']);
            return;
        }

        $this->workOrder->status = 'completed';
        $this->workOrder->completed_date = now();
        $this->workOrder->save();

        $this->dispatch('showToast', ['type' => 'success', 'message' => 'Orden completada.']);
        return redirect()->route('work-orders.index');
    }

    public function render()
    {
        return view('livewire.work-orders.work-order-show')->layout('components.layouts.app');
    }
}