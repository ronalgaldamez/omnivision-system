<?php

namespace App\Livewire\Bodega;

use App\Models\Branch;
use App\Models\BranchInventory;
use App\Models\CompanyProductInventory;
use App\Models\Device;
use App\Models\IntercompanySale;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class IntercompanySaleCreate extends Component
{
    public $productSearch = '';
    public $productResults = [];
    public $showProductModal = false;
    public $productList = [];
    public $productListSearch = '';

    public $selectedProductId = null;
    public $selectedProduct = null;
    public $sellerBranchId = '';
    public $buyerBranchId = '';
    public $available = 0;
    public $unitCost = 0;
    public $quantity = 0;
    public $subtotal = 0;
    public $ivaAmount = 0;
    public $total = 0;
    public $notes = '';

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

    public function openProductModal()
    {
        $this->productListSearch = '';
        $this->productList = Product::orderBy('name')->take(50)->get();
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
            $this->productList = Product::where('name', 'like', '%'.$this->productListSearch.'%')
                ->orWhere('sku', 'like', '%'.$this->productListSearch.'%')
                ->orderBy('name')->take(50)->get();
        } else {
            $this->productList = Product::orderBy('name')->take(50)->get();
        }
    }

    public function selectProductFromList($id)
    {
        $this->selectProduct($id);
        $this->closeProductModal();
    }

    public function selectProduct($id)
    {
        $product = Product::find($id);
        if (!$product) return;

        $this->selectedProductId = $product->id;
        $this->selectedProduct = $product;
        $this->productSearch = $product->name.' ('.$product->sku.')';
        $this->productResults = [];
        $this->loadPriceAndStock();
    }

    public function clearProduct()
    {
        $this->selectedProductId = null;
        $this->selectedProduct = null;
        $this->productSearch = '';
        $this->available = 0;
        $this->unitCost = 0;
        $this->quantity = 0;
        $this->recalculate();
    }

    public function updatedSellerBranchId()
    {
        if ($this->selectedProductId) {
            $this->loadPriceAndStock();
        }
    }

    private function loadPriceAndStock()
    {
        $this->loadAvailableStock();
        $this->loadUnitCost();
        $this->recalculate();
    }

    private function loadAvailableStock()
    {
        if (!$this->sellerBranchId || !$this->selectedProductId) {
            $this->available = 0;
            return;
        }

        $requiresDevice = $this->selectedProduct->category?->requires_device_registration ?? false;

        if ($requiresDevice) {
            $this->available = Device::where('product_id', $this->selectedProductId)
                ->where('branch_id', $this->sellerBranchId)
                ->where('status', 'in_stock')
                ->count();
        } else {
            $inv = BranchInventory::where('branch_id', $this->sellerBranchId)
                ->where('product_id', $this->selectedProductId)
                ->first();
            $this->available = (float) ($inv->allocated_quantity ?? 0);
        }
    }

    private function loadUnitCost()
    {
        $sellerBranch = Branch::find($this->sellerBranchId);
        $companyId = $sellerBranch?->company_id;

        $cost = $companyId
            ? CompanyProductInventory::averageCostFor($companyId, $this->selectedProductId)
            : null;

        $this->unitCost = $cost ?? (float) ($this->selectedProduct->average_cost ?? 0);
    }

    public function updatedQuantity()
    {
        $this->recalculate();
    }

    private function recalculate()
    {
        $this->quantity = max(0, (float) $this->quantity);
        $this->subtotal = round($this->quantity * $this->unitCost, 2);
        $this->ivaAmount = round($this->subtotal * 0.13, 2);
        $this->total = round($this->subtotal + $this->ivaAmount, 2);
    }

    public function save()
    {
        $this->validate([
            'sellerBranchId' => 'required|exists:branches,id',
            'buyerBranchId' => 'required|exists:branches,id|different:sellerBranchId',
            'selectedProductId' => 'required|exists:products,id',
        ]);

        $sellerBranch = Branch::find($this->sellerBranchId);
        $buyerBranch = Branch::find($this->buyerBranchId);

        if ($sellerBranch->company_id === $buyerBranch->company_id) {
            $this->dispatch('show-toast', type: 'error', message: 'Ambas sucursales pertenecen a la misma empresa. Para mover material dentro de una misma empresa usá un Traspaso.');
            return;
        }

        if ($this->quantity <= 0) {
            $this->dispatch('show-toast', type: 'error', message: 'Ingresá una cantidad válida.');
            return;
        }

        if ($this->quantity > $this->available) {
            $this->dispatch('show-toast', type: 'error', message: 'Stock insuficiente en la sucursal vendedora. Disponible: '.$this->available);
            return;
        }

        $requiresDevice = $this->selectedProduct->category?->requires_device_registration ?? false;
        if ($requiresDevice && $this->quantity != (int) $this->quantity) {
            $this->dispatch('show-toast', type: 'error', message: 'Para dispositivos la cantidad debe ser un número entero.');
            return;
        }

        $sale = IntercompanySale::create([
            'code' => IntercompanySale::generateCode(),
            'seller_branch_id' => $this->sellerBranchId,
            'buyer_branch_id' => $this->buyerBranchId,
            'subtotal' => $this->subtotal,
            'iva_amount' => $this->ivaAmount,
            'total' => $this->total,
            'status' => 'pending',
            'user_id' => Auth::id(),
        ]);

        $sale->items()->create([
            'product_id' => $this->selectedProduct->id,
            'product_name' => $this->selectedProduct->name,
            'quantity' => $this->quantity,
            'unit_cost' => $this->unitCost,
        ]);

        $this->dispatch('show-toast', type: 'success', message: "Venta {$sale->code} creada (pendiente): {$sellerBranch->name} → {$buyerBranch->name}. La sucursal compradora debe confirmar la recepción. Total: $".number_format($this->total, 2));
        $this->resetForm();
        return redirect()->route('bodega.intercompany-sales.index');
    }

    public function resetForm()
    {
        $this->selectedProductId = null;
        $this->selectedProduct = null;
        $this->productSearch = '';
        $this->available = 0;
        $this->unitCost = 0;
        $this->quantity = 0;
        $this->subtotal = 0;
        $this->ivaAmount = 0;
        $this->total = 0;
        $this->notes = '';
    }

    public function render()
    {
        $branches = Branch::with('company')->where('is_active', true)->orderBy('name')->get();
        $requiresDevice = $this->selectedProduct?->category?->requires_device_registration ?? false;
        return view('livewire.bodega.intercompany-sale-create', compact('branches', 'requiresDevice'))
            ->layout('components.layouts.app');
    }
}
