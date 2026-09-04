<?php

namespace App\Services;

use App\Models\BranchInventory;
use App\Models\CompanyProductInventory;
use App\Models\Device;
use App\Models\IntercompanySale;
use App\Models\Movement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class IntercompanySaleService
{
    /**
     * Confirma la recepción de una venta entre empresas (Opción A):
     * recién acá se mueve el stock (descuenta vendedora, suma compradora),
     * se mueven dispositivos, se registran movimientos de Kardex y se
     * actualiza el inventario a nivel de empresas.
     */
    public function confirm(IntercompanySale $sale): void
    {
        if ($sale->status === 'confirmed') {
            throw new \Exception("La venta {$sale->code} ya fue confirmada. No se puede confirmar dos veces.");
        }

        if ($sale->status !== 'delivered') {
            throw new \Exception("La venta {$sale->code} debe estar en estado 'Entregado' para confirmarse. Estado actual: {$sale->status}.");
        }

        DB::transaction(function () use ($sale) {
            $sellerBranch = $sale->sellerBranch;
            $buyerBranch = $sale->buyerBranch;

            foreach ($sale->items as $item) {
                $qty = (float) $item->quantity;
                $product = $item->product;
                $requiresDevice = $product?->category?->requires_device_registration ?? false;

                // Validar stock en la vendedora
                $originAvailable = $requiresDevice
                    ? Device::where('product_id', $item->product_id)
                        ->where('branch_id', $sale->seller_branch_id)
                        ->where('status', 'in_stock')->count()
                    : (float) (BranchInventory::where('branch_id', $sale->seller_branch_id)
                        ->where('product_id', $item->product_id)->first()?->allocated_quantity ?? 0);

                if ($originAvailable < $qty) {
                    throw new \Exception("Stock insuficiente en la vendedora para {$item->product_name}. Disponible: {$originAvailable}, requerido: {$qty}");
                }

                // Descontar vendedora
                if ($requiresDevice) {
                    $devices = Device::where('product_id', $item->product_id)
                        ->where('branch_id', $sale->seller_branch_id)
                        ->where('status', 'in_stock')
                        ->take((int) $qty)
                        ->get();

                    foreach ($devices as $device) {
                        $device->update(['branch_id' => $sale->buyer_branch_id, 'status' => 'in_stock']);
                    }
                } else {
                    BranchInventory::where('branch_id', $sale->seller_branch_id)
                        ->where('product_id', $item->product_id)
                        ->decrement('allocated_quantity', $qty);
                }

                // Sumar compradora
                BranchInventory::firstOrCreate([
                    'branch_id' => $sale->buyer_branch_id,
                    'product_id' => $item->product_id,
                ])->increment('allocated_quantity', $qty);

                $cost = (float) ($item->unit_cost ?? 0);

                // Salida (venta) en vendedora
                Movement::create([
                    'product_id' => $item->product_id,
                    'type' => 'exit',
                    'quantity' => $qty,
                    'unit_cost' => $cost,
                    'total_value' => round($qty * $cost, 2),
                    'description' => 'Venta entre empresas: '.$sale->code.' a '.$buyerBranch->name,
                    'user_id' => Auth::id(),
                    'branch_id' => $sale->seller_branch_id,
                    'reference_type' => 'intercompany_sale',
                    'reference_id' => $sale->id,
                ]);

                // Entrada (compra) en compradora
                Movement::create([
                    'product_id' => $item->product_id,
                    'type' => 'entry',
                    'quantity' => $qty,
                    'unit_cost' => $cost,
                    'total_value' => round($qty * $cost, 2),
                    'description' => 'Compra entre empresas: '.$sale->code.' desde '.$sellerBranch->name,
                    'user_id' => Auth::id(),
                    'branch_id' => $sale->buyer_branch_id,
                    'reference_type' => 'intercompany_sale',
                    'reference_id' => $sale->id,
                ]);

                // Inventario a nivel de empresas
                if ($sellerBranch?->company_id && $buyerBranch?->company_id) {
                    app(InventoryService::class)->processCompanySale(
                        $sellerBranch->company_id,
                        $buyerBranch->company_id,
                        $product,
                        $qty,
                        $cost
                    );
                }
            }

            // Transición atómica: solo avanza si sigue en "delivered" (evita doble confirmación en carreras)
            $updated = IntercompanySale::where('id', $sale->id)
                ->where('status', 'delivered')
                ->update([
                    'status' => 'confirmed',
                    'confirmed_by' => Auth::id(),
                    'confirmed_at' => now(),
                ]);

            if (! $updated) {
                throw new \Exception("La venta {$sale->code} ya fue confirmada o cambió de estado.");
            }
        });
    }
}
