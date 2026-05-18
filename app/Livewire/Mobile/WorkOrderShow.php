<?php

namespace App\Livewire\Mobile;

use Livewire\Component;
use App\Models\WorkOrder;
use App\Models\Requisition;
use Illuminate\Support\Facades\Auth;

class WorkOrderShow extends Component
{
    public $workOrder;
    public $confirmingAction = null;
    public $confirmingMessage = '';
    public $hasOpenRequisition = false;

    public function mount($id)
    {
        $this->workOrder = WorkOrder::with('technician', 'products.product', 'client')
            ->where('technician_id', Auth::id())
            ->findOrFail($id);

        $this->checkOpenRequisition();
    }

    protected function checkOpenRequisition()
    {
        $this->hasOpenRequisition = $this->workOrder->requisitions()
            ->where('status', 'open')
            ->exists();
    }

    public function attachToOpenRequisition()
    {
        $openRequisition = Requisition::where('technician_id', Auth::id())
            ->where('status', 'open')
            ->first();

        if (!$openRequisition) {
            $this->dispatch('show-toast', type: 'error', message: 'No tienes una requisición abierta. Crea una primero.');
            return;
        }

        if (!$this->workOrder->requisitions()->where('requisition_id', $openRequisition->id)->exists()) {
            $this->workOrder->requisitions()->attach($openRequisition->id);
            $this->hasOpenRequisition = true;
            $this->dispatch('show-toast', type: 'success', message: 'OT vinculada a tu requisición activa.');
        } else {
            $this->dispatch('show-toast', type: 'info', message: 'Esta OT ya está vinculada.');
        }
    }

    public function promptStartWorkOrder()
    {
        if ($this->workOrder->status !== 'pending') {
            $this->dispatch('show-toast', type: 'error', message: 'Esta orden ya está en progreso o finalizada.');
            return;
        }
        $this->confirmingAction = 'start';
        $this->confirmingMessage = '¿Estás seguro de iniciar esta orden de trabajo? El tiempo comenzará a registrarse.';
    }

    public function promptCompleteWorkOrder()
    {
        if (!Auth::user()->can('complete work_orders')) {
            $this->dispatch('show-toast', type: 'error', message: 'No tienes permiso para completar esta orden.');
            return;
        }

        if ($this->workOrder->status === 'completed') {
            $this->dispatch('show-toast', type: 'error', message: 'Esta orden ya está completada.');
            return;
        }

        $this->confirmingAction = 'complete';
        $this->confirmingMessage = '¿Marcar esta orden como completada? Esta acción no se puede deshacer.';
    }

    public function executeConfirmedAction()
    {
        if ($this->confirmingAction === 'start') {
            $this->startWorkOrder();
        } elseif ($this->confirmingAction === 'complete') {
            $this->completeWorkOrder();
        }

        $this->confirmingAction = null;
        $this->confirmingMessage = '';
    }

    public function cancelConfirmation()
    {
        $this->confirmingAction = null;
        $this->confirmingMessage = '';
    }

    public function startWorkOrder()
    {
        if ($this->workOrder->status !== 'pending') {
            $this->dispatch('show-toast', type: 'error', message: 'Esta orden ya está en progreso o finalizada.');
            return;
        }

        $this->workOrder->status = 'in_progress';
        $this->workOrder->started_at = now();
        $this->workOrder->save();

        $this->dispatch('show-toast', type: 'success', message: 'Orden iniciada correctamente.');
    }

    public function completeWorkOrder()
    {
        if (!Auth::user()->can('complete work_orders')) {
            $this->dispatch('show-toast', type: 'error', message: 'No tienes permiso para completar esta orden.');
            return;
        }

        if ($this->workOrder->status === 'completed') {
            $this->dispatch('show-toast', type: 'error', message: 'Esta orden ya está completada.');
            return;
        }

        $this->workOrder->status = 'completed';
        $this->workOrder->completed_date = now();
        $this->workOrder->save();

        $this->dispatch('show-toast', type: 'success', message: 'Orden completada.');
    }

    public function render()
    {
        return view('livewire.mobile.work-order-show')->layout('components.layouts.app');
    }
}