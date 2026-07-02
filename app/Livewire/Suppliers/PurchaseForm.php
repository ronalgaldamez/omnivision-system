<?php

namespace App\Livewire\Suppliers;

use App\Models\Movement;
use App\Models\Product;
use App\Models\ProductShelf;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Shelf;
use App\Models\Supplier;
use App\Services\InventoryService;
use App\Traits\ManagesProductPackaging;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class PurchaseForm extends Component
{
    use AuthorizesRequests;
    use ManagesProductPackaging;

    public $supplier_id;

    public $invoice_number;

    public $purchase_date;

    public $notes;

    public $items = [];

    public $includeIva = false;

    public $subtotal = 0;

    public $ivaAmount = 0;

    public $total = 0;

    public $supplierSearch = '';

    public $supplierResults = [];

    public $currentProductSearch = '';

    // Crear producto inline
    public $createMode = false;

    public $newProductName = '';

    public $currentQuantity = 1;

    public $currentUnitCost = 0;

    public $productSearchResults = [];

    // Stock thresholds (se guardan al registrar la compra)
    public $stockMin = null;

    public $stockMax = null;

    // Empaque fraccionado
    public $hasFractional = false;

    public $fractionalQuantity = 1;

    public $fractionalUnits = 1;

    public $editingIndex = null;

    public $showConfirmModal = false;

    public $modalAction = null;

    public $modalItemIndex = null;

    public $modalMessage = '';

    public $showShelfModal = false;

    public $purchasedId = null;

    public $shelfAssignments = [];

    public $allShelves = [];

    public $draftRestored = false;

    public bool $skipDraftSave = false;

    protected $rules = [
        'supplier_id' => 'required|exists:suppliers,id',
        'invoice_number' => 'required|string|unique:purchases,invoice_number',
        'purchase_date' => 'required|date',
        'notes' => 'nullable|string|max:65535',
        'items' => 'required|array|min:1',
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.quantity' => 'required|integer|min:1',
        'items.*.unit_cost' => 'required|numeric|gt:0',
    ];

    public function mount()
    {
        $this->initPackagingState();

        if ($draft = session()->get('purchase_form_draft')) {
            $this->supplier_id = $draft['supplier_id'] ?? '';
            $this->invoice_number = $draft['invoice_number'] ?? '';
            $this->purchase_date = $draft['purchase_date'] ?? date('Y-m-d');
            $this->notes = $draft['notes'] ?? '';
            $this->items = $draft['items'] ?? [];
            $this->includeIva = $draft['includeIva'] ?? false;
            $this->supplierSearch = $draft['supplierSearch'] ?? '';
            $this->currentProductId = $draft['currentProductId'] ?? '';
            $this->currentProductSearch = $draft['currentProductSearch'] ?? '';
            $this->currentPackagingId = $draft['currentPackagingId'] ?? '';
            $this->currentQuantity = $draft['currentQuantity'] ?? 1;
            $this->currentUnitCost = $draft['currentUnitCost'] ?? 0;
            $this->editingIndex = $draft['editingIndex'] ?? null;
            $this->newPackagingTypeId = (string) ($draft['newPackagingTypeId'] ?? '');
            $this->newPackagingQuantity = (int) ($draft['newPackagingQuantity'] ?? 1);
            $this->createMode = $draft['createMode'] ?? false;
            $this->newProductName = $draft['newProductName'] ?? '';
            $this->hasFractional = $draft['hasFractional'] ?? false;
            $this->fractionalQuantity = (int) ($draft['fractionalQuantity'] ?? 1);
            $this->fractionalUnits = (int) ($draft['fractionalUnits'] ?? 1);
            $this->stockMin = $draft['stockMin'] ?? null;
            $this->stockMax = $draft['stockMax'] ?? null;
            $this->draftRestored = true;

            // Si estaba editando, mantener el formulario; si no y hay items, limpiarlo
            if ($this->editingIndex === null && ! empty($this->items)) {
                $this->currentProductId = '';
                $this->currentProductSearch = '';
                $this->currentPackagingId = '';
                $this->currentQuantity = 1;
                $this->currentUnitCost = 0;
            }

            if ($this->currentProductId) {
                $this->loadPackagingsForProduct($this->currentProductId);
            }

            // Si hay supplier_id pero no supplierSearch, recuperar nombre
            if ($this->supplier_id && empty($this->supplierSearch)) {
                $supplier = Supplier::find($this->supplier_id);
                if ($supplier) {
                    $this->supplierSearch = $supplier->name.' (NIT: '.($supplier->nit ?? 'N/A').')';
                }
            }
        } else {
            $this->purchase_date = date('Y-m-d');
            $this->items = [];
        }
        $this->calculateTotals();

        if ($this->draftRestored) {
            $this->saveDraft();
        }
    }

    public function updated($property)
    {
        if (in_array($property, ['showConfirmModal', 'showShelfModal', 'showProductModal', 'draftRestored'])) {
            return;
        }

        $this->saveDraft();
    }

    public function dehydrate()
    {
        if (! $this->skipDraftSave) {
            $this->saveDraft();
        }
    }

    private function saveDraft(): void
    {
        session()->put('purchase_form_draft', [
            'supplier_id' => $this->supplier_id,
            'invoice_number' => $this->invoice_number,
            'purchase_date' => $this->purchase_date,
            'notes' => $this->notes,
            'items' => $this->items,
            'includeIva' => $this->includeIva,
            'supplierSearch' => $this->supplierSearch,
            'currentProductId' => $this->currentProductId,
            'currentProductSearch' => $this->currentProductSearch,
            'currentPackagingId' => $this->currentPackagingId,
            'currentQuantity' => $this->currentQuantity,
            'currentUnitCost' => $this->currentUnitCost,
            'editingIndex' => $this->editingIndex,
            'newPackagingTypeId' => $this->newPackagingTypeId,
            'newPackagingQuantity' => $this->newPackagingQuantity,
            'createMode' => $this->createMode,
            'newProductName' => $this->newProductName,
            'hasFractional' => $this->hasFractional,
            'fractionalQuantity' => $this->fractionalQuantity,
            'fractionalUnits' => $this->fractionalUnits,
            'stockMin' => $this->stockMin,
            'stockMax' => $this->stockMax,
        ]);
    }

    // Búsqueda de proveedor
    public function updatedSupplierSearch()
    {
        if (strlen($this->supplierSearch) >= 2) {
            $this->supplierResults = Supplier::where('name', 'like', '%'.$this->supplierSearch.'%')
                ->orWhere('nit', 'like', '%'.$this->supplierSearch.'%')
                ->orWhere('nrc', 'like', '%'.$this->supplierSearch.'%')
                ->limit(10)->get();
        } else {
            $this->supplierResults = [];
        }
    }

    public function selectSupplier($id)
    {
        $supplier = Supplier::find($id);
        if ($supplier) {
            $this->supplier_id = $supplier->id;
            $this->supplierSearch = $supplier->name.' (NIT: '.($supplier->nit ?? 'N/A').')';
            $this->supplierResults = [];
        }
    }

    // Búsqueda de producto
    public function updatedCurrentProductSearch()
    {
        if (strlen($this->currentProductSearch) >= 2) {
            $this->productSearchResults = Product::where('name', 'like', '%'.$this->currentProductSearch.'%')
                ->orWhere('sku', 'like', '%'.$this->currentProductSearch.'%')
                ->limit(10)->get();
        } else {
            $this->productSearchResults = [];
        }
    }

    public function selectProduct($id)
    {
        $product = Product::find($id);
        if ($product) {
            $this->currentProductId = $product->id;
            $this->currentProductSearch = $product->name.' ('.$product->sku.')';
            $this->loadPackagingsForProduct($product->id);
            $this->stockMin = $product->stock_min;
            $this->stockMax = $product->stock_max;
            $this->productSearchResults = [];
        }
    }

    public function addItem()
    {
        $rules = [
            'currentProductId' => 'required|exists:products,id',
            'currentQuantity' => 'required|integer|min:1',
            'currentUnitCost' => 'required|numeric|gt:0',
        ];

        $packaging = $this->selectedPackaging;

        if ($this->hasFractional && $packaging) {
            $rules['fractionalQuantity'] = 'required|integer|gt:0|lte:currentQuantity';
            $rules['fractionalUnits'] = 'required|integer|gt:0|lt:'.$packaging->quantity_in_base_unit;
        }

        try {
            $this->validate($rules);
        } catch (ValidationException $e) {
            $this->dispatch('show-toasts', errors: $e->validator->errors()->all());
            throw $e;
        }

        $product = Product::find($this->currentProductId);
        $baseQty = $packaging ? $this->currentQuantity * $packaging->quantity_in_base_unit : $this->currentQuantity;

        $fractionalQuantity = null;
        $fractionalUnits = null;

        if ($this->hasFractional && $packaging) {
            $baseQty = ($this->currentQuantity * $packaging->quantity_in_base_unit) - ($this->fractionalQuantity * $this->fractionalUnits);
            $fractionalQuantity = $this->fractionalQuantity;
            $fractionalUnits = $this->fractionalUnits;
        }

        $item = [
            'product_id' => $this->currentProductId,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'quantity' => $this->currentQuantity,
            'unit_cost' => $this->currentUnitCost,
            'packaging_id' => $packaging?->id,
            'packaging_name' => $packaging?->name,
            'packaging_units' => $packaging?->quantity_in_base_unit,
            'base_quantity' => $baseQty,
            'fractional_quantity' => $fractionalQuantity,
            'fractional_units' => $fractionalUnits,
            'stock_min' => $this->stockMin,
            'stock_max' => $this->stockMax,
        ];

        if ($this->editingIndex !== null && array_key_exists($this->editingIndex, $this->items)) {
            $this->items[$this->editingIndex] = $item;
        } else {
            $this->items[] = $item;
        }
        $this->editingIndex = null;

        $this->resetCurrentProduct();
        $this->calculateTotals();
    }

    public function resetCurrentProduct()
    {
        $this->currentProductSearch = '';
        $this->currentProductId = '';
        $this->currentPackagingId = '';
        $this->currentPackagings = [];
        $this->currentQuantity = 1;
        $this->currentUnitCost = 0;
        $this->productSearchResults = [];
        $this->hasFractional = false;
        $this->fractionalQuantity = 1;
        $this->fractionalUnits = 1;
        $this->stockMin = null;
        $this->stockMax = null;
    }

    public function clearProductSelection()
    {
        $this->resetCurrentProduct();
        $this->editingIndex = null;
    }

    public function editItem($index)
    {
        $item = $this->items[$index];
        $this->currentProductId = $item['product_id'];
        $this->currentProductSearch = $item['product_name'].' ('.$item['product_sku'].')';
        $this->currentQuantity = $item['quantity'];
        $this->currentUnitCost = $item['unit_cost'];
        $this->editingIndex = $index;
        $this->hasFractional = ! empty($item['fractional_quantity']);
        $this->fractionalQuantity = (int) ($item['fractional_quantity'] ?? 1);
        $this->fractionalUnits = (int) ($item['fractional_units'] ?? 1);
        $this->stockMin = $item['stock_min'] ?? null;
        $this->stockMax = $item['stock_max'] ?? null;
        // Quitar de la tabla mientras se edita
        unset($this->items[$index]);
        $this->items = array_values($this->items);

        // Cargar empaques del producto y restaurar el empaque seleccionado
        $this->loadPackagingsForProduct($item['product_id']);
        $this->currentPackagingId = $item['packaging_id'] ?? ($this->currentPackagings->first()?->id ?? '');

        $this->calculateTotals();
    }

    public function cancelEdit()
    {
        if ($this->editingIndex === null) {
            return;
        }

        $product = Product::find($this->currentProductId);
        $packaging = $this->selectedPackaging;
        $baseQty = $packaging ? $this->currentQuantity * $packaging->quantity_in_base_unit : $this->currentQuantity;

        $fractionalQuantity = null;
        $fractionalUnits = null;
        if ($this->hasFractional && $packaging) {
            $baseQty = ($this->currentQuantity * $packaging->quantity_in_base_unit) - ($this->fractionalQuantity * $this->fractionalUnits);
            $fractionalQuantity = $this->fractionalQuantity;
            $fractionalUnits = $this->fractionalUnits;
        }

        // Restaurar el item a la tabla
        $this->items[] = [
            'product_id' => $this->currentProductId,
            'product_name' => $product->name ?? '',
            'product_sku' => $product->sku ?? '',
            'quantity' => $this->currentQuantity,
            'unit_cost' => $this->currentUnitCost,
            'packaging_id' => $this->currentPackagingId ?: null,
            'packaging_name' => $this->selectedPackaging?->name,
            'packaging_units' => $packaging?->quantity_in_base_unit,
            'base_quantity' => $baseQty,
            'fractional_quantity' => $fractionalQuantity,
            'fractional_units' => $fractionalUnits,
            'stock_min' => $this->stockMin,
            'stock_max' => $this->stockMax,
        ];
        $this->editingIndex = null;
        $this->resetCurrentProduct();
        $this->calculateTotals();
    }

    public function confirmAction($action, $index = null)
    {
        $this->modalAction = $action;
        $this->modalItemIndex = $index;

        if ($action === 'edit') {
            $this->modalMessage = '¿Editar este producto?';
        } elseif ($action === 'delete') {
            $this->modalMessage = '¿Eliminar este producto de la lista?';
        } elseif ($action === 'reset_form') {
            $this->modalMessage = '¿Limpiar todo el formulario? Se perderán todos los datos ingresados.';
        }
        $this->showConfirmModal = true;
    }

    public function executeAction()
    {
        if ($this->modalAction === 'delete') {
            $this->removeItem($this->modalItemIndex);
            $this->dispatch('show-toast', type: 'success', message: 'Producto eliminado.');
        } elseif ($this->modalAction === 'edit') {
            $this->editItem($this->modalItemIndex);
            $this->dispatch('show-toast', type: 'info', message: 'Producto cargado para edición.');
        } elseif ($this->modalAction === 'new_product') {
            $this->resetCurrentProduct();
            $this->editingIndex = null;
            $this->createMode = true;
        } elseif ($this->modalAction === 'reset_form') {
            $this->resetForm();
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

    // Producto nuevo inline
    public function activateCreateMode()
    {
        if ($this->currentProductId) {
            $this->modalAction = 'new_product';
            $this->modalMessage = '¿Descartar el producto actual y registrar uno nuevo? Los datos del producto seleccionado se perderán.';
            $this->showConfirmModal = true;

            return;
        }
        $this->createMode = true;
        $this->currentProductId = '';
        $this->currentProductSearch = '';
        $this->productSearchResults = [];
    }

    public function cancelCreateMode()
    {
        $this->createMode = false;
        $this->newProductName = '';
        $this->productSearchResults = [];
    }

    public function resetForm()
    {
        $this->supplier_id = '';
        $this->invoice_number = '';
        $this->purchase_date = date('Y-m-d');
        $this->notes = '';
        $this->items = [];
        $this->includeIva = false;
        $this->supplierSearch = '';
        $this->supplierResults = [];
        $this->createMode = false;
        $this->newProductName = '';
        $this->resetCurrentProduct();
        $this->editingIndex = null;
        $this->newPackagingTypeId = '';
        $this->newPackagingQuantity = 1;
        session()->forget('purchase_form_draft');
        $this->calculateTotals();
        $this->dispatch('show-toast', type: 'info', message: 'Formulario limpiado.');
    }

    public function createProduct()
    {
        $this->validate(['newProductName' => 'required|string|max:255']);

        $product = Product::create([
            'name' => $this->newProductName,
            'sku' => 'PROD-'.str_pad(Product::max('id') + 1, 5, '0', STR_PAD_LEFT),
            'current_stock' => 0,
            'stock_min' => 0,
        ]);

        $this->currentProductId = $product->id;
        $this->currentProductSearch = $product->name.' ('.$product->sku.')';
        $this->currentPackagings = collect();
        $this->currentPackagingId = '';
        $this->productSearchResults = [];
        $this->createMode = false;
        $this->newProductName = '';
        $this->saveDraft();
        $this->dispatch('show-toast', type: 'success', message: 'Producto creado. Ahora definí su empaque.');
    }

    // Totales e IVA
    public function updatedIncludeIva()
    {
        $this->calculateTotals();
    }

    public function calculateTotals()
    {
        $this->subtotal = array_sum(array_map(fn ($i) => ($i['base_quantity'] ?? $i['quantity']) * $i['unit_cost'], $this->items));
        $this->ivaAmount = $this->includeIva ? round($this->subtotal * 0.13, 2) : 0;
        $this->total = $this->subtotal + $this->ivaAmount;
    }

    // Guardado
    public function save()
    {
        if ($this->editingIndex !== null && $this->currentProductId) {
            $this->dispatch('show-toast', type: 'warning', message: 'Estás editando un producto. Cancelá la edición o guardá los cambios del producto antes de continuar.');

            return;
        }

        try {
            $this->validate();
        } catch (ValidationException $e) {
            $this->dispatch('show-toasts', errors: $e->validator->errors()->all());
            throw $e;
        }
        $this->dispatch('confirm-save');
    }

    public function confirmSave()
    {
        $this->validate();

        // Recalcular totales server-side para evitar manipulación
        $subtotal = array_sum(array_map(fn ($i) => ($i['base_quantity'] ?? $i['quantity']) * $i['unit_cost'], $this->items));
        $ivaAmount = $this->includeIva ? round($subtotal * 0.13, 2) : 0;
        $total = $subtotal + $ivaAmount;

        // Buscar productos en una sola query (anti N+1)
        $productIds = collect($this->items)->pluck('product_id')->unique();
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        DB::beginTransaction();
        try {
            $purchase = Purchase::create([
                'supplier_id' => $this->supplier_id,
                'invoice_number' => $this->invoice_number,
                'purchase_date' => $this->purchase_date,
                'notes' => $this->notes,
                'user_id' => Auth::id(),
                'subtotal' => $subtotal,
                'iva_amount' => $ivaAmount,
                'total' => $total,
                'include_iva' => $this->includeIva,
            ]);

            $inventoryService = new InventoryService;

            foreach ($this->items as $item) {
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'packaging_id' => $item['packaging_id'] ?? null,
                    'base_quantity' => $item['base_quantity'] ?? $item['quantity'],
                    'fractional_quantity' => $item['fractional_quantity'] ?? null,
                    'fractional_units' => $item['fractional_units'] ?? null,
                ]);

                $movement = Movement::create([
                    'product_id' => $item['product_id'],
                    'type' => 'entry',
                    'quantity' => $item['base_quantity'] ?? $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'description' => 'Compra No. '.$this->invoice_number,
                    'user_id' => Auth::id(),
                    'reference_type' => 'purchase',
                    'reference_id' => $purchase->id,
                ]);

                $product = $products[$item['product_id']] ?? Product::find($item['product_id']);
                $baseQty = $item['base_quantity'] ?? $item['quantity'];
                $totalItemValue = $item['quantity'] * $item['unit_cost'];
                $baseUnitCost = $totalItemValue / max(1, $baseQty);
                $inventoryService->processPurchaseEntry($product, $baseQty, $baseUnitCost, $movement);

                $needsSave = false;
                if (! is_null($item['stock_min'] ?? null) && $item['stock_min'] != $product->stock_min) {
                    $product->stock_min = (int) $item['stock_min'];
                    $needsSave = true;
                }
                if (! is_null($item['stock_max'] ?? null) && $item['stock_max'] != $product->stock_max) {
                    $product->stock_max = (int) $item['stock_max'];
                    $needsSave = true;
                }
                if ($needsSave) {
                    $product->save();
                }
            }

            DB::commit();
            $this->skipDraftSave = true;
            session()->forget('purchase_form_draft');
            $this->dispatch('show-toast', type: 'success', message: 'Compra registrada exitosamente.');

            $this->purchasedId = $purchase->id;
            $this->loadShelfAssignments();
            $this->loadAllShelves();
            $this->showShelfModal = true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al registrar compra: '.$e->getMessage(), ['exception' => $e]);
            $this->dispatch('show-toast', type: 'error', message: 'Ocurrió un error al registrar la compra. Intente nuevamente.');
        }
    }

    // Estanterías
    private function loadShelfAssignments()
    {
        $productIds = collect($this->items)->pluck('product_id');
        $allProductShelves = ProductShelf::whereIn('product_id', $productIds)
            ->with('shelf')->get()->groupBy('product_id');

        $this->shelfAssignments = [];
        foreach ($this->items as $item) {
            $existing = $allProductShelves->get($item['product_id'], collect());
            $this->shelfAssignments[] = [
                'product_id' => $item['product_id'],
                'product_name' => $item['product_name'],
                'product_sku' => $item['product_sku'],
                'quantity' => $item['quantity'],
                'shelf_id' => $existing->first()?->shelf_id ?? '',
                'current_shelves' => $existing->map(fn ($ps) => $ps->shelf->code.' ('.$ps->quantity.')')->implode(', '),
            ];
        }
    }

    private function loadAllShelves()
    {
        $this->allShelves = [];
        $roots = Shelf::with('children')
            ->whereNull('parent_id')->where('is_active', true)
            ->orderBy('code')->get();
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
            'id' => $shelf->id, 'code' => $shelf->code, 'label' => $shelf->label,
            'display' => $indent.$branch.$shelf->code.' — '.$shelf->label.$fullLabel,
            'is_full' => $shelf->is_full, 'is_root' => $depth === 0,
        ];
        foreach ($shelf->children as $child) {
            $this->flattenShelfTree($child, $depth + 1);
        }
    }

    public function saveShelfAssignments()
    {
        $assignedIds = collect($this->shelfAssignments)->pluck('shelf_id')->filter();
        if ($assignedIds->isEmpty()) {
            $this->dispatch('show-toast', type: 'error', message: 'Seleccioná al menos una estantería.');

            return;
        }
        $fullShelves = Shelf::whereIn('id', $assignedIds)->where('is_full', true)->pluck('code')->toArray();
        if (! empty($fullShelves)) {
            $this->dispatch('show-toast', type: 'error', message: 'Ubicaciones llenas: '.implode(', ', $fullShelves));

            return;
        }
        foreach ($this->shelfAssignments as $assign) {
            if (! empty($assign['shelf_id'])) {
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
