<?php

namespace App\Livewire\Mobile;

use Livewire\Component;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\Auth;

class WorkOrderMap extends Component
{
    public function render()
    {
        $workOrders = WorkOrder::where('technician_id', Auth::id())
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();
        return view('livewire.mobile.work-order-map', compact('workOrders'))->layout('components.layouts.app');
    }
}