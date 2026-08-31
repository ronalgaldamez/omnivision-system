<?php

namespace App\Livewire\Technicians;

use Livewire\Component;
use App\Models\Device;
use App\Models\Requisition;
use App\Models\TechnicianInventory;
use App\Services\InventoryService;
use Illuminate\Support\Facades\Auth;

class RequisitionIndex extends Component
{
    public $showCloseModal = false;
    public $closeSummary = [];
    public $closeReturnQty = [];

    public function closeWeek()
    {
        $user = Auth::user();
        $openRequisitions = Requisition::where('technician_id', $user->id)
            ->where('status', 'approved')
            ->get();

        if ($openRequisitions->isEmpty()) {
            $this->dispatch('show-toast', type: 'error', message: 'No tienes requisiciones abiertas.');
            return;
        }

        $inventoryItems = TechnicianInventory::where('technician_id', $user->id)
            ->where('quantity_in_hand', '>', 0)
            ->with('product')
            ->get();

        if ($inventoryItems->isEmpty()) {
            $this->dispatch('show-toast', type: 'info', message: 'No tienes material pendiente de devolución.');
            return;
        }

        $productIds = $inventoryItems->pluck('product_id');
        $entregado = \App\Models\RequisitionItem::whereIn('product_id', $productIds)
            ->whereHas('requisition', fn($q) => $q->where('technician_id', $user->id)->where('status', 'approved'))
            ->selectRaw('product_id, SUM(quantity_requested) as total')
            ->groupBy('product_id')
            ->pluck('total', 'product_id');

        $this->closeSummary = [];
        $this->closeReturnQty = [];
        foreach ($inventoryItems as $inv) {
            $sobrante = (float) $inv->quantity_in_hand;
            $entregadoVal = (float) ($entregado[$inv->product_id] ?? 0);
            $this->closeSummary[] = [
                'product_id' => $inv->product_id,
                'product_name' => $inv->product->name,
                'unit' => $inv->product->unitLabel(),
                'entregado' => $entregadoVal,
                'usado' => max(0, $entregadoVal - $sobrante),
            ];
            $this->closeReturnQty[$inv->product_id] = $sobrante;
        }

        $this->showCloseModal = true;
    }

    public function cancelClose()
    {
        $this->showCloseModal = false;
        $this->closeSummary = [];
        $this->closeReturnQty = [];
    }

    public function confirmClose()
    {
        $user = Auth::user();
        $openRequisitions = Requisition::where('technician_id', $user->id)
            ->where('status', 'approved')
            ->get();

        foreach ($this->closeSummary as $row) {
            $qty = (float) ($this->closeReturnQty[$row['product_id']] ?? 0);
            if ($qty <= 0) continue;

            $inv = TechnicianInventory::where('technician_id', $user->id)
                ->where('product_id', $row['product_id'])
                ->first();

            if (!$inv || $qty > (float) $inv->quantity_in_hand) {
                $this->dispatch('show-toast', type: 'error', message: "Cantidad inválida para {$row['product_name']}.");
                return;
            }

            $product = $inv->product;
            $returnCost = $product->average_cost ?? 0;

            \App\Models\TechnicianReturn::create([
                'user_id' => $user->id,
                'product_id' => $row['product_id'],
                'quantity' => $qty,
                'type' => 'surplus',
                'notes' => 'Cierre semanal automático - Material sobrante de requisiciones',
            ]);

            $movement = \App\Models\Movement::create([
                'product_id' => $row['product_id'],
                'type' => 'technician_return',
                'quantity' => $qty,
                'description' => 'Devolución cierre semanal (Req. agrupadas)',
                'user_id' => $user->id,
                'reference_type' => 'weekly_close',
            ]);

            app(InventoryService::class)->processPurchaseEntry($product, $qty, $returnCost, $movement);

            $newQty = (float) $inv->quantity_in_hand - $qty;
            $inv->update(['quantity_in_hand' => max(0, $newQty)]);
        }

        $returnedDevices = Device::where('technician_id', $user->id)
            ->where('status', 'assigned')
            ->count();

        if ($returnedDevices > 0) {
            Device::where('technician_id', $user->id)
                ->where('status', 'assigned')
                ->update([
                    'status' => 'in_stock',
                    'technician_id' => null,
                    'assigned_at' => null,
                ]);
        }

        foreach ($openRequisitions as $req) {
            $req->update(['status' => 'closed', 'closed_at' => now()]);
        }

        $this->showCloseModal = false;
        $message = $returnedDevices > 0
            ? "Cierre semanal realizado. Material y {$returnedDevices} dispositivo(s) devueltos a bodega."
            : 'Cierre semanal realizado. Material devuelto a bodega.';
        $this->dispatch('show-toast', type: 'success', message: $message);
        return redirect()->route('technician-returns.index');
    }

    public function render()
    {
        $requisitions = Requisition::where('technician_id', Auth::id())
            ->with('items.product', 'workOrders')
            ->orderBy('created_at', 'desc')
            ->get();

        $hasPending = $requisitions->contains(fn($r) => $r->status === 'pending');
        return view('livewire.technicians.requisition-index', [
            'requisitions' => $requisitions,
            'hasPending' => $hasPending,
        ])->layout('components.layouts.app');
    }
}