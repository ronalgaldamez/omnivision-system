<?php

namespace App\Livewire\WorkOrders;

use Livewire\Component;
use App\Models\WorkOrder;

class WorkOrderMap extends Component
{
    public function render()
    {
        $workOrders = WorkOrder::whereNotNull('latitude')->whereNotNull('longitude')->get();
        return view('livewire.work-orders.work-order-map', compact('workOrders'))->layout('components.layouts.app');
    }
}