<?php

namespace App\Livewire\Technicians;

use Livewire\Component;
use App\Models\Requisition;
use App\Models\TechnicianInventory;
use App\Services\InventoryService;
use Illuminate\Support\Facades\Auth;

class RequisitionIndex extends Component
{
    public function closeWeek()
    {
        $user = Auth::user();
        $openRequisitions = Requisition::where('technician_id', $user->id)
            ->where('status', 'open')
            ->get();

        if ($openRequisitions->isEmpty()) {
            $this->dispatch('show-toast', type: 'error', message: 'No tienes requisiciones abiertas.');
            return;
        }

        // Consolidar inventario sobrante
        $inventoryItems = TechnicianInventory::where('technician_id', $user->id)->get();
        if ($inventoryItems->isEmpty()) {
            $this->dispatch('show-toast', type: 'info', message: 'No tienes material pendiente de devolución.');
            return;
        }

        // Crear una devolución por producto (una fila por producto en la tabla technician_returns)
        foreach ($inventoryItems as $inv) {
            if ($inv->quantity_in_hand > 0) {
                \App\Models\TechnicianReturn::create([
                    'user_id' => $user->id,
                    'product_id' => $inv->product_id,
                    'quantity' => $inv->quantity_in_hand,
                    'type' => 'surplus',
                    'notes' => 'Cierre semanal automático - Material sobrante de requisiciones',
                ]);

                // Devolver stock a bodega con costo actual
                $product = $inv->product;
                $returnCost = $product->average_cost ?? 0;

                $movement = \App\Models\Movement::create([
                    'product_id' => $inv->product_id,
                    'type' => 'technician_return',
                    'quantity' => $inv->quantity_in_hand,
                    'description' => 'Devolución cierre semanal (Req. agrupadas)',
                    'user_id' => $user->id,
                    'reference_type' => 'weekly_close',
                ]);

                app(InventoryService::class)->processPurchaseEntry($product, $inv->quantity_in_hand, $returnCost, $movement);

                // Limpiar inventario del técnico
                $inv->update(['quantity_in_hand' => 0]);
            }
        }

        // Marcar requisiciones como cerradas
        foreach ($openRequisitions as $req) {
            $req->update(['status' => 'closed', 'closed_at' => now()]);
        }

        $this->dispatch('show-toast', type: 'success', message: 'Cierre semanal realizado. Material devuelto a bodega.');
        return redirect()->route('technician-returns.index');
    }

    public function render()
    {
        $requisitions = Requisition::where('technician_id', Auth::id())
            ->with('items.product', 'workOrders')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('livewire.technicians.requisition-index', [
            'requisitions' => $requisitions,
        ])->layout('components.layouts.app');
    }
}