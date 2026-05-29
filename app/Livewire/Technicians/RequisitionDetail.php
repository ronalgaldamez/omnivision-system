<?php

namespace App\Livewire\Technicians;

use Livewire\Component;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\TechnicianInventory;
use Illuminate\Support\Facades\Auth;

class RequisitionDetail extends Component
{
    public $requisition;
    public $items = [];

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
            // Si diff > 0: usó más, hay que descontar del inventario del técnico
            // Si diff < 0: usó menos, hay que devolver al inventario
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