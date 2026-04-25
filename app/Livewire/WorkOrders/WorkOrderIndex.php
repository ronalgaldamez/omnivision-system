<?php

namespace App\Livewire\WorkOrders;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\WorkOrder;

class WorkOrderIndex extends Component
{
    use WithPagination;

    public $statusFilter = '';
    public $search = '';

    public function render()
    {
        $orders = WorkOrder::with('technician', 'client')  // ← añade 'client'
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->search, fn($q) => $q->whereHas('client', function ($q2) {
                $q2->where('name', 'like', '%' . $this->search . '%');
            })
                ->orWhereHas('technician', fn($q2) => $q2->where('name', 'like', '%' . $this->search . '%')))
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.work-orders.work-order-index', compact('orders'))->layout('components.layouts.app');
    }

    public function delete($id)
    {
        $order = WorkOrder::findOrFail($id);
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