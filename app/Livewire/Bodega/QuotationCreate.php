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

    // Id del borrador que se está editando (null = creación nueva).
    public $draftId = null;

    // Código del borrador en edición (para mostrarlo en la vista).
    public $draftCode = null;

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

    public function mount($id = null)
    {
        if ($id !== null) {
            $this->loadDraft((int) $id);
        }
    }

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
        if (! $supplier) {
            return;
        }

        $this->supplier_id = $supplier->id;
        $this->supplierSearch = $supplier->name.' (NIT: '.($supplier->nit ?? 'N/A').')';
        $this->supplierResults = [];
        $this->showSupplierModal = false;

        // En modo individual el proveedor del encabezado es el de TODA la cotización:
        // al cambiarlo con productos ya cargados, se re-asignan todos los items al nuevo
        // proveedor (evita pedirle a un proveedor productos que se cargaron para otro).
        // En modo múltiple cada item conserva su proveedor y no se toca nada.
        if ($this->mode === 'single' && $this->items !== []) {
            foreach ($this->items as &$item) {
                $item['supplier_id'] = $supplier->id;
            }
            unset($item);
        }
    }

    public function switchMode(string $mode)
    {
        if ($this->draftId) {
            $this->dispatch('show-toast', type: 'error', message: 'Un borrador se edita como cotización individual.');
            return;
        }

        if ($mode === $this->mode) {
            return;
        }

        // Hacia múltiple: siempre se permite; cada item conserva su proveedor.
        if ($mode === 'multiple') {
            $this->mode = 'multiple';
            return;
        }

        // Hacia individual: TODOS los items deben pertenecer a un solo proveedor.
        $suppliers = $this->buildUsedSuppliers();

        if (count($suppliers) > 1) {
            $names = collect($suppliers)->map(fn ($s) => "{$s['name']} ({$s['count']})")->implode(', ');
            $this->dispatch('show-toast', type: 'error', message: "No podés usar una cotización individual con productos de varios proveedores. La lista tiene productos de: {$names}. Eliminá los de otros proveedores o seguí en modo múltiple.");
            return;
        }

        $this->mode = 'single';

        // Consistencia: si los items ya tienen un único proveedor, el encabezado pasa
        // a ser ese proveedor (no se le pide a otro proveedor productos que no vende).
        if (! empty($suppliers[0]['id']) && (int) $this->supplier_id !== (int) $suppliers[0]['id']) {
            $this->selectSupplier($suppliers[0]['id']);
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
        $this->resetProductFields();
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

        if ($this->draftId) {
            $supplier = $this->supplier_id ? Supplier::find($this->supplier_id) : null;
            $target = $supplier?->name ?? 'este proveedor';

            return "¿Enviar este borrador a aprobación? La cotización quedará pendiente con {$count} producto(s) para {$target}.";
        }

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

        // Envío de un borrador: se completa la misma cotización y pasa a pendiente.
        if ($this->draftId) {
            $quotation = Quotation::find($this->draftId);

            if (! $quotation || $quotation->status !== 'draft' || (int) $quotation->created_by !== (int) Auth::id()) {
                $this->dispatch('show-toast', type: 'error', message: 'Este borrador ya no está disponible.');
                return;
            }

            $subtotal = round(array_sum(array_map(fn ($i) => $i['quantity'] * $i['unit_cost'], $this->items)), 2);
            $iva = round($subtotal * 0.13, 2);

            $quotation->update([
                'supplier_id' => (int) $this->supplier_id,
                'status' => 'pending',
                'subtotal' => $subtotal,
                'iva_amount' => $iva,
                'total' => round($subtotal + $iva, 2),
                'notes' => $this->notes,
            ]);
            $quotation->items()->delete();
            $this->persistItems($quotation, $this->items);

            session()->flash('message', "Cotización {$quotation->code} enviada. Queda pendiente de aprobación.");

            return redirect()->route('bodega.quotations.index');
        }

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

    /**
     * Carga un borrador existente (solo su creador, mientras siga en borrador).
     */
    private function loadDraft(int $id): void
    {
        $quotation = Quotation::with(['supplier', 'items.product'])->findOrFail($id);

        abort_unless(
            $quotation->status === 'draft' && (int) $quotation->created_by === (int) auth()->id(),
            403
        );

        $this->draftId = $quotation->id;
        $this->draftCode = $quotation->code;
        $this->mode = 'single'; // un borrador siempre se trabaja como cotización individual
        $this->notes = $quotation->notes ?? '';

        if ($quotation->supplier) {
            $this->supplier_id = $quotation->supplier_id;
            $this->supplierSearch = $quotation->supplier->name.' (NIT: '.($quotation->supplier->nit ?? 'N/A').')';
        }

        $this->items = $quotation->items->map(function ($qitem) {
            if ($qitem->isPending()) {
                return [
                    'product_id' => null,
                    'product_name' => $qitem->pending_name,
                    'product_sku' => 'Nuevo',
                    'supplier_id' => $this->supplier_id ?: '',
                    'pending_name' => $qitem->pending_name,
                    'pending_unit' => $qitem->pending_unit ?? 'unidad',
                    'pending_category_id' => $qitem->pending_category_id,
                    'quantity' => (float) $qitem->quantity,
                    'unit_cost' => (float) $qitem->unit_cost,
                ];
            }

            $product = $qitem->product;

            return [
                'product_id' => $qitem->product_id,
                'product_name' => $product?->name ?? 'Producto eliminado',
                'product_sku' => $product?->sku ?? '-',
                'supplier_id' => $this->supplier_id ?: '',
                'pending_name' => null,
                'pending_unit' => null,
                'pending_category_id' => null,
                'quantity' => (float) $qitem->quantity,
                'unit_cost' => (float) $qitem->unit_cost,
            ];
        })->values()->all();
    }

    /**
     * Guarda la sesión actual como borrador (crea o actualiza la fila en draft).
     */
    public function saveDraft()
    {
        // Los borradores se guardan por proveedor: para varios proveedores se envía la cotización múltiple.
        if ($this->mode === 'multiple' && count($this->buildUsedSuppliers()) > 1) {
            $this->dispatch('show-toast', type: 'error', message: 'Para guardar como borrador usá productos de un solo proveedor, o enviá la cotización múltiple.');
            return;
        }

        $supplierId = $this->supplier_id ? (int) $this->supplier_id : null;
        $subtotal = round(array_sum(array_map(fn ($i) => $i['quantity'] * $i['unit_cost'], $this->items)), 2);
        $iva = round($subtotal * 0.13, 2);

        $data = [
            'supplier_id' => $supplierId,
            'subtotal' => $subtotal,
            'iva_amount' => $iva,
            'total' => round($subtotal + $iva, 2),
            'notes' => $this->notes,
        ];

        if ($this->draftId) {
            $quotation = Quotation::find($this->draftId);
            if (! $quotation || $quotation->status !== 'draft' || (int) $quotation->created_by !== (int) Auth::id()) {
                $this->dispatch('show-toast', type: 'error', message: 'Este borrador ya no está disponible.');
                return;
            }

            $quotation->update($data);
            $quotation->items()->delete();
            $this->persistItems($quotation, $this->items);

            $this->dispatch('show-toast', type: 'success', message: 'Borrador actualizado.');
            return;
        }

        $quotation = Quotation::create($data + [
            'code' => Quotation::generateCode(),
            'branch_id' => auth()->user()->activeBranchId(),
            'created_by' => Auth::id(),
            'status' => 'draft',
        ]);
        $this->persistItems($quotation, $this->items);

        $this->draftId = $quotation->id;
        $this->dispatch('show-toast', type: 'success', message: "Borrador {$quotation->code} guardado. Lo encontrás en 'Mis borradores'.");
    }

    /**
     * Persiste los items de la sesión en una cotización (catálogo y productos propuestos).
     */
    private function persistItems(Quotation $quotation, array $items): void
    {
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

        $usedSuppliers = $this->buildUsedSuppliers();
        $itemsBySupplier = $this->buildItemsBySupplier();

        return view('livewire.bodega.quotation-create', compact('totals', 'units', 'usedSuppliers', 'itemsBySupplier'))->layout('components.layouts.app');
    }

    /**
     * Proveedores presentes en la lista (orden de aparición) con su cantidad de items.
     * Se usan para los chips de acceso rápido en modo múltiple.
     */
    private function buildUsedSuppliers(): array
    {
        $counts = [];
        foreach ($this->items as $item) {
            $sid = $item['supplier_id'] ?? null;
            if ($sid === null || $sid === '') {
                continue;
            }
            $key = (int) $sid;
            $counts[$key] = ($counts[$key] ?? 0) + 1;
        }

        if ($counts === []) {
            return [];
        }

        $suppliers = Supplier::whereIn('id', array_keys($counts))->get()->keyBy('id');

        $out = [];
        foreach ($counts as $sid => $count) {
            $s = $suppliers->get($sid);
            $out[] = [
                'id' => $sid,
                'name' => $s?->name ?? 'Proveedor #'.$sid,
                'count' => $count,
            ];
        }

        return $out;
    }

    /**
     * Items agrupados por proveedor (orden de aparición), conservando el índice
     * plano original para editar/eliminar. Cada grupo = una cotización en modo múltiple.
     */
    private function buildItemsBySupplier(): array
    {
        $position = [];
        $groups = [];

        foreach ($this->items as $flatIndex => $item) {
            $sid = $item['supplier_id'] ?? null;
            $key = ($sid !== null && $sid !== '') ? (int) $sid : '__sin_proveedor__';

            if (! array_key_exists($key, $position)) {
                $position[$key] = count($groups);
                $groups[] = [
                    'supplier_id' => is_int($key) ? $key : null,
                    'supplier_name' => '',
                    'supplier_nit' => '',
                    'subtotal' => 0,
                    'rows' => [],
                ];
            }

            $gi = $position[$key];
            $groups[$gi]['rows'][] = ['index' => $flatIndex] + $item;
            $groups[$gi]['subtotal'] += (float) $item['quantity'] * (float) $item['unit_cost'];
        }

        $ids = array_values(array_filter(array_map(fn ($g) => $g['supplier_id'], $groups)));
        if ($ids !== []) {
            $suppliers = Supplier::whereIn('id', $ids)->get()->keyBy('id');
            foreach ($groups as &$group) {
                $s = $group['supplier_id'] ? $suppliers->get($group['supplier_id']) : null;
                $group['supplier_name'] = $s?->name ?? 'Proveedor #'.$group['supplier_id'];
                $group['supplier_nit'] = $s?->nit ?? '';
            }
            unset($group);
        }

        return $groups;
    }
}
