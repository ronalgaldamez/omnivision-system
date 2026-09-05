<?php

namespace App\Livewire\Bodega;

use App\Models\Category;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\Supplier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class QuotationCreate extends Component
{
    public $supplier_id = '';
    public $supplierSearch = '';
    public $supplierResults = [];
    public $showSupplierModal = false;
    public $supplierList = [];
    public $supplierListSearch = '';

    public $items = [];

    // Agregar item
    public $productSearch = '';
    public $productResults = [];
    public $selectedProductId = '';
    public $selectedProductName = '';
    public $selectedProductSku = '';
    public $currentQuantity = 1;
    public $currentUnitCost = 0;
    public $editingIndex = null;

    // Producto nuevo propuesto (no existe en el catálogo aún)
    public $createMode = false;
    public $newProductName = '';
    public $newProductUnit = 'unidad';
    public $newProductCategoryId = '';
    public $newProductCategorySearch = '';
    public $newProductCategoryResults = [];

    public $confirmingRemoveIndex = null;

    public $confirmingSave = false;

    public $notes = '';

    public $mode = 'single'; // single | multiple

    protected function rules()
    {
        return [
            'supplier_id' => $this->mode === 'single' ? 'required|exists:suppliers,id' : 'nullable|exists:suppliers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.pending_name' => 'nullable|string|max:255',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_cost' => 'required|numeric|gt:0',
        ];
    }

    protected $messages = [
        'supplier_id.required' => 'Seleccioná un proveedor.',
        'items.required' => 'Agregá al menos un producto.',
        'items.min' => 'Agregá al menos un producto.',
    ];

    // ── Proveedor ──
    public function updatedSupplierSearch()
    {
        if (strlen($this->supplierSearch) >= 2) {
            $this->supplierResults = Supplier::where('name', 'like', '%'.$this->supplierSearch.'%')
                ->orWhere('nit', 'like', '%'.$this->supplierSearch.'%')
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
            $this->showSupplierModal = false;
        }
    }

    public function openSupplierModal()
    {
        $this->supplierListSearch = '';
        $this->supplierList = Supplier::orderBy('name')->take(50)->get();
        $this->showSupplierModal = true;
    }

    public function closeSupplierModal()
    {
        $this->showSupplierModal = false;
    }

    public function updatedSupplierListSearch()
    {
        if (strlen($this->supplierListSearch) >= 2) {
            $this->supplierList = Supplier::where('name', 'like', '%'.$this->supplierListSearch.'%')
                ->orWhere('nit', 'like', '%'.$this->supplierListSearch.'%')
                ->orderBy('name')->take(50)->get();
        } else {
            $this->supplierList = Supplier::orderBy('name')->take(50)->get();
        }
    }

    public function clearSupplier()
    {
        $this->supplier_id = '';
        $this->supplierSearch = '';
    }

    // ── Producto ──
    public function updatedProductSearch()
    {
        if (strlen($this->productSearch) >= 2) {
            $this->productResults = Product::where('name', 'like', '%'.$this->productSearch.'%')
                ->orWhere('sku', 'like', '%'.$this->productSearch.'%')
                ->limit(10)->get();
        } else {
            $this->productResults = [];
        }
    }

    public function selectProduct($id)
    {
        $product = Product::find($id);
        if ($product) {
            $this->selectedProductId = $product->id;
            $this->selectedProductName = $product->name;
            $this->selectedProductSku = $product->sku;
            $this->productSearch = $product->name.' ('.$product->sku.')';
            $this->productResults = [];
            $this->currentUnitCost = (float) ($product->average_cost ?? 0);
        }
    }

    public function clearSelectedProduct()
    {
        $this->selectedProductId = '';
        $this->selectedProductName = '';
        $this->selectedProductSku = '';
        $this->productSearch = '';
    }

    // ── Producto nuevo propuesto ──
    public function activateCreateMode()
    {
        // Entrar al modo "producto nuevo" abandona cualquier edición en curso.
        $this->editingIndex = null;
        $this->createMode = true;
        $this->selectedProductId = '';
        $this->selectedProductName = '';
        $this->selectedProductSku = '';
        $this->productSearch = '';
        $this->newProductName = '';
        $this->newProductUnit = 'unidad';
        $this->newProductCategoryId = '';
        $this->newProductCategorySearch = '';
        $this->newProductCategoryResults = [];
    }

    public function cancelCreateMode()
    {
        // Salir del modo "producto nuevo" también abandona la edición en curso.
        $this->editingIndex = null;
        $this->createMode = false;
        $this->newProductName = '';
        $this->newProductUnit = 'unidad';
        $this->newProductCategoryId = '';
        $this->newProductCategorySearch = '';
        $this->newProductCategoryResults = [];
    }

    public function cancelEdit()
    {
        // Abandonar la edición de una fila sin modificarla (queda intacta en la lista).
        $this->editingIndex = null;
        $this->createMode = false;
        $this->resetProductFields();
        $this->newProductName = '';
        $this->newProductUnit = 'unidad';
        $this->newProductCategoryId = '';
        $this->newProductCategorySearch = '';
        $this->newProductCategoryResults = [];
    }

    public function updatedNewProductCategorySearch()
    {
        if (strlen($this->newProductCategorySearch) >= 2) {
            $this->newProductCategoryResults = Category::where('name', 'like', '%'.$this->newProductCategorySearch.'%')
                ->orderBy('name')->limit(10)->get();
        } else {
            $this->newProductCategoryResults = [];
        }
    }

    public function selectNewProductCategory($id)
    {
        $cat = Category::find($id);
        if ($cat) {
            $this->newProductCategoryId = $cat->id;
            $this->newProductCategorySearch = $cat->name;
            $this->newProductCategoryResults = [];
        }
    }

    public function clearNewProductCategory()
    {
        $this->newProductCategoryId = '';
        $this->newProductCategorySearch = '';
    }

    public function addItem()
    {
        // En modo múltiple cada producto se agrega con el proveedor del encabezado
        // ("proveedor del próximo producto").
        if ($this->mode === 'multiple' && empty($this->supplier_id)) {
            $this->dispatch('show-toast', type: 'error', message: 'Elegí el proveedor de este producto antes de agregarlo.');
            return;
        }

        // Modo producto nuevo propuesto
        if ($this->createMode) {
            if (trim($this->newProductName) === '') {
                $this->dispatch('show-toast', type: 'error', message: 'Ingresá el nombre del producto.');
                return;
            }
            if ($this->currentQuantity <= 0) {
                $this->dispatch('show-toast', type: 'error', message: 'Ingresá una cantidad válida.');
                return;
            }
            if ($this->currentUnitCost <= 0) {
                $this->dispatch('show-toast', type: 'error', message: 'Ingresá un costo válido.');
                return;
            }

            $item = [
                'product_id' => null,
                'product_name' => $this->newProductName,
                'product_sku' => 'Nuevo',
                'supplier_id' => $this->supplier_id,
                'pending_name' => $this->newProductName,
                'pending_unit' => $this->newProductUnit,
                'pending_category_id' => $this->newProductCategoryId ?: null,
                'quantity' => $this->currentQuantity,
                'unit_cost' => $this->currentUnitCost,
            ];

            $this->putItem($item);
            $this->cancelCreateMode();
            $this->resetProductFields();
            return;
        }

        if (! $this->selectedProductId) {
            $this->dispatch('show-toast', type: 'error', message: 'Seleccioná un producto.');
            return;
        }
        if ($this->currentQuantity <= 0) {
            $this->dispatch('show-toast', type: 'error', message: 'Ingresá una cantidad válida.');
            return;
        }
        if ($this->currentUnitCost <= 0) {
            $this->dispatch('show-toast', type: 'error', message: 'Ingresá un costo válido.');
            return;
        }

        $item = [
            'product_id' => $this->selectedProductId,
            'product_name' => $this->selectedProductName,
            'product_sku' => $this->selectedProductSku,
            'supplier_id' => $this->supplier_id,
            'pending_name' => null,
            'pending_unit' => null,
            'pending_category_id' => null,
            'quantity' => $this->currentQuantity,
            'unit_cost' => $this->currentUnitCost,
        ];

        $this->putItem($item);
    }

    public function editItem($index)
    {
        if (! array_key_exists($index, $this->items)) {
            return;
        }

        $item = $this->items[$index];
        $this->editingIndex = $index;
        $this->currentQuantity = $item['quantity'];
        $this->currentUnitCost = $item['unit_cost'];

        // En modo múltiple el proveedor es por ítem: restaurar el del ítem editado
        // para no reasignarlo al proveedor del encabezado al re-agregar.
        if ($this->mode === 'multiple' && ! empty($item['supplier_id'])) {
            $this->selectSupplier($item['supplier_id']);
        }

        // Producto nuevo propuesto (aún no existe en el catálogo): se reabre el
        // panel de creación con sus datos precargados.
        if (empty($item['product_id'])) {
            $this->createMode = true;
            $this->selectedProductId = '';
            $this->selectedProductName = '';
            $this->selectedProductSku = '';
            $this->productSearch = '';
            $this->newProductName = $item['pending_name'] ?? '';
            $this->newProductUnit = $item['pending_unit'] ?? 'unidad';
            $this->newProductCategoryId = $item['pending_category_id'] ?? '';
            $this->newProductCategoryResults = [];
            $this->newProductCategorySearch = '';

            if ($this->newProductCategoryId) {
                $category = Category::find($this->newProductCategoryId);
                $this->newProductCategorySearch = $category?->name ?? '';
            }
            return;
        }

        // Producto existente del catálogo
        $this->createMode = false;
        $this->selectedProductId = $item['product_id'];
        $this->selectedProductName = $item['product_name'];
        $this->selectedProductSku = $item['product_sku'];
        $this->productSearch = $item['product_name'].' ('.$item['product_sku'].')';
    }

    public function askRemoveItem($index)
    {
        $this->confirmingRemoveIndex = $index;
    }

    public function cancelRemoveItem()
    {
        $this->confirmingRemoveIndex = null;
    }

    public function removeItem()
    {
        $index = $this->confirmingRemoveIndex;
        if ($index === null || ! array_key_exists($index, $this->items)) {
            return;
        }
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        $this->confirmingRemoveIndex = null;
        $this->dispatch('show-toast', type: 'success', message: 'Producto eliminado de la cotización.');
    }

    /**
     * Agrega o reemplaza un ítem respetando la edición en curso.
     * La edición es "en el lugar": la fila nunca se elimina de la lista,
     * así el índice no se corrompe.
     */
    private function putItem(array $item): void
    {
        if ($this->editingIndex !== null && array_key_exists($this->editingIndex, $this->items)) {
            $this->items[$this->editingIndex] = $item;
        } else {
            $this->items[] = $item;
        }
        $this->editingIndex = null;
    }

    private function resetProductFields()
    {
        $this->selectedProductId = '';
        $this->selectedProductName = '';
        $this->selectedProductSku = '';
        $this->productSearch = '';
        $this->currentQuantity = 1;
        $this->currentUnitCost = 0;
    }

    public function getTotalsProperty(): array
    {
        $subtotal = array_sum(array_map(fn ($i) => $i['quantity'] * $i['unit_cost'], $this->items));
        $iva = round($subtotal * 0.13, 2);
        return [
            'subtotal' => round($subtotal, 2),
            'iva' => $iva,
            'total' => round($subtotal + $iva, 2),
        ];
    }

    public function getConfirmSaveMessageProperty(): string
    {
        $count = count($this->items);

        if ($this->mode === 'multiple') {
            $providers = collect($this->items)->pluck('supplier_id')->filter()->unique()->count();

            return "¿Estás seguro? Se crearán {$providers} cotizaciones separadas (una por proveedor) con {$count} producto(s) en total. Quedarán pendientes de aprobación.";
        }

        $supplier = $this->supplier_id ? Supplier::find($this->supplier_id) : null;
        $target = $supplier?->name ?? 'este proveedor';

        return "¿Estás seguro de generar esta cotización a {$target} con {$count} producto(s)? Quedará pendiente de aprobación.";
    }

    public function save()
    {
        try {
            $this->validate();
        } catch (ValidationException $e) {
            $errors = $e->validator->errors();

            $this->dispatch('show-toasts', errors: $errors->all());

            foreach ($errors->keys() as $key) {
                $this->addError($key, $errors->first($key));
            }

            return;
        }

        // En modo múltiple, validar que cada item tenga proveedor
        if ($this->mode === 'multiple') {
            foreach ($this->items as $item) {
                if (empty($item['supplier_id'])) {
                    $this->dispatch('show-toast', type: 'error', message: 'Hay productos sin proveedor asignado. Elegí el proveedor antes de agregar cada producto.');
                    return;
                }
            }
        }

        $this->confirmingSave = true;
    }

    public function cancelSave()
    {
        $this->confirmingSave = false;
    }

    public function confirmSave()
    {
        $this->confirmingSave = false;

        $codes = [];

        if ($this->mode === 'single') {
            // Modo individual: UNA cotización al proveedor elegido en el encabezado.
            // Todos los items (aunque conserven un proveedor residual de otro modo)
            // se cotizan contra ese proveedor: para varios proveedores está el modo múltiple.
            $quotation = $this->createQuotationForSupplier((int) $this->supplier_id, $this->items);
            $codes[] = $quotation->code;
        } else {
            // Modo múltiple: una cotización separada por proveedor (cada item es dueño de su proveedor).
            $grouped = collect($this->items)->groupBy('supplier_id');
            foreach ($grouped as $supplierId => $groupItems) {
                $codes[] = $this->createQuotationForSupplier((int) $supplierId, $groupItems->values()->all())->code;
            }
        }

        $msg = count($codes) === 1
            ? "Cotización {$codes[0]} creada. Queda pendiente de aprobación."
            : count($codes).' cotizaciones creadas ('.implode(', ', $codes).'). Quedan pendientes de aprobación.';

        session()->flash('message', $msg);
        return redirect()->route('bodega.quotations.index');
    }

    private function createQuotationForSupplier(int $supplierId, array $items): Quotation
    {
        $subtotal = collect($items)->sum(fn ($i) => $i['quantity'] * $i['unit_cost']);
        $iva = round($subtotal * 0.13, 2);

        $quotation = Quotation::create([
            'code' => Quotation::generateCode(),
            'supplier_id' => $supplierId,
            'branch_id' => auth()->user()->activeBranchId(),
            'created_by' => Auth::id(),
            'status' => 'pending',
            'subtotal' => round($subtotal, 2),
            'iva_amount' => $iva,
            'total' => round($subtotal + $iva, 2),
            'notes' => $this->notes,
        ]);

        foreach ($items as $item) {
            $quotation->items()->create([
                'product_id' => $item['product_id'] ?? null,
                'pending_name' => $item['pending_name'] ?? null,
                'pending_unit' => $item['pending_unit'] ?? null,
                'pending_category_id' => $item['pending_category_id'] ?? null,
                'quantity' => $item['quantity'],
                'unit_cost' => $item['unit_cost'],
            ]);
        }

        return $quotation;
    }

    public function render()
    {
        $totals = [
            'subtotal' => round(array_sum(array_map(fn ($i) => $i['quantity'] * $i['unit_cost'], $this->items)), 2),
            'iva' => 0,
            'total' => 0,
        ];
        $totals['iva'] = round($totals['subtotal'] * 0.13, 2);
        $totals['total'] = round($totals['subtotal'] + $totals['iva'], 2);

        $units = \App\Models\UnitOfMeasure::where('is_active', true)->orderBy('name')->get();

        return view('livewire.bodega.quotation-create', compact('totals', 'units'))->layout('components.layouts.app');
    }
}
