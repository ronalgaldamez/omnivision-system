<?php

namespace App\Livewire\Mobile;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\Auth;

class WorkOrderList extends Component
{
    use WithPagination;

    public $statusFilter = 'pending,in_progress'; // por defecto ver pendientes y en progreso
    public $search = '';

    public function render()
    {
        $statusArray = explode(',', $this->statusFilter);
        $orders = WorkOrder::with('client') // <-- agregar esta relación
            ->where('technician_id', Auth::id())
            ->whereIn('status', $statusArray)
            ->when($this->search, function ($q) {
                $q->whereHas('client', function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('address', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('scheduled_date', 'asc')
            ->paginate(10);

        return view('livewire.mobile.work-order-list', compact('orders'))->layout('components.layouts.app');
    }
}