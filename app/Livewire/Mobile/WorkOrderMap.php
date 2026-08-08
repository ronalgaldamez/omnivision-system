<?php

namespace App\Livewire\Mobile;

use Livewire\Component;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\Auth;

class WorkOrderMap extends Component
{
    public function render()
    {
        $workOrders = WorkOrder::with('client')
            ->where('technician_id', Auth::id())
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->map(function ($wo) {
                return [
                    'id' => $wo->id,
                    'latitude' => $wo->latitude,
                    'longitude' => $wo->longitude,
                    'client_name' => $wo->client?->name ?? 'Sin cliente',
                    'client_address' => $wo->client?->address ?? '',
                ];
            })
            ->values();

        return view('livewire.mobile.work-order-map', compact('workOrders'))->layout('components.layouts.app');
    }
}