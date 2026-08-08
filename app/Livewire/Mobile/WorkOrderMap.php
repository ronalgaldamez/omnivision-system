<?php

namespace App\Livewire\Mobile;

use Livewire\Component;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\Auth;

class WorkOrderMap extends Component
{
    public function render()
    {
        $workOrders = WorkOrder::with(['client', 'ticket'])
            ->where('technician_id', Auth::id())
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->map(function ($wo) {
                return [
                    'id' => $wo->id,
                    'code' => $wo->code ?? ('OT-' . $wo->id),
                    'latitude' => $wo->latitude,
                    'longitude' => $wo->longitude,
                    'status' => $wo->status,
                    'priority' => $wo->ticket?->priority ?? '—',
                    'client_name' => $wo->client?->name ?? 'Sin cliente',
                    'client_address' => $wo->client?->address ?? '',
                ];
            })
            ->values();

        return view('livewire.mobile.work-order-map', compact('workOrders'))->layout('components.layouts.app');
    }
}