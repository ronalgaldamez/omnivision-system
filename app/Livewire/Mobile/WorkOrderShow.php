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
    public $hasAnotherInProgress = false;   // ← nueva propiedad

    public function mount($id)
    {
        $this->workOrder = WorkOrder::with('technician', 'products.product', 'client')
            ->where('technician_id', Auth::id())
            ->findOrFail($id);

        $this->checkOpenRequisition();
        $this->checkAnotherInProgress();     // ← nueva validación
    }

    protected function checkOpenRequisition()
    {
        $this->hasOpenRequisition = $this->workOrder->requisitions()
            ->where('status', 'open')
            ->exists();
    }

    // NUEVA validación: verifica si el técnico ya tiene otra OT en progreso
    protected function checkAnotherInProgress()
    {
        $this->hasAnotherInProgress = WorkOrder::where('technician_id', Auth::id())
            ->where('status', 'in_progress')
            ->where('id', '!=', $this->workOrder->id)   // excluye la actual
            ->exists();
    }

    public function attachToOpenRequisition()
    {
        $openRequisition = Requisition::where('technician_id', Auth::id())
            ->where('status', 'open')
            ->first();

        if (!$openRequisition) {
            $this->dispatch('show-toast', type: 'error', message: 'No tienes una requisición abierta.');
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

    // ========== CONFIRMACIONES ==========
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

    public function promptPauseWorkOrder()
    {
        if ($this->workOrder->status !== 'in_progress') {
            $this->dispatch('show-toast', type: 'error', message: 'Solo se puede pausar una orden en progreso.');
            return;
        }
        $this->confirmingAction = 'pause';
        $this->confirmingMessage = '¿Pausar esta orden? El tiempo trabajado se guardará. Podrás reanudarla más tarde.';
    }

    public function promptResumeWorkOrder()
    {
        if ($this->workOrder->status !== 'paused') {
            $this->dispatch('show-toast', type: 'error', message: 'Solo se puede reanudar una orden pausada.');
            return;
        }
        $this->confirmingAction = 'resume';
        $this->confirmingMessage = '¿Reanudar esta orden? El tiempo continuará registrándose desde ahora.';
    }

    public function executeConfirmedAction()
    {
        switch ($this->confirmingAction) {
            case 'start': $this->startWorkOrder(); break;
            case 'complete': $this->completeWorkOrder(); break;
            case 'pause': $this->pauseWorkOrder(); break;
            case 'resume': $this->resumeWorkOrder(); break;
        }
        $this->confirmingAction = null;
        $this->confirmingMessage = '';
    }

    public function cancelConfirmation()
    {
        $this->confirmingAction = null;
        $this->confirmingMessage = '';
    }

    // ========== ACCIONES REALES ==========
    public function startWorkOrder()
    {
        if ($this->workOrder->status !== 'pending') {
            $this->dispatch('show-toast', type: 'error', message: 'Esta orden ya está en progreso o finalizada.');
            return;
        }

        // Validar que no tenga otra OT en progreso (excepto pausadas)
        if ($this->hasAnotherInProgress) {
            $this->dispatch('show-toast', type: 'error', message: 'Ya tienes otra OT en progreso. Finalízala o pausala antes de iniciar esta.');
            return;
        }

        $this->workOrder->status = 'in_progress';
        $this->workOrder->started_at = now();
        $this->workOrder->save();

        $this->checkAnotherInProgress();   // refrescar
        $this->dispatch('show-toast', type: 'success', message: 'Orden iniciada correctamente.');
    }

    public function pauseWorkOrder()
    {
        if ($this->workOrder->status !== 'in_progress') {
            return;
        }

        $now = now();
        $elapsed = $this->workOrder->started_at->diffInSeconds($now);
        $this->workOrder->accumulated_seconds += $elapsed;
        $this->workOrder->status = 'paused';
        $this->workOrder->started_at = null;   // limpiamos hasta reanudar
        $this->workOrder->save();

        $this->checkAnotherInProgress();
        $this->dispatch('show-toast', type: 'success', message: 'Orden pausada. Tiempo guardado.');
    }

    public function resumeWorkOrder()
    {
        if ($this->workOrder->status !== 'paused') {
            return;
        }

        $this->workOrder->status = 'in_progress';
        $this->workOrder->started_at = now();
        $this->workOrder->save();

        $this->checkAnotherInProgress();
        $this->dispatch('show-toast', type: 'success', message: 'Orden reanudada.');
    }

    public function completeWorkOrder()
    {
        if (!Auth::user()->can('complete work_orders')) {
            $this->dispatch('show-toast', type: 'error', message: 'No tienes permiso para completar esta orden.');
            return;
        }
        if ($this->workOrder->status === 'completed') {
            return;
        }

        $totalSeconds = $this->workOrder->accumulated_seconds;

        // Si está en progreso, sumar el tiempo actual
        if ($this->workOrder->started_at) {
            $totalSeconds += $this->workOrder->started_at->diffInSeconds(now());
        }

        $this->workOrder->status = 'completed';
        $this->workOrder->completed_date = now();
        $this->workOrder->accumulated_seconds = $totalSeconds;   // guardamos el total final
        $this->workOrder->save();

        $this->dispatch('show-toast', type: 'success', message: 'Orden completada.');
    }

    public function render()
    {
        return view('livewire.mobile.work-order-show')->layout('components.layouts.app');
    }
}