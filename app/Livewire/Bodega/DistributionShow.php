<?php

namespace App\Livewire\Bodega;

use App\Models\DistributionShipment;
use Livewire\Component;

class DistributionShow extends Component
{
    public $shipment;

    public function mount($id)
    {
        $this->shipment = DistributionShipment::with('branch', 'creator', 'confirmer', 'items.product')
            ->findOrFail($id);
    }

    public function markInTransit()
    {
        if ($this->shipment->status === 'pending') {
            $this->shipment->update(['status' => 'in_transit', 'in_transit_at' => now()]);
            $this->dispatch('show-toast', type: 'success', message: 'Envío marcado como en tránsito.');
        }
    }

    public function markDelivered()
    {
        if ($this->shipment->status === 'in_transit') {
            $this->shipment->update(['status' => 'delivered', 'delivered_at' => now()]);
            $this->dispatch('show-toast', type: 'success', message: 'Envío marcado como entregado.');
        }
    }

    public function render()
    {
        $steps = [
            [
                'key' => 'pending',
                'icon' => 'pending',
                'name' => 'Pendiente',
                'description' => 'Envío creado, esperando salida de bodega',
                'timestamp' => $this->shipment->created_at,
                'isCompleted' => true,
                'isActive' => $this->shipment->status === 'pending',
                'user' => $this->shipment->creator?->name,
            ],
            [
                'key' => 'in_transit',
                'icon' => 'local_shipping',
                'name' => 'En tránsito',
                'description' => 'Salió de bodega, en camino a sucursal',
                'timestamp' => $this->shipment->in_transit_at,
                'isCompleted' => in_array($this->shipment->status, ['in_transit', 'delivered', 'confirmed']),
                'isActive' => $this->shipment->status === 'in_transit',
                'user' => null,
            ],
            [
                'key' => 'delivered',
                'icon' => 'inventory',
                'name' => 'Entregado',
                'description' => 'Llegó a la sucursal, pendiente de confirmación',
                'timestamp' => $this->shipment->delivered_at,
                'isCompleted' => in_array($this->shipment->status, ['delivered', 'confirmed']),
                'isActive' => $this->shipment->status === 'delivered',
                'user' => null,
            ],
            [
                'key' => 'confirmed',
                'icon' => 'check_circle',
                'name' => 'Confirmado',
                'description' => 'Recibido y verificado en sucursal',
                'timestamp' => $this->shipment->confirmed_at,
                'isCompleted' => $this->shipment->status === 'confirmed',
                'isActive' => $this->shipment->status === 'confirmed',
                'user' => $this->shipment->confirmer?->name,
            ],
        ];

        $statusColors = [
            'pending' => ['bg-gray-100 text-gray-700', 'bg-gray-500'],
            'in_transit' => ['bg-blue-50 text-blue-700', 'bg-blue-500'],
            'delivered' => ['bg-amber-50 text-amber-700', 'bg-amber-500'],
            'confirmed' => ['bg-green-50 text-green-700', 'bg-green-500'],
        ];

        return view('livewire.bodega.distribution-show', compact('steps', 'statusColors'))
            ->layout('components.layouts.app');
    }
}
