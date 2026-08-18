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
    public $serviceTypeFilter = '';
    public $dayFilter = '';
    public $technicianFilter = '';
    public $priorityFilter = '';
    public $viewMode = 'cards'; // 'cards' | 'table'

    protected $queryString = [
        'statusFilter' => ['except' => ''],
        'search' => ['except' => ''],
        'serviceTypeFilter' => ['except' => ''],
        'dayFilter' => ['except' => ''],
        'technicianFilter' => ['except' => ''],
        'priorityFilter' => ['except' => ''],
        'viewMode' => ['except' => 'cards'],
    ];

    public function setViewMode($mode)
    {
        $this->viewMode = $mode;
    }

    public $confirmingAction = null;
    public $confirmingOrderId = null;

    public $selectedOrders = [];
    public $selectAll = false;
    public $showAssignModal = false;
    public $assignTechnicianId = '';
    public $assignAuxiliarId = '';
    public $assignVehicleId = '';
    public $scheduledDate = '';
    public $notes = '';
    public $skipAssigned = false;

    public function updatedAssignTechnicianId($value)
    {
        // Pre-cargar el vehículo de la asignación activa del encargado (editable)
        if ($value) {
            $asignacion = \App\Models\Asignacion::where('encargado_id', $value)
                ->where('is_active', true)
                ->first();
            $this->assignVehicleId = $asignacion?->vehicle_id ? (string) $asignacion->vehicle_id : '';
        } else {
            $this->assignVehicleId = '';
        }
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedOrders = $this->getFilteredQuery()
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->pluck('id')
                ->toArray();
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
        $query = WorkOrder::query();

        if (!$this->applyPermissionScope($query)) {
            return null;
        }

        if ($this->statusFilter === 'unassigned') {
            $query->whereNull('technician_id');
        } elseif ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        } else {
            // Por defecto: solo OTs activas (no mostrar completadas/canceladas)
            $query->whereIn('status', ['pending', 'in_progress', 'paused']);
        }
        if ($this->serviceTypeFilter) {
            $query->where('service_type', $this->serviceTypeFilter);
        }
        if ($this->dayFilter === 'today') {
            $query->whereDate('scheduled_date', now()->toDateString());
        } elseif ($this->dayFilter === 'tomorrow') {
            $query->whereDate('scheduled_date', now()->addDay()->toDateString());
        } elseif ($this->dayFilter === 'week') {
            $query->whereBetween('scheduled_date', [now()->startOfWeek(), now()->endOfWeek()]);
        }
        if ($this->technicianFilter) {
            $query->where('technician_id', $this->technicianFilter);
        }
        if ($this->priorityFilter) {
            $query->whereHas('ticket', fn($t) => $t->where('priority', $this->priorityFilter));
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

    protected function applyPermissionScope($query): bool
    {
        $user = Auth::user();

        if ($user->can('view all work orders')) {
            return true;
        }

        if ($user->can('view own work_orders')) {
            $query->whereHas('ticket', fn($q) => $q->where('created_by', $user->id)->orWhere('resolved_by', $user->id));
            return true;
        }

        return false;
    }

    protected function getKpis(): array
    {
        $base = WorkOrder::query();
        if (!$this->applyPermissionScope($base)) {
            return ['pending' => 0, 'in_progress' => 0, 'unassigned' => 0, 'total' => 0, 'completed' => 0];
        }

        return [
            'pending' => (clone $base)->where('status', 'pending')->count(),
            'in_progress' => (clone $base)->where('status', 'in_progress')->count(),
            'unassigned' => (clone $base)->whereNull('technician_id')->whereNotIn('status', ['completed', 'cancelled'])->count(),
            'total' => (clone $base)->whereIn('status', ['pending', 'in_progress', 'paused'])->count(),
            'completed' => (clone $base)->where('status', 'completed')->count(),
        ];
    }

    protected function getServiceTypes(): array
    {
        $base = WorkOrder::query();
        if (!$this->applyPermissionScope($base)) {
            return [];
        }

        return $base->select('service_type')
            ->whereNotNull('service_type')
            ->where('service_type', '!=', '')
            ->distinct()
            ->orderBy('service_type')
            ->pluck('service_type')
            ->toArray();
    }

    protected function getListeners()
    {
        return [
            'assignFromDrag' => 'assignFromDrag',
        ];
    }

    public function acceptOrder($otId)
    {
        $wo = WorkOrder::findOrFail($otId);

        if ($wo->status !== 'pending') {
            $this->dispatch('show-toast', type: 'error', message: 'Solo se pueden aceptar OT pendientes.');
            return;
        }

        if ($wo->accepted_at) {
            $this->dispatch('show-toast', type: 'info', message: 'Esta OT ya fue aceptada.');
            return;
        }

        $wo->update(['accepted_at' => now()]);
        $this->dispatch('show-toast', type: 'success', message: "OT {$wo->code} aceptada. Ahora podés asignarla.");
    }

    public function promptAcceptOrder($otId)
    {
        $wo = WorkOrder::findOrFail($otId);

        if ($wo->status !== 'pending') {
            $this->dispatch('show-toast', type: 'error', message: 'Solo se pueden aceptar OT pendientes.');
            return;
        }
        if ($wo->accepted_at) {
            $this->dispatch('show-toast', type: 'info', message: 'Esta OT ya fue aceptada.');
            return;
        }

        $this->confirmingAction = 'accept';
        $this->confirmingOrderId = $otId;
    }

    public function assignOrder($otId)
    {
        $wo = WorkOrder::findOrFail($otId);

        if (in_array($wo->status, ['completed', 'cancelled'])) {
            $this->dispatch('show-toast', type: 'error', message: 'No se puede asignar una OT finalizada.');
            return;
        }

        if (!$wo->accepted_at) {
            $this->dispatch('show-toast', type: 'error', message: 'Primero debés aceptar la OT antes de asignarla.');
            return;
        }

        // Pre-cargar los datos ya asignados para poder cambiarlos sin perderlos de vista
        $this->selectedOrders = [$wo->id];
        $this->assignTechnicianId = (string) ($wo->technician_id ?? '');
        $this->assignAuxiliarId = (string) ($wo->auxiliar_technician_id ?? '');
        $this->assignVehicleId = (string) ($wo->vehicle_id ?? '');
        $this->scheduledDate = $wo->scheduled_date?->format('Y-m-d') ?? '';
        $this->notes = $wo->notes ?? '';
        $this->showAssignModal = true;
    }

    public function closeAssignModal()
    {
        $this->showAssignModal = false;
        $this->selectedOrders = [];
        $this->assignTechnicianId = '';
        $this->assignAuxiliarId = '';
        $this->assignVehicleId = '';
        $this->scheduledDate = '';
        $this->notes = '';
    }

    public function assignFromDrag($otId, $technicianId)
    {
        $wo = WorkOrder::with('technician')->findOrFail($otId);

        if ($technicianId && $wo->auxiliar_technician_id && (int) $technicianId === (int) $wo->auxiliar_technician_id) {
            $this->dispatch('show-toast', type: 'error', message: 'El técnico no puede ser el mismo que el auxiliar.');
            return;
        }

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

        // Restricción: las OTs deben estar aceptadas para poder asignarlas
        $unaccepted = WorkOrder::whereIn('id', $this->selectedOrders)->whereNull('accepted_at')->count();
        if ($unaccepted > 0) {
            $this->dispatch('show-toast', type: 'error', message: "{$unaccepted} OT seleccionada(s) aún no han sido aceptadas. Aceptá primero.");
            return;
        }

        if (!$this->assignTechnicianId && !$this->assignAuxiliarId && !$this->assignVehicleId && !$this->scheduledDate && !$this->notes) {
            $this->dispatch('show-toast', type: 'error', message: 'Completá al menos un campo para asignar.');
            return;
        }

        if ($this->assignTechnicianId && $this->assignAuxiliarId && $this->assignAuxiliarId === $this->assignTechnicianId) {
            $this->dispatch('show-toast', type: 'error', message: 'El auxiliar no puede ser el mismo técnico.');
            return;
        }

        $data = [];
        if ($this->assignTechnicianId) {
            $data['technician_id'] = $this->assignTechnicianId;
            $data['assigned_at'] = now();
            $data['assigned_by'] = auth()->id();
            // Al reasignar a un técnico nuevo, se desvincula el auxiliar y vehículo
            // del técnico anterior (a menos que se elija uno nuevo).
            if (!$this->assignAuxiliarId) {
                $data['auxiliar_technician_id'] = null;
            }
            if (!$this->assignVehicleId) {
                $data['vehicle_id'] = null;
            }
        }
        if ($this->assignAuxiliarId) {
            $data['auxiliar_technician_id'] = $this->assignAuxiliarId;
        }
        if ($this->assignVehicleId) {
            $data['vehicle_id'] = $this->assignVehicleId;
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
        $this->assignVehicleId = '';
        $this->assignTechnicianId = '';
        $this->assignAuxiliarId = '';

        if ($skipped > 0 && $count === 0) {
            $msg = 'Todas las OT ya tenían técnico. Ninguna fue modificada.';
        } elseif ($skipped > 0) {
            $msg = "{$count} OT asignadas ({$skipped} saltadas por ya tener técnico).";
        } else {
            $msg = "{$count} OT(s) asignadas correctamente.";
        }
        $this->dispatch('show-toast', type: 'success', message: $msg);
    }
    public function promptUnassign($otId)
    {
        $wo = WorkOrder::findOrFail($otId);

        if (!$wo->technician_id) {
            $this->dispatch('show-toast', type: 'info', message: 'Esta OT no tiene técnico asignado.');
            return;
        }

        $this->confirmingAction = 'unassign';
        $this->confirmingOrderId = $otId;
    }

    public function unassignTechnician($otId)
    {
        $wo = WorkOrder::findOrFail($otId);

        $wo->update([
            'technician_id' => null,
            'auxiliar_technician_id' => null,
            'vehicle_id' => null,
            'assigned_at' => null,
            'assigned_by' => null,
        ]);

        $this->dispatch('show-toast', type: 'success', message: "OT {$wo->code} desvinculada. Quedó libre para reasignar.");
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
        } elseif ($this->confirmingAction === 'accept') {
            $this->acceptOrder($this->confirmingOrderId);
        } elseif ($this->confirmingAction === 'unassign') {
            $this->unassignTechnician($this->confirmingOrderId);
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

    public function promptQuickNote($orderId)
    {
        $this->confirmingAction = 'quick_note';
        $this->confirmingOrderId = $orderId;
        $this->notes = WorkOrder::find($orderId)?->notes ?? '';
    }

    public function saveQuickNote()
    {
        if (empty(trim($this->notes))) {
            $this->dispatch('show-toast', type: 'error', message: 'La nota no puede estar vacía.');
            return;
        }

        $order = WorkOrder::find($this->confirmingOrderId);
        if ($order) {
            $order->update(['notes' => trim($this->notes)]);
            $this->dispatch('show-toast', type: 'success', message: 'Nota guardada en la OT ' . $order->code . '.');
        }

        $this->confirmingAction = null;
        $this->confirmingOrderId = null;
        $this->notes = '';
    }

    public function cancelQuickNote()
    {
        $this->confirmingAction = null;
        $this->confirmingOrderId = null;
        $this->notes = '';
    }

    public function render()
    {
        $user = Auth::user();
        if ($user) {
            $user->load('roles.permissions');
            Auth::setUser($user);
        }

        $kpis = $this->getKpis();
        $serviceTypes = $this->getServiceTypes();
        $query = $this->getFilteredQuery();

        if ($query === null) {
            $orders = collect();
            $encargados = collect();
            $tecnicos = collect();
            $vehiculos = collect();
            return view('livewire.work-orders.work-order-index', compact(
                'orders', 'encargados', 'tecnicos', 'vehiculos', 'kpis', 'serviceTypes'
            ))->layout('components.layouts.app');
        }

        $encargados = User::role('technician')->encargados()->orderBy('name')->get(['id', 'name']);
        $tecnicos = User::role('technician')->orderBy('name')->get(['id', 'name']);
        $vehiculos = \App\Models\Vehiculo::where('estado', 'activo')->orderBy('placa')->get(['id', 'placa', 'marca', 'modelo']);

        $orders = $query->with(['technician', 'auxiliarTechnician', 'vehicle', 'client', 'ticket', 'zone'])
            ->orderBy('created_at', 'desc')->paginate(50);

        $alreadyAssigned = !empty($this->selectedOrders)
            ? WorkOrder::whereIn('id', $this->selectedOrders)->whereNotNull('technician_id')->count()
            : 0;

        return view('livewire.work-orders.work-order-index', compact(
            'orders', 'encargados', 'tecnicos', 'vehiculos', 'alreadyAssigned', 'kpis', 'serviceTypes'
        ))->layout('components.layouts.app');
    }
}
