<?php

namespace App\Livewire\Suppliers;

use Livewire\Component;
use App\Models\Supplier;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Movement;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReturnToSupplierForm extends Component
{
    public $supplier_id = '';
    public $purchase_id = '';
    public $returnMode = 'full'; // 'full', 'individual', 'partial'
    public $items = [];
    public $selectedItems = [];
    public $partialQuantities = [];

    public $showConfirmModal = false;

    protected $rules = [
        'supplier_id' => 'required|exists:suppliers,id',
        'purchase_id' => 'required|exists:purchases,id',
        'returnMode' => 'required|in:full,individual,partial',
    ];

    public function updatedSupplierId()
    {
        $this->purchase_id = '';
        $this->items = [];
        $this->selectedItems = [];
        $this->partialQuantities = [];
    }

    public function updatedPurchaseId()
    {
        $this->items = [];
        $this->selectedItems = [];
        $this->partialQuantities = [];

        if ($this->purchase_id) {
            $this->items = PurchaseItem::with('product')
                ->where('purchase_id', $this->purchase_id)
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'product_name' => $item->product->name,
                        'product_sku' => $item->product->sku,
                        'purchased_quantity' => $item->quantity,
                        'returned_quantity' => $item->returned_quantity,
                        'available_quantity' => $item->availableToReturn(),
                        'unit_cost' => $item->unit_cost,
                    ];
                })
                ->toArray();
        }
    }

    public function updatedReturnMode()
    {
        $this->selectedItems = [];
        $this->partialQuantities = [];
    }

    public function confirmReturn()
    {
        $this->validate();

        if ($this->returnMode === 'individual' && empty($this->selectedItems)) {
            $this->addError('selectedItems', 'Debe seleccionar al menos un producto para devolver.');
            return;
        }

        if ($this->returnMode === 'partial') {
            $hasError = false;
            foreach ($this->items as $item) {
                $qty = $this->partialQuantities[$item['id']] ?? 0;
                if ($qty > 0 && $qty > $item['available_quantity']) {
                    $this->addError("partialQuantities.{$item['id']}", "La cantidad excede lo disponible para {$item['product_name']} (máx. {$item['available_quantity']})");
                    $hasError = true;
                }
            }
            if ($hasError)
                return;
        }

        $this->showConfirmModal = true;
    }

    public function performReturn()
    {
        DB::beginTransaction();
        try {
            $purchase = Purchase::findOrFail($this->purchase_id);
            $supplier = Supplier::findOrFail($this->supplier_id);

            foreach ($this->items as $item) {
                $quantityToReturn = 0;

                if ($this->returnMode === 'full') {
                    $quantityToReturn = $item['available_quantity'];
                } elseif ($this->returnMode === 'individual') {
                    if (in_array($item['id'], $this->selectedItems)) {
                        $quantityToReturn = $item['available_quantity'];
                    }
                } elseif ($this->returnMode === 'partial') {
                    $quantityToReturn = (int) ($this->partialQuantities[$item['id']] ?? 0);
                }

                if ($quantityToReturn <= 0)
                    continue;

                $product = Product::find($item['product_id']);
                if ($product->current_stock < $quantityToReturn) {
                    throw new \Exception("Stock insuficiente para {$product->name}. Stock actual: {$product->current_stock}");
                }

                Movement::create([
                    'product_id' => $item['product_id'],
                    'type' => 'return_to_supplier',
                    'quantity' => $quantityToReturn,
                    'description' => "Devolución a proveedor {$supplier->name} - Factura {$purchase->invoice_number}",
                    'user_id' => Auth::id(),
                    'reference_type' => 'purchase',
                    'reference_id' => $purchase->id,
                ]);

                $product->updateStock($quantityToReturn, 'return_to_supplier');
                $product->save();

                $purchaseItem = PurchaseItem::find($item['id']);
                $purchaseItem->returned_quantity += $quantityToReturn;
                $purchaseItem->save();
            }

            DB::commit();
            $this->dispatch('showToast', ['type' => 'success', 'message' => 'Devolución registrada exitosamente.']);
            $this->redirectRoute('returns.index');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('showToast', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function render()
    {
        $suppliers = Supplier::orderBy('name')->get();
        $purchases = [];
        if ($this->supplier_id) {
            $purchases = Purchase::where('supplier_id', $this->supplier_id)
                ->orderBy('purchase_date', 'desc')
                ->get();
        }
        return view('livewire.suppliers.return-form', compact('suppliers', 'purchases'))->layout('components.layouts.app');
    }
}