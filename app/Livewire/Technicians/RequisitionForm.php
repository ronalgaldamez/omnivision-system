<?php

namespace App\Livewire\Technicians;

use Livewire\Component;
use App\Models\WorkOrder;
use App\Models\Product;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\TechnicianInventory;
use Illuminate\Support\Facades\Auth;

class RequisitionForm extends Component
{
    public $selectedWorkOrders = [];
    public $search = '';
    public $productResults = [];
    public $currentProductId;
    public $currentProductSearch = '';
    public $currentQuantity = 1;
    public $items = [];
    public $notes = '';
    public $confirmingSave = false;

    // Inventario actual del técnico (para referencia)
    public $technicianStock = [];

    protected function rules()
    {
        return [
            'selectedWorkOrders' => 'required|array|min:1',
            'selectedWorkOrders.*' => 'exists:work_orders,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
        ];
    }

    public function mount()
    {
        $this->loadTechnicianStock();
    }

    public function loadTechnicianStock()
    {
        $userId = Auth::id();
        $inventory = TechnicianInventory::where('technician_id', $userId)
            ->with('product')
            ->get();

        $this->technicianStock = $inventory->mapWithKeys(function ($item) {
            return [$item->product_id => [
                'name' => $item->product->name,
                'quantity' => $item->quantity_in_hand,
            ]];
        })->toArray();
    }

    public function updatedCurrentProductSearch()
    {
        if (strlen($this->currentProductSearch) >= 2) {
            $this->productResults = Product::where('name', 'like', '%' . $this->currentProductSearch . '%')
                ->orWhere('sku', 'like', '%' . $this->currentProductSearch . '%')
                ->limit(10)
                ->get();
        } else {
            $this->productResults = [];
        }
    }

    public function selectProduct($id)
    {
        $product = Product::find($id);
        if ($product) {
            $this->currentProductId = $product->id;
            $this->currentProductSearch = $product->name . ' (' . $product->sku . ')';
            $this->productResults = [];
        }
    }

    public function addItem()
    {
        $this->validate([
            'currentProductId' => 'required|exists:products,id',
            'currentQuantity' => 'required|numeric|min:0.01',
        ]);

        $product = Product::find($this->currentProductId);
        $this->items[] = [
            'product_id' => $this->currentProductId,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'quantity' => $this->currentQuantity,
        ];

        $this->currentProductSearch = '';
        $this->currentProductId = null;
        $this->currentQuantity = 1;
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function promptSave()
    {
        $this->validate();
        $this->confirmingSave = true;
    }

    public function executeSave()
    {
        $this->confirmingSave = false;
        $this->save();
    }

    public function cancelSave()
    {
        $this->confirmingSave = false;
    }

    public function save()
    {
        $requisition = Requisition::create([
            'technician_id' => Auth::id(),
            'status' => 'open',
            'week_start_date' => now()->startOfWeek(),
            'notes' => $this->notes,
        ]);

        $requisition->workOrders()->sync($this->selectedWorkOrders);

        foreach ($this->items as $item) {
            $product = Product::find($item['product_id']);

            RequisitionItem::create([
                'requisition_id' => $requisition->id,
                'product_id' => $product->id,
                'quantity_requested' => $item['quantity'],
                'quantity_used' => 0,
            ]);

            // Descontar de bodega (movimiento de salida)
            if ($product->current_stock >= $item['quantity']) {
                \App\Models\Movement::create([
                    'product_id' => $product->id,
                    'type' => 'requisition_out',
                    'quantity' => $item['quantity'],
                    'description' => 'Requisición #' . $requisition->id,
                    'user_id' => Auth::id(),
                    'reference_type' => 'requisition',
                    'reference_id' => $requisition->id,
                ]);

                $product->decrement('current_stock', $item['quantity']);
            }

            // Sumar al inventario del técnico
            TechnicianInventory::updateOrCreate(
                [
                    'technician_id' => Auth::id(),
                    'product_id' => $product->id,
                ],
                [
                    'quantity_in_hand' => \DB::raw('quantity_in_hand + ' . $item['quantity']),
                ]
            );
        }

        $this->dispatch('show-toast', type: 'success', message: 'Requisición creada correctamente.');
        return redirect()->route('technician.requisitions.index');
    }

    public function render()
    {
        $workOrders = WorkOrder::where('technician_id', Auth::id())
            ->whereIn('status', ['pending', 'in_progress'])
            ->get();

        return view('livewire.technicians.requisition-form', [
            'workOrders' => $workOrders,
        ])->layout('components.layouts.app');
    }
}