<?php

namespace App\Livewire\Suppliers;

use Livewire\Component;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Movement;
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

    public $showConfirmModal = false;
    public $modalAction = null;
    public $modalItemIndex = null;
    public $modalMessage = '';

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

    public function mount()
    {
        $this->purchase_date = date('Y-m-d');
        $this->items = [];
        $this->calculateTotals();
    }

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

    // Búsqueda de proveedor
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

    // Búsqueda de producto
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
        // No eliminamos el item, se reemplazará al guardar
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
            $this->dispatch('showToast', ['type' => 'success', 'message' => 'Producto eliminado de la lista.']);
        } elseif ($this->modalAction === 'edit') {
            $this->editItem($this->modalItemIndex);
            $this->dispatch('showToast', ['type' => 'info', 'message' => 'Producto cargado para edición.']);
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
            $this->dispatch('showToast', ['type' => 'success', 'message' => 'Compra registrada exitosamente.']);
            $this->redirectRoute('purchases.index');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function render()
    {
        return view('livewire.suppliers.purchase-form')->layout('components.layouts.app');
    }
}