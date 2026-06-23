<?php

namespace App\Livewire\Suppliers;

use Livewire\Component;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Movement;
use App\Models\Shelf;
use App\Models\ProductShelf;
use App\Services\InventoryService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseForm extends Component
{
    public $supplier_id;
    public $invoice_number;
    public $purchase_date;
    public $notes;
    public $items = [];
    public $includeIva = false;
    public $subtotal = 0;
    public $ivaAmount = 0;
    public $total = 0;

    // Campos para búsqueda de proveedor
    public $supplierSearch = '';
    public $supplierResults = [];

    // Campos para producto actual (búsqueda)
    public $currentProductSearch = '';
    public $currentProductId = '';
    public $currentQuantity = 1;
    public $currentUnitCost = 0;
    public $productSearchResults = [];
    public $editingIndex = null;

    // Modal de confirmación (editar/eliminar item)
    public $showConfirmModal = false;
    public $modalAction = null;
    public $modalItemIndex = null;
    public $modalMessage = '';

    // Modal para crear producto rápidamente
    public $showProductModal = false;

    // Asignación a estanterías post-guardado
    public $showShelfModal = false;
    public $purchasedId = null;
    public $shelfAssignments = [];
    public $allShelves = [];

    protected $rules = [
        'supplier_id' => 'required|exists:suppliers,id',
        'invoice_number' => 'required|string|unique:purchases,invoice_number',
        'purchase_date' => 'required|date',
        'notes' => 'nullable|string',
        'items' => 'required|array|min:1',
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.quantity' => 'required|numeric|min:0.01',
        'items.*.unit_cost' => 'required|numeric|min:0',
    ];

    protected function getListeners()
    {
        return [
            'productCreated' => 'handleProductCreated',
        ];
    }

    public function mount()
    {
        $this->purchase_date = date('Y-m-d');
        $this->items = [];
        $this->calculateTotals();
    }

    // ==================== Proveedor ====================
    public function updatedSupplierSearch()
    {
        if (strlen($this->supplierSearch) >= 2) {
            $this->supplierResults = Supplier::where('name', 'like', '%' . $this->supplierSearch . '%')
                ->orWhere('nit', 'like', '%' . $this->supplierSearch . '%')
                ->orWhere('nrc', 'like', '%' . $this->supplierSearch . '%')
                ->limit(10)
                ->get();
        } else {
            $this->supplierResults = [];
        }
    }

    public function selectSupplier($id)
    {
        $supplier = Supplier::find($id);
        if ($supplier) {
            $this->supplier_id = $supplier->id;
            $this->supplierSearch = $supplier->name . ' (NIT: ' . ($supplier->nit ?? 'N/A') . ')';
            $this->supplierResults = [];
        }
    }

    // ==================== Producto ====================
    public function updatedCurrentProductSearch()
    {
        if (strlen($this->currentProductSearch) >= 2) {
            $this->productSearchResults = Product::where('name', 'like', '%' . $this->currentProductSearch . '%')
                ->orWhere('sku', 'like', '%' . $this->currentProductSearch . '%')
                ->limit(10)
                ->get();
        } else {
            $this->productSearchResults = [];
        }
    }

    public function selectProduct($id)
    {
        $product = Product::find($id);
        if ($product) {
            $this->currentProductId = $product->id;
            $this->currentProductSearch = $product->name . ' (' . $product->sku . ')';
            $this->productSearchResults = [];
        }
    }

    public function addItem()
    {
        $this->validate([
            'currentProductId' => 'required|exists:products,id',
            'currentQuantity' => 'required|numeric|min:0.01',
            'currentUnitCost' => 'required|numeric|min:0',
        ]);

        $product = Product::find($this->currentProductId);

        if ($this->editingIndex !== null) {
            $this->items[$this->editingIndex] = [
                'product_id' => $this->currentProductId,
                'product_name' => $product->name,
                'product_sku' => $product->sku,
                'quantity' => $this->currentQuantity,
                'unit_cost' => $this->currentUnitCost,
            ];
            $this->editingIndex = null;
        } else {
            $this->items[] = [
                'product_id' => $this->currentProductId,
                'product_name' => $product->name,
                'product_sku' => $product->sku,
                'quantity' => $this->currentQuantity,
                'unit_cost' => $this->currentUnitCost,
            ];
        }

        $this->resetCurrentProduct();
        $this->calculateTotals();
    }

    public function resetCurrentProduct()
    {
        $this->currentProductSearch = '';
        $this->currentProductId = '';
        $this->currentQuantity = 1;
        $this->currentUnitCost = 0;
        $this->productSearchResults = [];
    }

    public function editItem($index)
    {
        $item = $this->items[$index];
        $this->currentProductId = $item['product_id'];
        $this->currentProductSearch = $item['product_name'] . ' (' . $item['product_sku'] . ')';
        $this->currentQuantity = $item['quantity'];
        $this->currentUnitCost = $item['unit_cost'];
        $this->editingIndex = $index;
    }

    public function confirmAction($action, $index)
    {
        $this->modalAction = $action;
        $this->modalItemIndex = $index;
        $this->modalMessage = $action === 'edit'
            ? '¿Editar este producto? Los datos se cargarán en el formulario para modificarlos.'
            : '¿Eliminar este producto de la lista?';
        $this->showConfirmModal = true;
    }

    public function executeAction()
    {
        if ($this->modalAction === 'delete') {
            $this->removeItem($this->modalItemIndex);
            $this->dispatch('show-toast', type: 'success', message: 'Producto eliminado de la lista.');
        } elseif ($this->modalAction === 'edit') {
            $this->editItem($this->modalItemIndex);
            $this->dispatch('show-toast', type: 'info', message: 'Producto cargado para edición.');
        }
        $this->closeModal();
    }

    public function closeModal()
    {
        $this->showConfirmModal = false;
        $this->modalAction = null;
        $this->modalItemIndex = null;
        $this->modalMessage = '';
    }

    public function removeItem($index, $showMessage = true)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        $this->calculateTotals();
    }

    // ==================== Modal de producto nuevo ====================
    public function openProductModal()
    {
        $this->showProductModal = true;
    }

    public function closeProductModal()
    {
        $this->showProductModal = false;
    }

    public function handleProductCreated($productId, $productName)
    {
        $this->dispatch('show-toast', type: 'success', message: 'Producto creado exitosamente.');
        $this->closeProductModal();
    }

    // ==================== Totales e IVA ====================
    public function updatedIncludeIva()
    {
        $this->calculateTotals();
    }

    public function calculateTotals()
    {
        $this->subtotal = array_sum(array_map(fn($i) => $i['quantity'] * $i['unit_cost'], $this->items));
        if ($this->includeIva) {
            $this->ivaAmount = round($this->subtotal * 0.13, 2);
            $this->total = $this->subtotal + $this->ivaAmount;
        } else {
            $this->ivaAmount = 0;
            $this->total = $this->subtotal;
        }
    }

    // ==================== Guardado ====================
    public function save()
    {
        $this->validate();
        $this->dispatch('confirm-save');
    }

    public function confirmSave()
    {
        $this->validate();

        DB::beginTransaction();
        try {
            $purchase = Purchase::create([
                'supplier_id' => $this->supplier_id,
                'invoice_number' => $this->invoice_number,
                'purchase_date' => $this->purchase_date,
                'notes' => $this->notes,
                'user_id' => Auth::id(),
                'subtotal' => $this->subtotal,
                'iva_amount' => $this->ivaAmount,
                'total' => $this->total,
            ]);

            $inventoryService = new InventoryService();

            foreach ($this->items as $item) {
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                ]);

                $product = Product::find($item['product_id']);

                $movement = Movement::create([
                    'product_id' => $item['product_id'],
                    'type' => 'entry',
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'description' => 'Compra No. ' . $this->invoice_number,
                    'user_id' => Auth::id(),
                    'reference_type' => 'purchase',
                    'reference_id' => $purchase->id,
                ]);

                $inventoryService->processPurchaseEntry($product, $item['quantity'], $item['unit_cost'], $movement);
            }

            DB::commit();
            $this->dispatch('show-toast', type: 'success', message: 'Compra registrada exitosamente.');

            // Mostrar modal de asignación a estanterías
            $this->purchasedId = $purchase->id;
            $this->loadShelfAssignments();
            $this->loadAllShelves();
            $this->showShelfModal = true;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('show-toast', type: 'error', message: 'Error: ' . $e->getMessage());
        }
    }

    // ==================== Estanterías ====================

    private function loadShelfAssignments()
    {
        $this->shelfAssignments = [];
        foreach ($this->items as $item) {
            $existing = ProductShelf::where('product_id', $item['product_id'])
                ->with('shelf')
                ->get();

            $this->shelfAssignments[] = [
                'product_id' => $item['product_id'],
                'product_name' => $item['product_name'],
                'product_sku' => $item['product_sku'],
                'quantity' => $item['quantity'],
                'shelf_id' => $existing->first()?->shelf_id ?? '',
                'current_shelves' => $existing->map(fn($ps) => $ps->shelf->code . ' (' . $ps->quantity . ')')->implode(', '),
            ];
        }
    }

    private function loadAllShelves()
    {
        $this->allShelves = [];
        $roots = Shelf::with('children')
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('code')
            ->get();
        foreach ($roots as $root) {
            $this->flattenShelfTree($root, 0);
        }
    }

    private function flattenShelfTree($shelf, $depth)
    {
        $indent = str_repeat('  ', $depth);
        $branch = $depth === 0 ? '' : ($depth === 1 ? '└─ ' : '  └─ ');
        $fullLabel = $shelf->is_full ? ' 🔴 LLENO' : '';
        $this->allShelves[] = [
            'id' => $shelf->id,
            'code' => $shelf->code,
            'label' => $shelf->label,
            'display' => $indent . $branch . $shelf->code . ' — ' . $shelf->label . $fullLabel,
            'is_full' => $shelf->is_full,
            'is_root' => $depth === 0,
        ];
        foreach ($shelf->children as $child) {
            $this->flattenShelfTree($child, $depth + 1);
        }
    }

    public function saveShelfAssignments()
    {
        $assignedIds = collect($this->shelfAssignments)->pluck('shelf_id')->filter();

        if ($assignedIds->isEmpty()) {
            $this->dispatch('show-toast', type: 'error', message: 'Seleccioná al menos una estantería para guardar la asignación.');
            return;
        }

        $fullShelves = Shelf::whereIn('id', $assignedIds)
            ->where('is_full', true)
            ->pluck('code')
            ->toArray();

        if (!empty($fullShelves)) {
            $this->dispatch('show-toast', type: 'error', message: 'No se puede asignar: estas ubicaciones están llenas: ' . implode(', ', $fullShelves));
            return;
        }

        foreach ($this->shelfAssignments as $assign) {
            if (!empty($assign['shelf_id'])) {
                $record = ProductShelf::firstOrNew([
                    'product_id' => $assign['product_id'],
                    'shelf_id' => $assign['shelf_id'],
                ]);
                $record->quantity += intval($assign['quantity']);
                $record->save();
            }
        }

        $this->showShelfModal = false;
        $this->dispatch('show-toast', type: 'success', message: 'Productos asignados a estanterías.');
        $this->redirectRoute('purchases.index');
    }

    public function skipShelfAssignment()
    {
        $this->showShelfModal = false;
        $this->redirectRoute('purchases.index');
    }

    public function render()
    {
        return view('livewire.suppliers.purchase-form')->layout('components.layouts.app');
    }
}