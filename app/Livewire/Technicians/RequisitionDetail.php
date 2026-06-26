<?php

namespace App\Livewire\Technicians;

use Livewire\Component;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\TechnicianInventory;
use App\Models\WorkOrder;
use App\Models\WorkOrderMaterial;
use Illuminate\Support\Facades\Auth;

class RequisitionDetail extends Component
{
    public $requisition;
    public $linkedWorkOrders = [];
    public $unlinkedWorkOrders = [];

    public $selectedWorkOrderId = null;
    public $selectedWoMaterials = [];

    public $showEditModal = false;
    public $editingMaterials = [];

    protected $listeners = ['refreshComponent' => '$refresh'];

    public function mount($id)
    {
        $this->requisition = Requisition::with('items.product', 'workOrders', 'technician')->findOrFail($id);
        $this->loadWorkOrders();
    }

    public function linkWorkOrder($workOrderId)
    {
        $this->requisition->workOrders()->syncWithoutDetaching([$workOrderId]);
        $this->loadWorkOrders();
        $this->dispatch('show-toast', type: 'success', message: 'OT vinculada a la requisición.');
    }

    public function selectWorkOrder($workOrderId)
    {
        $this->selectedWorkOrderId = $workOrderId;

        $requisitionItemIds = $this->requisition->items->pluck('id');

        $materials = WorkOrderMaterial::where('work_order_id', $workOrderId)
            ->whereIn('requisition_item_id', $requisitionItemIds)
            ->with('product', 'requisitionItem')
            ->get();

        $this->selectedWoMaterials = $materials->groupBy('product_id')
            ->map(function ($items) {
                $first = $items->first();
                return [
                    'product_id' => $first->product_id,
                    'product_name' => $first->product->name,
                    'product_sku' => $first->product->sku,
                    'quantity_requested' => $items->sum(fn($i) => $i->requisitionItem->quantity_requested ?? 0),
                    'quantity_used' => $items->sum('quantity_used'),
                    'requisition_item_ids' => $items->pluck('requisition_item_id')->toArray(),
                    'ids' => $items->pluck('id')->toArray(),
                ];
            })
            ->values()
            ->toArray();
    }

    public function openEditModal()
    {
        if ($this->requisition->status !== 'open') {
            $this->dispatch('show-toast', type: 'error', message: 'Solo se puede ajustar la requisición activa.');
            return;
        }

        $this->editingMaterials = [];

        foreach ($this->selectedWoMaterials as $mat) {
            $this->editingMaterials[] = [
                'product_id' => $mat['product_id'],
                'product_name' => $mat['product_name'],
                'product_sku' => $mat['product_sku'],
                'quantity_requested' => $mat['quantity_requested'],
                'quantity_used' => $mat['quantity_used'],
                'ids' => $mat['ids'],
                'motivo' => '',
            ];
        }

        $this->showEditModal = true;
    }

    public function saveEditModal()
    {
        if ($this->requisition->status !== 'open') {
            $this->dispatch('show-toast', type: 'error', message: 'Solo se puede ajustar la requisición activa.');
            return;
        }

        foreach ($this->editingMaterials as $edit) {
            $materials = WorkOrderMaterial::whereIn('id', $edit['ids'])->orderBy('id')->get();
            $oldTotal = $materials->sum('quantity_used');
            $newTotal = max(0, (float)$edit['quantity_used']);
            $diff = $newTotal - $oldTotal;

            if ($diff == 0) continue;

            $this->updateInventory($edit['product_id'], abs($diff), $diff > 0 ? 'decrement' : 'increment');

            $remaining = abs($diff);
            $sorted = $diff > 0
                ? $materials->sortBy('id')          // increase: fill oldest first
                : $materials->sortByDesc('id');      // decrease: empty newest first

            foreach ($sorted as $material) {
                if ($remaining <= 0) break;

                $available = $diff > 0
                    ? ($material->requisitionItem
                        ? ($material->requisitionItem->quantity_requested - $material->requisitionItem->quantity_used + $material->quantity_used)
                        : 0)
                    : $material->quantity_used;

                $take = min($remaining, $available);
                if ($take <= 0) continue;

                $material->quantity_used += ($diff > 0 ? $take : -$take);
                if (!empty($edit['motivo'])) {
                    $material->notes = $edit['motivo'];
                }
                $material->save();
                $remaining -= $take;
            }

            foreach ($materials as $material) {
                $this->recalculateRequisitionItemUsage($material->requisition_item_id);
            }
        }

        $this->showEditModal = false;
        $this->dispatch('show-toast', type: 'success', message: 'Material ajustado correctamente.');

        $this->requisition->load('items.product');
        $this->selectWorkOrder($this->selectedWorkOrderId);
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->editingMaterials = [];
    }

    protected function recalculateRequisitionItemUsage($requisitionItemId)
    {
        $totalUsed = WorkOrderMaterial::where('requisition_item_id', $requisitionItemId)
            ->sum('quantity_used');

        $item = RequisitionItem::find($requisitionItemId);
        if ($item) {
            $item->quantity_used = $totalUsed;
            $item->save();
        }
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

    protected function loadWorkOrders()
    {
        $this->linkedWorkOrders = $this->requisition->workOrders()
            ->with('client')
            ->get();

        $this->unlinkedWorkOrders = WorkOrder::where('technician_id', $this->requisition->technician_id)
            ->whereIn('status', ['pending', 'in_progress'])
            ->whereDoesntHave('requisitions', function ($q) {
                $q->where('status', 'open');
            })
            ->with('client')
            ->get();
    }

    public function render()
    {
        $totalRequested = $this->requisition->items->sum('quantity_requested');
        $totalUsed = $this->requisition->items->sum('quantity_used');
        $inventoryItems = TechnicianInventory::where('technician_id', Auth::id())->get();
        $totalInHand = $inventoryItems->sum('quantity_in_hand');
        $hasWeeklyClose = \App\Models\TechnicianReturn::where('user_id', Auth::id())
            ->where('type', 'surplus')
            ->where('notes', 'like', '%Cierre semanal%')
            ->exists();

        return view('livewire.technicians.requisition-detail', [
            'requisition' => $this->requisition,
            'totalRequested' => $totalRequested,
            'totalUsed' => $totalUsed,
            'totalInHand' => $totalInHand,
            'hasWeeklyClose' => $hasWeeklyClose,
        ])->layout('components.layouts.app');
    }
}
