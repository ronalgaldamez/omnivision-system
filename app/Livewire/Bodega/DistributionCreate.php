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
    public $originStock = 0;
    public $available = 0;
    public $devices = [];
    public $selectedDevices = [];
    public $selectAll = false;
    public $quantity = 0;
    public $originBranchId = '';
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
        $this->loadOriginStock();
    }

    public function clearProduct()
    {
        $this->selectedProductId = null;
        $this->selectedProduct = null;
        $this->productSearch = '';
        $this->originStock = 0;
        $this->available = 0;
        $this->devices = [];
        $this->selectedDevices = [];
        $this->selectAll = false;
        $this->quantity = 0;
    }

    public function updatedOriginBranchId()
    {
        if ($this->selectedProductId) {
            $this->loadOriginStock();
        }
    }

    private function loadOriginStock()
    {
        if (!$this->originBranchId || !$this->selectedProductId) {
            $this->originStock = 0;
            $this->available = 0;
            $this->devices = [];
            $this->selectedDevices = [];
            $this->selectAll = false;
            $this->quantity = 0;
            return;
        }

        $requiresDevice = $this->selectedProduct->category?->requires_device_registration ?? false;

        if ($requiresDevice) {
            $originDevices = Device::where('product_id', $this->selectedProductId)
                ->where('branch_id', $this->originBranchId)
                ->where('status', 'in_stock')->get();

            $this->originStock = $originDevices->count();
            $this->available = $this->originStock;

            $this->devices = $originDevices->map(fn($d) => [
                'id' => $d->id,
                'mac_address' => $d->mac_address,
            ])->toArray();
        } else {
            $inv = BranchInventory::where('branch_id', $this->originBranchId)
                ->where('product_id', $this->selectedProductId)
                ->first();

            $this->originStock = (float) ($inv->allocated_quantity ?? 0);
            $this->available = $this->originStock;
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
            'originBranchId' => 'required|exists:branches,id|different:targetBranchId',
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

        if (!$requiresDevice && $this->quantity > $this->available) {
            $this->dispatch('show-toast', type: 'error', message: 'Stock insuficiente en la sucursal de origen. Disponible: '.$this->available);
            return;
        }

        $shipment = DistributionShipment::create([
            'code' => DistributionShipment::generateCode(),
            'origin_branch_id' => $this->originBranchId,
            'branch_id' => $this->targetBranchId,
            'status' => 'pending',
            'created_by' => Auth::id(),
            'notes' => $this->notes,
        ]);

        $origin = Branch::find($this->originBranchId);
        $branch = Branch::find($this->targetBranchId);

        $itemQty = $requiresDevice ? count($this->selectedDevices) : $this->quantity;

        $shipment->items()->create([
            'product_id' => $this->selectedProduct->id,
            'product_name' => $this->selectedProduct->name,
            'quantity' => $itemQty,
        ]);

        $this->dispatch('show-toast', type: 'success', message: "Traspaso {$shipment->code} creado: {$origin?->name} → {$branch?->name}. Entregalo para confirmar.");
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
