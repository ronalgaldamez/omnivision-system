<?php

namespace App\Livewire\Technicians;

use Livewire\Component;
use App\Models\WorkOrder;
use App\Models\Product;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\TechnicianInventory;
use App\Services\InventoryService;
use Illuminate\Support\Facades\Auth;

class RequisitionForm extends Component
{
    public $selectedWorkOrders = [];
    public $search = '';
    public $productResults = [];
    public $currentProductId;
    public $currentProductSearch = '';
    public $currentQuantity = 1;
    public $showProductModal = false;
    public $productList = [];
    public $productListSearch = '';
    public $items = [];
    public $notes = '';
    public $confirmingSave = false;

    public $technicianStock = [];

    // Modal de vista previa con slider
    public $previewWorkOrder = null;
    public $previewWorkOrderId = null;
    public $previewIndex = 0;        // índice actual en la lista de OTs
    public $previewTotal = 0;        // total de OTs disponibles
    public $workOrderIds = [];       // lista ordenada de IDs para navegar

    protected function rules()
    {
        $otRequired = \App\Models\Setting::get('ot_required', 'false') === 'true';

        return [
            'selectedWorkOrders' => $otRequired ? 'required|array|min:1' : 'nullable|array',
            'selectedWorkOrders.*' => ['exists:work_orders,id', function ($attribute, $value, $fail) {
                if (\App\Models\WorkOrder::whereHas('requisitions', fn($q) => $q->where('status', 'pending'))
                    ->where('id', $value)
                    ->exists()
                ) {
                    $fail("La OT #{$value} ya pertenece a una requisición abierta.");
                }
            }],
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
        ];
    }

    public $inheritedItemIds = [];

    public function mount()
    {
        $this->loadTechnicianStock();
        $this->loadInheritedItems();
    }

    public function loadInheritedItems()
    {
        $inventory = TechnicianInventory::where('technician_id', Auth::id())
            ->where('quantity_in_hand', '>', 0)
            ->with('product')
            ->get();

        foreach ($inventory as $inv) {
            $exists = collect($this->items)->firstWhere('product_id', $inv->product_id);
            if (!$exists) {
                $this->items[] = [
                    'product_id' => $inv->product_id,
                    'product_name' => $inv->product->name,
                    'product_sku' => $inv->product->sku,
                    'quantity' => $inv->quantity_in_hand,
                    'unit' => $inv->product->unitLabel(),
                    'inherited' => true,
                ];
                $this->inheritedItemIds[] = $inv->product_id;
            }
        }
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
            $this->productResults = Product::with('category')
                ->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->currentProductSearch . '%')
                        ->orWhere('sku', 'like', '%' . $this->currentProductSearch . '%');
                })
                ->where(function ($q) {
                    $q->whereDoesntHave('category', fn($q2) => $q2->where('requires_device_registration', true))
                        ->orWhereHas('devices');
                })
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
            $this->showProductModal = false;
        }
    }

    public function openProductModal()
    {
        $this->productListSearch = '';
        $this->productList = $this->availableProducts()->take(50)->get();
        $this->showProductModal = true;
    }

    public function closeProductModal()
    {
        $this->showProductModal = false;
        $this->productListSearch = '';
        $this->productList = [];
    }

    public function updatedProductListSearch()
    {
        if (strlen($this->productListSearch) >= 2) {
            $this->productList = $this->availableProducts()
                ->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->productListSearch . '%')
                        ->orWhere('sku', 'like', '%' . $this->productListSearch . '%');
                })
                ->orderBy('name')->take(50)->get();
        } else {
            $this->productList = $this->availableProducts()->take(50)->get();
        }
    }

    private function availableProducts()
    {
        return Product::where(function ($q) {
            $q->whereDoesntHave('category', fn($q2) => $q2->where('requires_device_registration', true))
                ->orWhereHas('devices');
        });
    }

    public function addItem()
    {
        $product = Product::find($this->currentProductId);
        $isWhole = $product?->isWholeUnit() ?? true;

        $this->validate([
            'currentProductId' => 'required|exists:products,id',
            'currentQuantity' => $isWhole ? 'required|integer|min:1' : 'required|numeric|min:0.01',
        ], [
            'currentProductId.required' => 'Seleccioná un producto antes de agregar.',
            'currentProductId.exists' => 'El producto seleccionado no existe.',
            'currentQuantity.required' => 'Indicá la cantidad a solicitar.',
            'currentQuantity.integer' => 'Este producto se maneja en unidades enteras.',
            'currentQuantity.min' => 'La cantidad debe ser mayor a 0.',
        ]);

        $this->items[] = [
            'product_id' => $this->currentProductId,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'quantity' => $isWhole ? (int) $this->currentQuantity : $this->currentQuantity,
            'unit' => $product->unitLabel(),
            'is_whole' => $isWhole,
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

    // Abre el modal cargando todas las OTs y posicionando en la seleccionada
    public function openPreviewWorkOrder($id, $allIds = [])
    {
        $this->workOrderIds = array_values($allIds);
        $this->previewTotal = count($this->workOrderIds);
        $this->previewIndex = array_search($id, $this->workOrderIds) ?: 0;
        $this->loadPreview($id);
    }

    // Navega a la OT anterior
    public function previewPrev()
    {
        if ($this->previewIndex > 0) {
            $this->previewIndex--;
            $this->loadPreview($this->workOrderIds[$this->previewIndex]);
        }
    }

    // Navega a la OT siguiente
    public function previewNext()
    {
        if ($this->previewIndex < $this->previewTotal - 1) {
            $this->previewIndex++;
            $this->loadPreview($this->workOrderIds[$this->previewIndex]);
        }
    }

    private function loadPreview($id)
    {
        $this->previewWorkOrderId = $id;
        $this->previewWorkOrder = WorkOrder::with('client', 'ticket')->find($id);
    }

    public function closePreviewWorkOrder()
    {
        $this->previewWorkOrderId = null;
        $this->previewWorkOrder = null;
        $this->previewIndex = 0;
        $this->previewTotal = 0;
        $this->workOrderIds = [];
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
        \App\Models\Requisition::where('technician_id', Auth::id())
            ->where('status', 'approved')
            ->update(['status' => 'heredada']);

        $requisition = Requisition::create([
            'technician_id' => Auth::id(),
            'status' => 'pending',
            'week_start_date' => now()->startOfWeek(),
            'notes' => $this->notes,
        ]);

        $requisition->workOrders()->sync($this->selectedWorkOrders);

        foreach ($this->items as $item) {
            $product = Product::find($item['product_id']);
            $isInherited = $item['inherited'] ?? false;

            RequisitionItem::create([
                'requisition_id' => $requisition->id,
                'product_id' => $product->id,
                'quantity_requested' => $item['quantity'],
                'quantity_used' => 0,
                'is_inherited' => $isInherited,
            ]);

            // El stock heredado ya está en el inventario del técnico (quantity_in_hand).
            // NO se vuelve a sumar aquí para no duplicarlo; el despacho de bodega solo
            // agrega los ítems nuevos (is_inherited = false).
        }

        \App\Notifications\RequisitionSubmittedNotification::notifyWarehouse($requisition);

        $this->dispatch('show-toast', type: 'success', message: 'Requisición enviada a bodega para aprobación.');
        return redirect()->route('technician.requisitions.index');
    }

    public function render()
    {
        $workOrders = WorkOrder::where('technician_id', Auth::id())
            ->whereIn('status', ['pending', 'in_progress'])
            ->whereDoesntHave('requisitions', fn($q) => $q->where('status', 'pending'))
            ->with('client')
            ->get();

        return view('livewire.technicians.requisition-form', [
            'workOrders' => $workOrders,
        ])->layout('components.layouts.app');
    }
}