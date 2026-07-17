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
    public $viewMode = 'table';

    public $confirmingAction = null;
    public $confirmingOrderId = null;

    public $selectedOrders = [];
    public $selectAll = false;
    public $showAssignModal = false;
    public $assignTechnicianId = '';
    public $assignAuxiliarId = '';
    public $scheduledDate = '';
    public $notes = '';
    public $skipAssigned = false;

    public function updatedViewMode($value)
    {
        if ($value === 'planner') {
            $this->dispatch('planner-activated');
        }
    }

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

    protected function getListeners()
    {
        return [
            'assignFromDrag' => 'assignFromDrag',
        ];
    }

    public function assignFromDrag($otId, $technicianId)
    {
        $wo = WorkOrder::with('technician')->findOrFail($otId);
        $techName = $technicianId ? User::find($technicianId)?->name : 'Sin asignar';

        $data = ['technician_id' => $technicianId ?: null];
        if ($technicianId && !$wo->assigned_at) {
            $data['assigned_at'] = now();
            $data['assigned_by'] = auth()->id();
        }

        $wo->update($data);
        $this->dispatch('show-toast', type: 'success', message: "{$wo->code} → {$techName}");
    }

    public function assignSelected()
    {
        if (empty($this->selectedOrders)) {
            $this->dispatch('show-toast', type: 'error', message: 'Seleccioná al menos una OT.');
            return;
        }

        if (!$this->assignTechnicianId && !$this->assignAuxiliarId && !$this->scheduledDate && !$this->notes) {
            $this->dispatch('show-toast', type: 'error', message: 'Completá al menos un campo para asignar.');
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

        $totalSelected = count($this->selectedOrders);
        $ids = $this->selectedOrders;
        $skipped = 0;

        if ($this->skipAssigned && ($this->assignTechnicianId || $this->assignAuxiliarId)) {
            $ids = WorkOrder::whereIn('id', $ids)->whereNull('technician_id')->pluck('id')->toArray();
            $skipped = $totalSelected - count($ids);
        }

        $count = WorkOrder::whereIn('id', $ids)->update($data);

        $this->selectedOrders = [];
        $this->selectAll = false;
        $this->showAssignModal = false;
        $this->skipAssigned = false;
        $this->scheduledDate = '';
        $this->notes = '';

        if ($skipped > 0 && $count === 0) {
            $msg = 'Todas las OT ya tenían técnico. Ninguna fue modificada.';
        } elseif ($skipped > 0) {
            $msg = "{$count} OT asignadas ({$skipped} saltadas por ya tener técnico).";
        } else {
            $msg = "{$count} OT(s) asignadas correctamente.";
        }
        $this->dispatch('show-toast', type: 'success', message: $msg);
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
            $technicians = collect();
            $unassigned = collect();
            $byTechnician = collect();
            return view('livewire.work-orders.work-order-index', compact(
                'orders', 'encargados', 'tecnicos', 'technicians', 'unassigned', 'byTechnician'
            ))->layout('components.layouts.app');
        }

        $encargados = User::role('technician')->encargados()->orderBy('name')->get(['id', 'name']);
        $tecnicos = User::role('technician')->orderBy('name')->get(['id', 'name']);

        if ($this->viewMode === 'table') {
            $orders = $query->with(['technician', 'auxiliarTechnician', 'client', 'ticket', 'zone'])
                ->orderBy('created_at', 'desc')->paginate(50);

            $alreadyAssigned = !empty($this->selectedOrders)
                ? WorkOrder::whereIn('id', $this->selectedOrders)->whereNotNull('technician_id')->count()
                : 0;

            return view('livewire.work-orders.work-order-index', compact(
                'orders', 'encargados', 'tecnicos', 'alreadyAssigned'
            ))->layout('components.layouts.app');
        }

        // Planner view
        $allOrders = $query->with(['technician', 'auxiliarTechnician', 'client', 'zone'])
            ->orderBy('scheduled_date')->get();

        $technicians = $encargados;
        $unassigned = $allOrders->whereNull('technician_id');
        $byTechnician = collect();
        foreach ($technicians as $tech) {
            $byTechnician[$tech->id] = $allOrders->where('technician_id', $tech->id);
        }

        $maxLoad = $technicians->map(fn($t) => $byTechnician[$t->id]->count())->max() ?: 1;

        return view('livewire.work-orders.work-order-index', compact(
            'encargados', 'tecnicos', 'technicians', 'unassigned', 'byTechnician', 'maxLoad'
        ))->layout('components.layouts.app');
    }
}
