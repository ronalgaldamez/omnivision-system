<?php

namespace App\Livewire\Bodega;

use App\Models\Branch;
use App\Models\BranchInventory;
use App\Models\Device;
use App\Models\DistributionShipment;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DistributionCreate extends Component
{
    public $productSearch = '';
    public $productResults = [];
    public $showProductModal = false;
    public $productList = [];
    public $productListSearch = '';

    public $selectedProductId = null;
    public $selectedProduct = null;
    public $globalStock = 0;
    public $alreadyAllocated = 0;
    public $available = 0;
    public $devices = [];
    public $selectedDevices = [];
    public $selectAll = false;
    public $quantity = 0;
    public $targetBranchId = '';
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
        $this->loadProductData();
    }

    public function clearProduct()
    {
        $this->selectedProductId = null;
        $this->selectedProduct = null;
        $this->productSearch = '';
        $this->globalStock = 0;
        $this->alreadyAllocated = 0;
        $this->available = 0;
        $this->devices = [];
        $this->selectedDevices = [];
        $this->selectAll = false;
        $this->quantity = 0;
    }

    private function loadProductData()
    {
        $requiresDevice = $this->selectedProduct->category?->requires_device_registration ?? false;

        if ($requiresDevice) {
            $allDevices = Device::where('product_id', $this->selectedProductId)
                ->whereNull('branch_id')
                ->where('status', 'in_stock')->get();

            $this->globalStock = $allDevices->count();
            $this->available = $this->globalStock;

            $this->devices = $allDevices->map(fn($d) => [
                'id' => $d->id,
                'mac_address' => $d->mac_address,
            ])->toArray();
        } else {
            $this->globalStock = (float) $this->selectedProduct->current_stock;
            $alreadyAllocated = BranchInventory::where('product_id', $this->selectedProductId)->sum('allocated_quantity');
            $this->alreadyAllocated = (float) $alreadyAllocated;
            $this->available = $this->globalStock - $this->alreadyAllocated;
            $this->devices = [];
        }

        $this->selectedDevices = [];
        $this->selectAll = false;
        $this->quantity = 0;
    }

    public function toggleSelectAll()
    {
        $this->selectAll = !$this->selectAll;
        $this->selectedDevices = $this->selectAll ? collect($this->devices)->pluck('id')->toArray() : [];
    }

    public function toggleDevice($id)
    {
        if (in_array($id, $this->selectedDevices)) {
            $this->selectedDevices = array_filter($this->selectedDevices, fn($d) => $d != $id);
            $this->selectAll = false;
        } else {
            $this->selectedDevices[] = $id;
        }
    }

    public function save()
    {
        $this->validate([
            'targetBranchId' => 'required|exists:branches,id',
            'selectedProductId' => 'required|exists:products,id',
        ]);

        $requiresDevice = $this->selectedProduct->category?->requires_device_registration ?? false;

        if ($requiresDevice && empty($this->selectedDevices)) {
            $this->dispatch('show-toast', type: 'error', message: 'Seleccioná al menos un dispositivo.');
            return;
        }

        if (!$requiresDevice && $this->quantity <= 0) {
            $this->dispatch('show-toast', type: 'error', message: 'Ingresá una cantidad para enviar.');
            return;
        }

        $shipment = DistributionShipment::create([
            'code' => DistributionShipment::generateCode(),
            'branch_id' => $this->targetBranchId,
            'status' => 'pending',
            'created_by' => Auth::id(),
            'notes' => $this->notes,
        ]);

        $branch = Branch::find($this->targetBranchId);

        $itemQty = $requiresDevice ? count($this->selectedDevices) : $this->quantity;

        $shipment->items()->create([
            'product_id' => $this->selectedProduct->id,
            'product_name' => $this->selectedProduct->name,
            'quantity' => $itemQty,
        ]);

        if ($requiresDevice) {
            Device::whereIn('id', $this->selectedDevices)
                ->update(['branch_id' => $this->targetBranchId]);
        }

        $this->dispatch('show-toast', type: 'success', message: "Envío {$shipment->code} creado. Entregalo en {$branch?->name} para confirmar.");
        $this->reset();
        return redirect()->route('bodega.shipments.index');
    }

    public function render()
    {
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $requiresDevice = $this->selectedProduct?->category?->requires_device_registration ?? false;
        return view('livewire.bodega.distribution-create', compact('branches', 'requiresDevice'))
            ->layout('components.layouts.app');
    }
}
