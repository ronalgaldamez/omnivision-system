<?php

namespace App\Livewire\Bodega;

use App\Models\BranchInventory;
use App\Models\CompanyProductInventory;
use App\Models\Device;
use App\Models\DistributionShipment;
use App\Models\Movement;
use App\Models\Product;
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
        $this->shipment = DistributionShipment::with('items.product', 'originBranch', 'branch', 'creator')
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

        if (!$this->shipment->origin_branch_id) {
            $this->dispatch('show-toast', type: 'error', message: 'Este envío no tiene sucursal de origen. No se puede confirmar.');
            return;
        }

        if ($this->shipment->origin_branch_id === $this->shipment->branch_id) {
            $this->dispatch('show-toast', type: 'error', message: 'El origen y el destino son la misma sucursal. Envío inválido.');
            return;
        }

        DB::beginTransaction();
        try {
            $originId = $this->shipment->origin_branch_id;
            $destId = $this->shipment->branch_id;
            $originCompanyId = $this->shipment->originBranch?->company_id;

            foreach ($this->shipment->items as $item) {
                $qty = (float) $item->quantity;
                $product = $item->product;
                $requiresDevice = $product?->category?->requires_device_registration ?? false;

                // ── Stock de la sucursal origen ──
                $originInv = BranchInventory::where('branch_id', $originId)
                    ->where('product_id', $item->product_id)
                    ->first();

                $originAvailable = $requiresDevice
                    ? Device::where('product_id', $item->product_id)->where('branch_id', $originId)->where('status', 'in_stock')->count()
                    : (float) ($originInv->allocated_quantity ?? 0);

                if ($originAvailable < $qty) {
                    throw new \Exception("Stock insuficiente en la sucursal de origen para {$item->product_name}. Disponible: {$originAvailable}, requerido: {$qty}");
                }

                // ── Descontar origen ──
                if ($requiresDevice) {
                    $devices = Device::where('product_id', $item->product_id)
                        ->where('branch_id', $originId)
                        ->where('status', 'in_stock')
                        ->take((int) $qty)
                        ->get();

                    foreach ($devices as $device) {
                        $device->update(['branch_id' => $destId, 'status' => 'in_stock']);
                    }
                } else {
                    $originInv->decrement('allocated_quantity', $qty);
                }

                // ── Sumar destino ──
                BranchInventory::firstOrCreate([
                    'branch_id' => $destId,
                    'product_id' => $item->product_id,
                ])->increment('allocated_quantity', $qty);

                // ── Costo: el de la empresa del origen (traspaso no cambia el costo) ──
                $cost = null;
                if ($originCompanyId) {
                    $cost = CompanyProductInventory::averageCostFor($originCompanyId, $item->product_id);
                }
                $cost = $cost ?? (float) ($product?->average_cost ?? 0);

                // ── Movimiento de salida en el Kardex de la sucursal origen ──
                Movement::create([
                    'product_id' => $item->product_id,
                    'type' => 'exit',
                    'quantity' => $qty,
                    'unit_cost' => $cost,
                    'total_value' => round($qty * $cost, 2),
                    'description' => 'Traspaso saliente: ' . $this->shipment->code,
                    'user_id' => Auth::id(),
                    'branch_id' => $originId,
                    'reference_type' => 'shipment',
                    'reference_id' => $this->shipment->id,
                ]);

                // ── Movimiento de entrada en el Kardex de la sucursal destino ──
                Movement::create([
                    'product_id' => $item->product_id,
                    'type' => 'entry',
                    'quantity' => $qty,
                    'unit_cost' => $cost,
                    'total_value' => round($qty * $cost, 2),
                    'description' => 'Traspaso entrante: ' . $this->shipment->code,
                    'user_id' => Auth::id(),
                    'branch_id' => $destId,
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
            $this->dispatch('show-toast', type: 'success', message: "Traspaso {$this->shipment->code} confirmado. Origen y destino actualizados.");
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
