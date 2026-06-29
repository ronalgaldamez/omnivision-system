<?php

namespace App\Livewire\Suppliers;

use Livewire\Component;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\ProductPackaging;
use App\Models\PackagingType;
use App\Models\Movement;
use App\Models\Shelf;
use App\Models\ProductShelf;
use App\Services\InventoryService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PurchaseForm extends Component
{
    use AuthorizesRequests;

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
    public $currentProductId = '';
    public $currentPackagingId = '';
    public $currentPackagings = [];
    public $newPackagingTypeId = '';
    public $newPackagingQuantity = 1;
    public $packagingTypes = [];

    // Crear producto inline
    public $createMode = false;
    public $newProductName = '';
    public $currentQuantity = 1;
    public $currentUnitCost = 0;
    public $productSearchResults = [];
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

    protected $rules = [
        'supplier_id' => 'required|exists:suppliers,id',
        'invoice_number' => 'required|string|unique:purchases,invoice_number',
        'purchase_date' => 'required|date',
        'notes' => 'nullable|string|max:65535',
        'items' => 'required|array|min:1',
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.quantity' => 'required|integer|min:1',
        'items.*.unit_cost' => 'required|numeric|min:0',
    ];

    public function mount()
    {
        $this->packagingTypes = PackagingType::orderBy('name')->get();

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
            $this->draftRestored = true;

            // Si estaba editando, mantener el formulario; si no y hay items, limpiarlo
            if ($this->editingIndex === null && !empty($this->items)) {
                $this->currentProductId = '';
                $this->currentProductSearch = '';
                $this->currentPackagingId = '';
                $this->currentQuantity = 1;
                $this->currentUnitCost = 0;
            }
            
            if ($this->currentProductId) {
                $product = Product::with('packagings')->find($this->currentProductId);
                if ($product) {
                    $this->currentPackagings = $product->packagings;
                }
            }

            // Si hay supplier_id pero no supplierSearch, recuperar nombre
            if ($this->supplier_id && empty($this->supplierSearch)) {
                $supplier = Supplier::find($this->supplier_id);
                if ($supplier) {
                    $this->supplierSearch = $supplier->name . ' (NIT: ' . ($supplier->nit ?? 'N/A') . ')';
                }
            }
        } else {
            $this->purchase_date = date('Y-m-d');
            $this->items = [];
        }
        $this->calculateTotals();
    }

    public function updated($property)
    {
        if (in_array($property, ['showConfirmModal', 'showShelfModal', 'showProductModal', 'draftRestored'])) return;

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
        ]);
    }

    // Búsqueda de proveedor
    public function updatedSupplierSearch()
    {
        if (strlen($this->supplierSearch) >= 2) {
            $this->supplierResults = Supplier::where('name', 'like', '%' . $this->supplierSearch . '%')
                ->orWhere('nit', 'like', '%' . $this->supplierSearch . '%')
                ->orWhere('nrc', 'like', '%' . $this->supplierSearch . '%')
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
            $this->supplierSearch = $supplier->name . ' (NIT: ' . ($supplier->nit ?? 'N/A') . ')';
            $this->supplierResults = [];
        }
    }

    // Búsqueda de producto
    public function updatedCurrentProductSearch()
    {
        if (strlen($this->currentProductSearch) >= 2) {
            $this->productSearchResults = Product::where('name', 'like', '%' . $this->currentProductSearch . '%')
                ->orWhere('sku', 'like', '%' . $this->currentProductSearch . '%')
                ->limit(10)->get();
        } else {
            $this->productSearchResults = [];
        }
    }

    public function selectProduct($id)
    {
        $product = Product::with('packagings')->find($id);
        if ($product) {
            $this->currentProductId = $product->id;
            $this->currentProductSearch = $product->name . ' (' . $product->sku . ')';
            $this->currentPackagings = $product->packagings;
            // Auto-seleccionar el empaque por defecto o el primero
            $default = $product->packagings->firstWhere('is_default_for_purchase', true);
            $this->currentPackagingId = $default ? $default->id : ($product->packagings->first()?->id ?? '');
            $this->productSearchResults = [];
        }
    }

    public function updatedCurrentPackagingId()
    {
        // Recalcular cuando cambia el empaque
    }

    public function getSelectedPackagingProperty()
    {
        if (!$this->currentPackagingId) return null;
        return collect($this->currentPackagings)->firstWhere('id', $this->currentPackagingId);
    }

    public function savePackaging()
    {
        $this->validate([
            'newPackagingTypeId' => 'required|exists:packaging_types,id',
            'newPackagingQuantity' => 'required|numeric|min:1',
        ]);

        $type = PackagingType::find($this->newPackagingTypeId);
        $name = $type->name . ' x' . rtrim(rtrim(number_format($this->newPackagingQuantity, 4), '0'), '.');

        ProductPackaging::create([
            'product_id' => $this->currentProductId,
            'packaging_type_id' => $type->id,
            'name' => $name,
            'quantity_in_base_unit' => $this->newPackagingQuantity,
            'is_default_for_purchase' => true,
        ]);

        $product = Product::find($this->currentProductId);
        $product->refresh();
        $this->currentPackagings = $product->packagings;
        $pkg = $product->packagings->last();
        $this->currentPackagingId = $pkg ? $pkg->id : '';
        $this->newPackagingTypeId = '';
        $this->newPackagingQuantity = 1;
        $this->dispatch('show-toast', type: 'success', message: 'Empaque creado.');
    }

    public function addItem()
    {
        $this->validate([
            'currentProductId' => 'required|exists:products,id',
            'currentQuantity' => 'required|integer|min:1',
            'currentUnitCost' => 'required|numeric|min:0',
        ]);

        $product = Product::find($this->currentProductId);
        $packaging = $this->selectedPackaging;
        $baseQty = $packaging ? $this->currentQuantity * $packaging->quantity_in_base_unit : $this->currentQuantity;

        $item = [
            'product_id' => $this->currentProductId,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'quantity' => $this->currentQuantity,
            'unit_cost' => $this->currentUnitCost,
            'packaging_id' => $packaging?->id,
            'packaging_name' => $packaging?->name,
            'base_quantity' => $baseQty,
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
    }

    public function editItem($index)
    {
        $item = $this->items[$index];
        $this->currentProductId = $item['product_id'];
        $this->currentProductSearch = $item['product_name'] . ' (' . $item['product_sku'] . ')';
        $this->currentQuantity = $item['quantity'];
        $this->currentUnitCost = $item['unit_cost'];
        $this->editingIndex = $index;
        // Quitar de la tabla mientras se edita
        unset($this->items[$index]);
        $this->items = array_values($this->items);

        // Cargar empaques del producto y restaurar el empaque seleccionado
        $product = Product::with('packagings')->find($item['product_id']);
        if ($product) {
            $this->currentPackagings = $product->packagings;
            $this->currentPackagingId = $item['packaging_id'] ?? ($product->packagings->first()?->id ?? '');
        }

        $this->calculateTotals();
    }

    public function cancelEdit()
    {
        if ($this->editingIndex === null) return;

        $product = Product::find($this->currentProductId);
        // Restaurar el item a la tabla
        $this->items[] = [
            'product_id' => $this->currentProductId,
            'product_name' => $product->name ?? '',
            'product_sku' => $product->sku ?? '',
            'quantity' => $this->currentQuantity,
            'unit_cost' => $this->currentUnitCost,
            'packaging_id' => $this->currentPackagingId ?: null,
            'packaging_name' => $this->selectedPackaging?->name,
            'base_quantity' => $this->selectedPackaging ? $this->currentQuantity * $this->selectedPackaging->quantity_in_base_unit : $this->currentQuantity,
        ];
        $this->editingIndex = null;
        $this->resetCurrentProduct();
        $this->calculateTotals();
    }

    public function confirmAction($action, $index)
    {
        $this->modalAction = $action;
        $this->modalItemIndex = $index;
        $this->modalMessage = $action === 'edit'
            ? '¿Editar este producto?'
            : '¿Eliminar este producto de la lista?';
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
        $this->createMode = true;
        $this->currentProductId = '';
        $this->currentProductSearch = '';
    }

    public function cancelCreateMode()
    {
        $this->createMode = false;
        $this->newProductName = '';
    }

    public function createProduct()
    {
        $this->validate(['newProductName' => 'required|string|max:255']);

        $product = Product::create([
            'name' => $this->newProductName,
            'sku' => 'PROD-' . str_pad(Product::max('id') + 1, 5, '0', STR_PAD_LEFT),
            'current_stock' => 0,
            'stock_min' => 0,
        ]);

        $this->currentProductId = $product->id;
        $this->currentProductSearch = $product->name . ' (' . $product->sku . ')';
        $this->currentPackagings = collect();
        $this->currentPackagingId = '';
        $this->createMode = false;
        $this->newProductName = '';
        $this->dispatch('show-toast', type: 'success', message: 'Producto creado. Ahora definí su empaque.');
    }

    // Totales e IVA
    public function updatedIncludeIva() { $this->calculateTotals(); }

    public function calculateTotals()
    {
        $this->subtotal = array_sum(array_map(fn($i) => $i['quantity'] * $i['unit_cost'], $this->items));
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
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('show-toasts', errors: $e->validator->errors()->all());
            throw $e;
        }
        $this->dispatch('confirm-save');
    }

    public function confirmSave()
    {
        $this->validate();

        // Recalcular totales server-side para evitar manipulación
        $subtotal = array_sum(array_map(fn($i) => $i['quantity'] * $i['unit_cost'], $this->items));
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

            $inventoryService = new InventoryService();

            foreach ($this->items as $item) {
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'packaging_id' => $item['packaging_id'] ?? null,
                    'base_quantity' => $item['base_quantity'] ?? $item['quantity'],
                ]);

                $movement = Movement::create([
                    'product_id' => $item['product_id'],
                    'type' => 'entry',
                    'quantity' => $item['base_quantity'] ?? $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'description' => 'Compra No. ' . $this->invoice_number,
                    'user_id' => Auth::id(),
                    'reference_type' => 'purchase',
                    'reference_id' => $purchase->id,
                ]);

                $product = $products[$item['product_id']] ?? Product::find($item['product_id']);
                $baseQty = $item['base_quantity'] ?? $item['quantity'];
                $totalItemValue = $item['quantity'] * $item['unit_cost'];
                $baseUnitCost = $totalItemValue / max(1, $baseQty);
                $inventoryService->processPurchaseEntry($product, $baseQty, $baseUnitCost, $movement);
            }

            DB::commit();
            session()->forget('purchase_form_draft');
            $this->dispatch('show-toast', type: 'success', message: 'Compra registrada exitosamente.');

            $this->purchasedId = $purchase->id;
            $this->loadShelfAssignments();
            $this->loadAllShelves();
            $this->showShelfModal = true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al registrar compra: ' . $e->getMessage(), ['exception' => $e]);
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
                'current_shelves' => $existing->map(fn($ps) => $ps->shelf->code . ' (' . $ps->quantity . ')')->implode(', '),
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
            'display' => $indent . $branch . $shelf->code . ' — ' . $shelf->label . $fullLabel,
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
        if (!empty($fullShelves)) {
            $this->dispatch('show-toast', type: 'error', message: 'Ubicaciones llenas: ' . implode(', ', $fullShelves));
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
