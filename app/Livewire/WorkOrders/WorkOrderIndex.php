<?php

namespace App\Livewire\WorkOrders;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\WorkOrder;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class WorkOrderIndex extends Component
{
    use WithPagination;

    public $statusFilter = '';
    public $search = '';

    public $confirmingAction = null;
    public $confirmingOrderId = null;

    public $selectedOrders = [];
    public $selectAll = false;
    public $showAssignModal = false;
    public $assignMode = 'quick';
    public $currentStepIndex = 0;
    public $assignTechnicianId = '';
    public $assignAuxiliarId = '';
    public $scheduledDate = '';
    public $notes = '';

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedOrders = $this->getFilteredQuery()->pluck('id')->toArray();
        } else {
            $this->selectedOrders = [];
        }
    }

    public function updatedSelectedOrders()
    {
        $this->selectAll = false;
    }

    protected function getFilteredQuery()
    {
        $user = Auth::user();
        $query = WorkOrder::query();

        if ($user->can('view all work orders')) {
            // todas
        } elseif ($user->can('view own work_orders')) {
            $query->whereHas('ticket', fn($q) => $q->where('created_by', $user->id)->orWhere('resolved_by', $user->id));
        } else {
            return null;
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }
        if ($this->search) {
            $query->where(function ($q) {
                $q->whereHas('client', fn($c) => $c->where('name', 'like', '%' . $this->search . '%'))
                    ->orWhereHas('technician', fn($t) => $t->where('name', 'like', '%' . $this->search . '%'))
                    ->orWhereHas('ticket', fn($t) => $t->where('ticket_code', 'like', '%' . $this->search . '%'));
            });
        }

        return $query;
    }

    public function setQuickMode()
    {
        $this->assignMode = 'quick';
        $this->currentStepIndex = 0;
    }

    public function setStepMode()
    {
        $this->assignMode = 'step';
        $this->currentStepIndex = 0;
    }

    public function goToStep($index)
    {
        if ($index >= 0 && $index < count($this->selectedOrders)) {
            $this->currentStepIndex = $index;
        }
    }

    public function applyStep()
    {
        $otId = $this->selectedOrders[$this->currentStepIndex] ?? null;
        if (!$otId) return;

        $data = [];
        if ($this->assignTechnicianId) {
            $data['technician_id'] = $this->assignTechnicianId;
            $data['assigned_at'] = now();
            $data['assigned_by'] = auth()->id();
        }
        if ($this->assignAuxiliarId) {
            $data['auxiliar_technician_id'] = $this->assignAuxiliarId;
        }
        if ($this->scheduledDate) {
            $data['scheduled_date'] = $this->scheduledDate;
        }
        if ($this->notes) {
            $data['notes'] = $this->notes;
        }

        WorkOrder::where('id', $otId)->update($data);

        $total = count($this->selectedOrders);
        if ($this->currentStepIndex < $total - 1) {
            $this->currentStepIndex++;
        }

        $this->dispatch('show-toast', type: 'success', message: 'OT actualizada.');
    }

    public function assignSelected()
    {
        if (empty($this->selectedOrders)) {
            $this->dispatch('show-toast', type: 'error', message: 'Seleccioná al menos una OT.');
            return;
        }

        if (!$this->assignTechnicianId && !$this->assignAuxiliarId) {
            $this->dispatch('show-toast', type: 'error', message: 'Seleccioná un técnico o auxiliar para asignar.');
            return;
        }

        $data = [];
        if ($this->assignTechnicianId) {
            $data['technician_id'] = $this->assignTechnicianId;
            $data['assigned_at'] = now();
            $data['assigned_by'] = auth()->id();
        }
        if ($this->assignAuxiliarId) {
            $data['auxiliar_technician_id'] = $this->assignAuxiliarId;
        }
        if ($this->scheduledDate) {
            $data['scheduled_date'] = $this->scheduledDate;
        }
        if ($this->notes) {
            $data['notes'] = $this->notes;
        }

        $count = WorkOrder::whereIn('id', $this->selectedOrders)->update($data);

        $this->selectedOrders = [];
        $this->selectAll = false;
        $this->showAssignModal = false;
        $this->assignMode = 'quick';
        $this->currentStepIndex = 0;
        $this->scheduledDate = '';
        $this->notes = '';
        $this->dispatch('show-toast', type: 'success', message: "{$count} OT(s) asignadas correctamente.");
    }

    public function promptDelete($id)
    {
        $user = Auth::user();
        $order = WorkOrder::findOrFail($id);

        if ($user->cannot('delete work_orders')) {
            $this->dispatch('show-toast', type: 'error', message: 'No tienes permiso para eliminar órdenes.');
            return;
        }
        if ($order->status !== 'pending') {
            $this->dispatch('show-toast', type: 'error', message: 'Solo se pueden eliminar órdenes pendientes.');
            return;
        }

        $this->confirmingAction = 'delete';
        $this->confirmingOrderId = $id;
    }

    public function executeConfirmedAction()
    {
        if ($this->confirmingAction === 'delete') {
            $this->delete($this->confirmingOrderId);
        }

        $this->confirmingAction = null;
        $this->confirmingOrderId = null;
    }

    public function cancelConfirmation()
    {
        $this->confirmingAction = null;
        $this->confirmingOrderId = null;
    }

    public function delete($id)
    {
        $user = Auth::user();
        $order = WorkOrder::findOrFail($id);

        if ($user->cannot('delete work_orders')) {
            $this->dispatch('show-toast', type: 'error', message: 'No tienes permiso para eliminar órdenes.');
            return;
        }
        if ($order->status !== 'pending') {
            $this->dispatch('show-toast', type: 'error', message: 'Solo se pueden eliminar órdenes pendientes.');
            return;
        }

        $order->delete();
        $this->dispatch('show-toast', type: 'success', message: 'Orden eliminada.');
    }

    public function nearby($lat, $lng, $radius = 10)
    {
        $haversine = "(6371 * acos(cos(radians($lat)) * cos(radians(latitude)) * cos(radians(longitude) - radians($lng)) + sin(radians($lat)) * sin(radians(latitude))))";
        $orders = WorkOrder::select('*')
            ->selectRaw("{$haversine} AS distance")
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->having('distance', '<', $radius)
            ->orderBy('distance')
            ->get();
        return $orders;
    }

    public function render()
    {
        $user = Auth::user();
        if ($user) {
            $user->load('roles.permissions');
            Auth::setUser($user);
        }

        $query = $this->getFilteredQuery();

        if ($query === null) {
            $orders = collect();
            $encargados = collect();
            $tecnicos = collect();
            return view('livewire.work-orders.work-order-index', compact('orders', 'encargados', 'tecnicos'))->layout('components.layouts.app');
        }

        $orders = $query->with(['technician', 'auxiliarTechnician', 'client', 'ticket', 'zone'])
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        $encargados = User::role('technician')->encargados()->orderBy('name')->get(['id', 'name']);
        $tecnicos = User::role('technician')->orderBy('name')->get(['id', 'name']);

        return view('livewire.work-orders.work-order-index', compact('orders', 'encargados', 'tecnicos'))->layout('components.layouts.app');
    }
}
