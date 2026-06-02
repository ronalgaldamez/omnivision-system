<?php

namespace App\Livewire\Technicians;

use Livewire\Component;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\TechnicianInventory;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\Auth;

class RequisitionDetail extends Component
{
    public $requisition;
    public $items = [];
    public $linkedWorkOrders = [];
    public $unlinkedWorkOrders = [];

    protected $listeners = ['refreshComponent' => '$refresh'];

    public function mount($id)
    {
        $this->requisition = Requisition::with('items.product', 'workOrders')->findOrFail($id);

        // Cargar items actuales
        foreach ($this->requisition->items as $item) {
            $this->items[$item->id] = [
                'quantity_used' => $item->quantity_used,
                'quantity_requested' => $item->quantity_requested,
                'product_name' => $item->product->name,
            ];
        }

        $this->loadWorkOrders();
    }

    protected function loadWorkOrders()
    {
        // OTs vinculadas directamente a esta requisición
        $this->linkedWorkOrders = $this->requisition->workOrders()
            ->with('client')
            ->get();

        // OTs del mismo técnico que NO están vinculadas a ninguna requisición abierta
        $this->unlinkedWorkOrders = WorkOrder::where('technician_id', $this->requisition->technician_id)
            ->whereIn('status', ['pending', 'in_progress'])
            ->whereDoesntHave('requisitions', function ($q) {
                $q->where('status', 'open');
            })
            ->with('client')
            ->get();
    }

    public function increment($itemId)
    {
        $item = RequisitionItem::find($itemId);
        if ($item && $item->quantity_used < $item->quantity_requested) {
            $item->quantity_used += 1;
            $item->save();
            $this->updateInventory($item->product_id, 1, 'decrement');
            $this->items[$itemId]['quantity_used'] = $item->quantity_used;
        }
    }

    public function decrement($itemId)
    {
        $item = RequisitionItem::find($itemId);
        if ($item && $item->quantity_used > 0) {
            $item->quantity_used -= 1;
            $item->save();
            $this->updateInventory($item->product_id, 1, 'increment');
            $this->items[$itemId]['quantity_used'] = $item->quantity_used;
        }
    }

    public function updateQuantity($itemId, $newQuantity)
    {
        $item = RequisitionItem::find($itemId);
        if (!$item) return;

        $newQuantity = max(0, min($newQuantity, $item->quantity_requested));
        $diff = $newQuantity - $item->quantity_used;
        $item->quantity_used = $newQuantity;
        $item->save();

        if ($diff != 0) {
            $this->updateInventory($item->product_id, abs($diff), $diff > 0 ? 'decrement' : 'increment');
        }

        $this->items[$itemId]['quantity_used'] = $newQuantity;
    }

    protected function updateInventory($productId, $quantity, $action)
    {
        $userId = Auth::id();
        $inventory = TechnicianInventory::where('technician_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if ($inventory) {
            if ($action === 'decrement') {
                $inventory->decrement('quantity_in_hand', $quantity);
            } else {
                $inventory->increment('quantity_in_hand', $quantity);
            }
        }
    }

    public function render()
    {
        return view('livewire.technicians.requisition-detail', [
            'requisition' => $this->requisition,
        ])->layout('components.layouts.app');
    }
}