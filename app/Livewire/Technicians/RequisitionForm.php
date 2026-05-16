<?php

namespace App\Livewire\Technicians;

use Livewire\Component;
use App\Models\WorkOrder;
use App\Models\Product;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\TechnicianInventory;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class RequisitionForm extends Component
{
    public $selectedWorkOrders = [];
    public $productResults = [];
    public $currentProductId;
    public $currentProductSearch = '';
    public $currentQuantity = 1;
    public $items = [];
    public $notes = '';
    public $confirmingSave = false;
    public $technicianStock = [];
    public $otRequired = false;
    public $isSaving = false;
    public $groupedWorkOrders = [];

    // Propiedades para el modal slider de detalle de OT
    public $showWorkOrderModal = false;
    public $selectedWorkOrder = null; // Almacena el modelo WorkOrder
    public $selectedWorkOrderIndex = 0;
    public $allWorkOrdersFlat = []; // Lista plana de todas las OTs para el slider

    protected function rules()
    {
        $rules = [
            'selectedWorkOrders' => ['array'],
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
        ];

        if ($this->otRequired) {
            $rules['selectedWorkOrders'][] = 'required';
            $rules['selectedWorkOrders'][] = 'min:1';
        }

        return $rules;
    }

    public function mount($work_order_id = null)
    {
        $this->loadTechnicianStock();
        $this->otRequired = Setting::get('ot_required', 'false') === 'true';
        $this->loadWorkOrders();

        // Preseleccionar la OT si viene de la URL
        if ($work_order_id) {
            $this->selectedWorkOrders = [(int) $work_order_id];
        }
    }

    public function loadTechnicianStock()
    {
        $inventory = TechnicianInventory::where('technician_id', Auth::id())
            ->with('product')
            ->get();

        $this->technicianStock = $inventory->mapWithKeys(function ($item) {
            return [
                $item->product_id => [
                    'name' => $item->product->name,
                    'quantity' => $item->quantity_in_hand,
                ]
            ];
        })->toArray();
    }

    protected function loadWorkOrders()
    {
        $userId = Auth::id();
        $allOrders = WorkOrder::where('technician_id', $userId)
            ->whereIn('status', ['pending', 'in_progress'])
            ->with(['client.phones', 'requisitions' => fn($q) => $q->where('status', 'open')])
            ->orderBy('scheduled_date')
            ->get();

        // Guardar lista plana para el slider
        $this->allWorkOrdersFlat = $allOrders;

        // Agrupar por día de la semana
        $grouped = [];
        foreach ($allOrders as $wo) {
            $date = $wo->scheduled_date ?? $wo->created_at;
            $dayName = Carbon::parse($date)->locale('es')->dayName;
            $grouped[$dayName][] = [
                'id' => $wo->id,
                'name' => $wo->client->name ?? 'N/A',
                'blocked' => $wo->requisitions->isNotEmpty(),
                'service_type' => $wo->service_type,
                'description' => $wo->description,
            ];
        }
        $this->groupedWorkOrders = $grouped;
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

    // --- Métodos del modal slider ---
    public function viewWorkOrderDetail($index)
    {
        if (isset($this->allWorkOrdersFlat[$index])) {
            $this->selectedWorkOrder = $this->allWorkOrdersFlat[$index]->load('client.phones');
            $this->selectedWorkOrderIndex = $index;
            $this->showWorkOrderModal = true;
        }
    }

    public function nextWorkOrder()
    {
        $total = count($this->allWorkOrdersFlat);
        if ($total > 0) {
            $newIndex = ($this->selectedWorkOrderIndex + 1) % $total;
            $this->viewWorkOrderDetail($newIndex);
        }
    }

    public function previousWorkOrder()
    {
        $total = count($this->allWorkOrdersFlat);
        if ($total > 0) {
            $newIndex = ($this->selectedWorkOrderIndex - 1 + $total) % $total;
            $this->viewWorkOrderDetail($newIndex);
        }
    }

    public function closeWorkOrderModal()
    {
        $this->showWorkOrderModal = false;
        $this->selectedWorkOrder = null;
    }

    // --- Guardado ---
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
        if ($this->isSaving)
            return;
        $this->isSaving = true;

        $requisition = Requisition::create([
            'technician_id' => Auth::id(),
            'status' => 'open',
            'week_start_date' => now()->startOfWeek(),
            'notes' => $this->notes,
        ]);

        if (!empty($this->selectedWorkOrders)) {
            $requisition->workOrders()->sync($this->selectedWorkOrders);
        }

        foreach ($this->items as $item) {
            $product = Product::find($item['product_id']);

            RequisitionItem::create([
                'requisition_id' => $requisition->id,
                'product_id' => $product->id,
                'quantity_requested' => $item['quantity'],
                'quantity_used' => 0,
            ]);

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

            TechnicianInventory::updateOrCreate(
                ['technician_id' => Auth::id(), 'product_id' => $product->id],
                ['quantity_in_hand' => \DB::raw('quantity_in_hand + ' . $item['quantity'])]
            );
        }

        $this->dispatch('show-toast', type: 'success', message: 'Requisición creada correctamente.');
        return redirect()->route('technician.requisitions.index');
    }

    public function render()
    {
        return view('livewire.technicians.requisition-form')->layout('components.layouts.app');
    }
}