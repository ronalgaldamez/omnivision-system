<?php

namespace App\Livewire\WorkOrders;

use Livewire\Component;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\Auth;

class WorkOrderShow extends Component
{
    public $order;

    public function mount($id)
    {
        $this->order = WorkOrder::with('technician', 'products.product', 'client')->findOrFail($id);
    }

    public function completeWorkOrder()
    {
        if (!Auth::user()->can('complete work_orders')) {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'No tienes permiso.']);
            return;
        }

        if ($this->order->status === 'completed') {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'Ya está completada.']);
            return;
        }

        $this->order->status = 'completed';
        $this->order->completed_date = now();
        $this->order->save();

        $this->dispatch('showToast', ['type' => 'success', 'message' => 'Orden completada.']);
        return redirect()->route('work-orders.index');
    }

    public function cancelWorkOrder()
    {
        if (!Auth::user()->can('cancel work orders')) {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'No tienes permiso para cancelar.']);
            return;
        }

        if (in_array($this->order->status, ['completed', 'cancelled'])) {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'No se puede cancelar una orden ya completada o cancelada.']);
            return;
        }

        $this->order->status = 'cancelled';
        $this->order->save();

        $this->dispatch('showToast', ['type' => 'success', 'message' => 'Orden cancelada.']);
        return redirect()->route('work-orders.index');
    }

    public function render()
    {
        return view('livewire.work-orders.work-order-show', ['order' => $this->order])->layout('components.layouts.app');
    }
}