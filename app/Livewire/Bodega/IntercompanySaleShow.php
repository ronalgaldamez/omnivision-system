<?php

namespace App\Livewire\Bodega;

use App\Models\IntercompanySale;
use App\Services\IntercompanySaleService;
use Livewire\Component;

class IntercompanySaleShow extends Component
{
    public $sale;

    public $steps = [];

    public function mount($id)
    {
        $this->sale = IntercompanySale::with('sellerBranch', 'buyerBranch', 'user', 'confirmer', 'items.product')
            ->findOrFail($id);

        $this->buildSteps();
    }

    private function buildSteps(): void
    {
        $this->steps = [
            [
                'key' => 'pending',
                'icon' => 'pending',
                'name' => 'Pendiente',
                'description' => 'Venta creada, esperando salida',
                'timestamp' => $this->sale->created_at,
                'user' => $this->sale->user?->name,
                'isCompleted' => true,
                'isActive' => $this->sale->status === 'pending',
            ],
            [
                'key' => 'in_transit',
                'icon' => 'local_shipping',
                'name' => 'En tránsito',
                'description' => 'La vendedora despachó el material',
                'timestamp' => $this->sale->in_transit_at,
                'user' => null,
                'isCompleted' => in_array($this->sale->status, ['in_transit', 'delivered', 'confirmed'], true),
                'isActive' => $this->sale->status === 'in_transit',
            ],
            [
                'key' => 'delivered',
                'icon' => 'inventory_2',
                'name' => 'Entregado',
                'description' => 'El material llegó a la compradora',
                'timestamp' => $this->sale->delivered_at,
                'user' => null,
                'isCompleted' => in_array($this->sale->status, ['delivered', 'confirmed'], true),
                'isActive' => $this->sale->status === 'delivered',
            ],
            [
                'key' => 'confirmed',
                'icon' => 'check_circle',
                'name' => 'Confirmado',
                'description' => 'La compradora confirmó la recepción y el stock se movió',
                'timestamp' => $this->sale->confirmed_at,
                'user' => $this->sale->confirmer?->name,
                'isCompleted' => $this->sale->status === 'confirmed',
                'isActive' => $this->sale->status === 'confirmed',
            ],
        ];
    }

    public function markInTransit()
    {
        if ($this->sale->status !== 'pending') {
            $this->dispatch('show-toast', type: 'error', message: 'La venta ya no está pendiente.');
            return;
        }
        $this->sale->update(['status' => 'in_transit', 'in_transit_at' => now()]);
        $this->sale->refresh();
        $this->buildSteps();
        $this->dispatch('show-toast', type: 'success', message: 'Venta marcada como en tránsito.');
    }

    public function markDelivered()
    {
        if ($this->sale->status !== 'in_transit') {
            $this->dispatch('show-toast', type: 'error', message: 'La venta no está en tránsito.');
            return;
        }
        $this->sale->update(['status' => 'delivered', 'delivered_at' => now()]);
        $this->sale->refresh();
        $this->buildSteps();
        $this->dispatch('show-toast', type: 'success', message: 'Venta marcada como entregada.');
    }

    public function confirm()
    {
        if ($this->sale->status !== 'delivered') {
            $this->dispatch('show-toast', type: 'error', message: 'La venta debe estar en estado "Entregado" para confirmarla.');
            return;
        }

        try {
            app(IntercompanySaleService::class)->confirm($this->sale);
            $this->sale->refresh();
            $this->buildSteps();
            $this->dispatch('show-toast', type: 'success', message: 'Venta confirmada. El stock se movió a la compradora.');
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Error: '.$e->getMessage());
        }
    }

    public function render()
    {
        $statusMap = [
            'pending' => ['Pendiente', 'bg-gray-100 text-gray-700'],
            'in_transit' => ['En tránsito', 'bg-blue-50 text-blue-700'],
            'delivered' => ['Entregado', 'bg-amber-50 text-amber-700'],
            'confirmed' => ['Confirmado', 'bg-green-50 text-green-700'],
        ];

        $statusInfo = $statusMap[$this->sale->status] ?? ['Desconocido', 'bg-gray-100 text-gray-700'];

        return view('livewire.bodega.intercompany-sale-show', [
            'statusInfo' => $statusInfo,
        ])->layout('components.layouts.app');
    }
}
