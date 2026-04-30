<?php

namespace App\Livewire\WorkOrders;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\Auth;

class WorkOrderIndex extends Component
{
    use WithPagination;

    public $statusFilter = '';
    public $search = '';

    // Propiedades para confirmación de eliminación
    public $confirmingAction = null;   // 'delete'
    public $confirmingOrderId = null;

    public function render()
    {
        // Recargar los permisos del usuario desde la base de datos para evitar la caché de sesión
        $user = Auth::user();
        if ($user) {
            // Forzamos la recarga de la relación roles y sus permisos
            $user->load('roles.permissions');
            // Actualizamos la instancia en el contenedor de autorización
            Auth::setUser($user);
        }

        $query = WorkOrder::with('technician', 'client', 'ticket');

        if ($user->can('view all work orders')) {
            // todas
        } elseif ($user->can('view own work_orders')) {
            $query->whereHas('ticket', function ($q) use ($user) {
                $q->where('created_by', $user->id)
                    ->orWhere('resolved_by', $user->id);
            });
        } else {
            $orders = collect();
            return view('livewire.work-orders.work-order-index', compact('orders'))->layout('components.layouts.app');
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

        $orders = $query->orderBy('created_at', 'desc')->paginate(10);
        return view('livewire.work-orders.work-order-index', compact('orders'))->layout('components.layouts.app');
    }

    // Prepara la confirmación para eliminar
    public function promptDelete($id)
    {
        $user = Auth::user();
        $order = WorkOrder::findOrFail($id);

        // Verificar permiso
        if ($user->cannot('delete work_orders')) {
            $this->dispatch('show-toast', type: 'error', message: 'No tienes permiso para eliminar órdenes.');
            return;
        }
        // Verificar que esté pendiente
        if ($order->status !== 'pending') {
            $this->dispatch('show-toast', type: 'error', message: 'Solo se pueden eliminar órdenes pendientes.');
            return;
        }

        $this->confirmingAction = 'delete';
        $this->confirmingOrderId = $id;
    }

    // Ejecuta la acción confirmada
    public function executeConfirmedAction()
    {
        if ($this->confirmingAction === 'delete') {
            $this->delete($this->confirmingOrderId);
        }

        $this->confirmingAction = null;
        $this->confirmingOrderId = null;
    }

    // Cancela la confirmación
    public function cancelConfirmation()
    {
        $this->confirmingAction = null;
        $this->confirmingOrderId = null;
    }

    // Eliminar (lógica original, ahora con toasts)
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

    // Método nearby (se mantiene igual)
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
}