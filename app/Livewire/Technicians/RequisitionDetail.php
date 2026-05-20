<?php

namespace App\Livewire\Technicians;

use Livewire\Component;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\TechnicianInventory;
use App\Models\WorkOrderMaterial;
use Illuminate\Support\Facades\Auth;

class RequisitionDetail extends Component
{
    public $requisition;
    public $items = [];
    public $workOrders = [];
    public $selectedWorkOrder = null;
    public $editMode = false;
    public $editReason = '';
    public $consumptionProducts = [];
    public $consumptionQuantities = [];
    public $showConsumptionModal = false;

    protected $rules = [
        'consumptionQuantities.*' => 'nullable|numeric|min:0',
        'editReason' => 'required_if:editMode,true|string|max:255',
    ];

    public function mount($id)
    {
        $this->requisition = Requisition::with('items.product', 'workOrders')->findOrFail($id);
        $this->loadItems();
        $this->loadWorkOrders();
    }

    public function loadItems()
    {
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
        if (!$item)
            return;

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

    // Cargar las OTs vinculadas
    protected function loadWorkOrders()
    {
        $this->workOrders = $this->requisition->workOrders->map(function ($wo) {
            $hasConsumption = WorkOrderMaterial::where('work_order_id', $wo->id)->exists();
            return [
                'id' => $wo->id,
                'name' => 'OT #' . $wo->id,
                'hasConsumption' => $hasConsumption,
            ];
        })->toArray();
    }

    // Seleccionar una OT para editar consumo
    public function selectWorkOrder($workOrderId)
    {
        $this->selectedWorkOrder = $workOrderId;
        $this->editMode = WorkOrderMaterial::where('work_order_id', $workOrderId)->exists();
        $this->editReason = '';
        $this->loadConsumptionData($workOrderId);
        $this->showConsumptionModal = true;
    }

    // Cargar los productos y sus consumos previos para la OT seleccionada
    protected function loadConsumptionData($workOrderId)
    {
        $userId = Auth::id();
        $this->consumptionProducts = $this->requisition->items->map(function ($item) use ($workOrderId, $userId) {
            // Cantidad usada previamente en esta OT
            $previous = WorkOrderMaterial::where('work_order_id', $workOrderId)
                ->where('requisition_item_id', $item->id)
                ->sum('quantity_used');

            // Inventario actual del técnico para este producto
            $inventory = TechnicianInventory::where('technician_id', $userId)
                ->where('product_id', $item->product_id)
                ->value('quantity_in_hand') ?? 0;

            // El máximo que puede poner es inventario + lo que ya había puesto (para editar)
            $max = $inventory + $previous;

            return [
                'requisition_item_id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product->name,
                'product_sku' => $item->product->sku,
                'quantity_requested' => $item->quantity_requested,
                'previous_quantity' => $previous,
                'inventory' => $inventory,
                'max_allowed' => $max,
            ];
        })->toArray();

        // Inicializar cantidades con el valor previo
        $this->consumptionQuantities = array_fill(0, count($this->consumptionProducts), 0);
        foreach ($this->consumptionProducts as $index => $product) {
            $this->consumptionQuantities[$index] = $product['previous_quantity'];
        }
    }

    // Guardar los cambios de consumo para la OT seleccionada
    public function saveConsumption()
    {
        $this->validate();

        $workOrderId = $this->selectedWorkOrder;
        $userId = Auth::id();

        foreach ($this->consumptionProducts as $index => $product) {
            $newQuantity = floatval($this->consumptionQuantities[$index] ?? 0);
            $oldQuantity = $product['previous_quantity'];

            // Validar máximo
            if ($newQuantity > $product['max_allowed']) {
                $this->addError("consumptionQuantities.{$index}", "No puedes usar más de {$product['max_allowed']}.");
                return;
            }

            if ($newQuantity == $oldQuantity)
                continue;

            // Actualizar o crear el registro de material
            WorkOrderMaterial::updateOrCreate(
                [
                    'work_order_id' => $workOrderId,
                    'requisition_item_id' => $product['requisition_item_id'],
                ],
                [
                    'product_id' => $product['product_id'],
                    'quantity_used' => $newQuantity,
                    'notes' => $this->editMode ? $this->editReason : null,
                ]
            );

            // Ajustar inventario del técnico (la diferencia)
            $diff = $newQuantity - $oldQuantity;
            if ($diff != 0) {
                $inventory = TechnicianInventory::where('technician_id', $userId)
                    ->where('product_id', $product['product_id'])
                    ->first();
                if ($inventory) {
                    if ($diff > 0) {
                        $inventory->decrement('quantity_in_hand', $diff);
                    } else {
                        $inventory->increment('quantity_in_hand', abs($diff));
                    }
                }
            }

            // Recalcular quantity_used en la requisición
            $requisitionItem = RequisitionItem::find($product['requisition_item_id']);
            if ($requisitionItem) {
                $totalUsed = WorkOrderMaterial::where('requisition_item_id', $product['requisition_item_id'])
                    ->sum('quantity_used');
                $requisitionItem->quantity_used = $totalUsed;
                $requisitionItem->save();
            }
        }

        // Recargar datos para actualizar colores y tabla
        $this->loadWorkOrders();
        $this->loadItems();
        $this->showConsumptionModal = false;
        $this->dispatch('show-toast', type: 'success', message: 'Consumo guardado correctamente.');
    }

    public function closeConsumptionModal()
    {
        $this->showConsumptionModal = false;
    }

    public function render()
    {
        return view('livewire.technicians.requisition-detail')->layout('components.layouts.app');
    }
}