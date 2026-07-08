<?php

namespace App\Livewire\Bodega;

use App\Models\DistributionShipment;
use Livewire\Component;
use Livewire\WithPagination;

class DistributionIndex extends Component
{
    use WithPagination;

    public $statusFilter = '';

    public function markInTransit($id)
    {
        $shipment = DistributionShipment::findOrFail($id);
        if ($shipment->status === 'pending') {
            $shipment->update(['status' => 'in_transit', 'in_transit_at' => now()]);
            $this->dispatch('show-toast', type: 'success', message: "{$shipment->code} marcado como en tránsito.");
        }
    }

    public function render()
    {
        $shipments = DistributionShipment::with('branch', 'creator', 'items.product')
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('livewire.bodega.distribution-index', compact('shipments'))
            ->layout('components.layouts.app');
    }
}
