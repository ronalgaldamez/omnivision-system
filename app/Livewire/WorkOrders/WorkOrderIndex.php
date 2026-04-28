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

    public function render()
    {
        $user = Auth::user();
        $query = WorkOrder::with('technician', 'client');

        // ========== FILTRO SEGÚN PERMISOS ==========
        if ($user->can('view all work orders')) {
            // Ver todas las órdenes (sin restricción)
        } elseif ($user->can('view own work_orders')) {
            // Ver solo las órdenes relacionadas con tickets que el usuario creó o resolvió
            // Para NOC/secretaria: órdenes donde el ticket tiene created_by = user id
            $query->whereHas('ticket', function($q) use ($user) {
                $q->where('created_by', $user->id);
            });
            // Si la orden no tiene ticket (caso raro), no la muestra
        } else {
            // Sin permiso, lista vacía
            $orders = collect();
            return view('livewire.work-orders.work-order-index', compact('orders'))->layout('components.layouts.app');
        }

        // Filtros adicionales
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }
        if ($this->search) {
            $query->where(function($q) {
                $q->whereHas('client', fn($c) => $c->where('name', 'like', '%'.$this->search.'%'))
                  ->orWhereHas('technician', fn($t) => $t->where('name', 'like', '%'.$this->search.'%'));
            });
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(10);
        return view('livewire.work-orders.work-order-index', compact('orders'))->layout('components.layouts.app');
    }

    public function delete($id)
    {
        $user = Auth::user();
        $order = WorkOrder::findOrFail($id);

        // Solo admin o quien tenga permiso 'delete work_orders' puede eliminar
        if ($user->cannot('delete work_orders')) {
            session()->flash('error', 'No tienes permiso para eliminar órdenes.');
            return;
        }

        if ($order->status !== 'pending') {
            session()->flash('error', 'Solo se pueden eliminar órdenes pendientes.');
            return;
        }
        $order->delete();
        session()->flash('message', 'Orden eliminada.');
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
}