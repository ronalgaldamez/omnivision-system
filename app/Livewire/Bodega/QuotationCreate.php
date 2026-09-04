<?php

namespace App\Livewire\Bodega;

use App\Models\Product;
use App\Models\Quotation;
use App\Models\Supplier;
use Illuminate\Support\Facades\Auth;
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

    public $notes = '';

    protected $rules = [
        'supplier_id' => 'required|exists:suppliers,id',
        'items' => 'required|array|min:1',
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.quantity' => 'required|numeric|min:1',
        'items.*.unit_cost' => 'required|numeric|gt:0',
    ];

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

    public function addItem()
    {
        if (!$this->selectedProductId) {
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
            'quantity' => $this->currentQuantity,
            'unit_cost' => $this->currentUnitCost,
        ];

        if ($this->editingIndex !== null && array_key_exists($this->editingIndex, $this->items)) {
            $this->items[$this->editingIndex] = $item;
        } else {
            $this->items[] = $item;
        }
        $this->editingIndex = null;
        $this->resetProductFields();
    }

    public function editItem($index)
    {
        $item = $this->items[$index];
        $this->selectedProductId = $item['product_id'];
        $this->selectedProductName = $item['product_name'];
        $this->selectedProductSku = $item['product_sku'];
        $this->productSearch = $item['product_name'].' ('.$item['product_sku'].')';
        $this->currentQuantity = $item['quantity'];
        $this->currentUnitCost = $item['unit_cost'];
        $this->editingIndex = $index;
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
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

    public function save()
    {
        $this->validate();

        $totals = $this->totals;

        $quotation = Quotation::create([
            'code' => Quotation::generateCode(),
            'supplier_id' => $this->supplier_id,
            'branch_id' => auth()->user()->activeBranchId(),
            'created_by' => Auth::id(),
            'status' => 'pending',
            'subtotal' => $totals['subtotal'],
            'iva_amount' => $totals['iva'],
            'total' => $totals['total'],
            'notes' => $this->notes,
        ]);

        foreach ($this->items as $item) {
            $quotation->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_cost' => $item['unit_cost'],
            ]);
        }

        $this->dispatch('show-toast', type: 'success', message: "Cotización {$quotation->code} creada. Queda pendiente de aprobación.");
        return redirect()->route('bodega.quotations.index');
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

        return view('livewire.bodega.quotation-create', compact('totals'))->layout('components.layouts.app');
    }
}
