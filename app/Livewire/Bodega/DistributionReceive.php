<?php

namespace App\Livewire\Bodega;

use App\Models\BranchInventory;
use App\Models\Device;
use App\Models\DistributionShipment;
use App\Models\Movement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class DistributionReceive extends Component
{
    public $code = '';
    public $shipment = null;
    public $found = false;

    public function mount($code = null)
    {
        if ($code) {
            $this->code = $code;
            $this->search();
        }
    }

    public function search()
    {
        $this->shipment = DistributionShipment::with('items.product', 'branch', 'creator')
            ->where('code', $this->code)
            ->first();

        if (!$this->shipment) {
            $this->dispatch('show-toast', type: 'error', message: 'No se encontró ningún envío con ese código.');
            $this->found = false;
            return;
        }

        $this->found = true;
    }

    public function confirm()
    {
        if (!$this->shipment || $this->shipment->status !== 'delivered') {
            $this->dispatch('show-toast', type: 'error', message: 'El envío debe estar en estado "Entregado" para confirmarlo.');
            return;
        }

        DB::beginTransaction();
        try {
            foreach ($this->shipment->items as $item) {
                $qty = (float) $item->quantity;

                BranchInventory::firstOrCreate([
                    'branch_id' => $this->shipment->branch_id,
                    'product_id' => $item->product_id,
                ])->increment('allocated_quantity', $qty);

                $devices = Device::where('product_id', $item->product_id)
                    ->where('branch_id', $this->shipment->branch_id)
                    ->whereNull('technician_id')
                    ->take((int) $qty)
                    ->get();

                foreach ($devices as $device) {
                    $device->update(['status' => 'in_stock']);
                }

                Movement::create([
                    'product_id' => $item->product_id,
                    'type' => 'branch_allocation',
                    'quantity' => $qty,
                    'unit_cost' => $item->product->average_cost ?? 0,
                    'total_value' => ($qty * ($item->product->average_cost ?? 0)),
                    'description' => 'Recepción confirmada: ' . $this->shipment->code,
                    'user_id' => Auth::id(),
                    'branch_id' => $this->shipment->branch_id,
                    'reference_type' => 'shipment',
                    'reference_id' => $this->shipment->id,
                ]);
            }

            $this->shipment->update([
                'status' => 'confirmed',
                'confirmed_by' => Auth::id(),
                'confirmed_at' => now(),
            ]);

            DB::commit();
            $this->dispatch('show-toast', type: 'success', message: "Envío {$this->shipment->code} confirmado. Stock actualizado.");
            $this->reset(['code', 'shipment', 'found']);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('show-toast', type: 'error', message: 'Error: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.bodega.distribution-receive')->layout('components.layouts.app');
    }
}
