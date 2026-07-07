<?php

namespace App\Livewire\Inventory;

use App\Models\Branch;
use App\Models\BranchInventory;
use App\Models\Device;
use App\Models\Movement;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DistributionForm extends Component
{
    public $productSearch = '';

    public $productSearchResults = [];

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

    public $targetBranchId = '';

    public $branchAllocations = [];

    public $activeBranchId = null;

    public $purchaseFilter = '';

    public $quantityAllocations = [];

    public function updatedProductSearch()
    {
        if (strlen($this->productSearch) >= 2) {
            $this->productSearchResults = Product::where('name', 'like', '%'.$this->productSearch.'%')
                ->orWhere('sku', 'like', '%'.$this->productSearch.'%')
                ->limit(10)->get();
        } else {
            $this->productSearchResults = [];
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
            $this->productList = Product::where('name', 'like', '%' . $this->productListSearch . '%')
                ->orWhere('sku', 'like', '%' . $this->productListSearch . '%')
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
        if (! $product) {
            return;
        }

        $this->selectedProductId = $product->id;
        $this->selectedProduct = $product;
        $this->productSearch = $product->name.' ('.$product->sku.')';
        $this->productSearchResults = [];

        $this->loadProductData();
    }

    public function updatedPurchaseFilter()
    {
        if ($this->selectedProductId) {
            $this->loadProductData();
        }
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
        $this->targetBranchId = '';
        $this->branchAllocations = [];
        $this->purchaseFilter = '';
    }

    private function loadProductData()
    {
        $this->activeBranchId = auth()->user()->activeBranchId();
        $this->purchaseFilter = '';

        $requiresDevice = $this->selectedProduct->category?->requires_device_registration ?? false;

        if ($requiresDevice) {
            $query = Device::where('product_id', $this->selectedProductId)->with('branch');
            $allDevices = $query->orderBy('mac_address')->get();

            $this->globalStock = $allDevices->count();
            $this->alreadyAllocated = $allDevices->whereNotNull('branch_id')->count();
            $this->available = $this->globalStock - $this->alreadyAllocated;

            $this->devices = $allDevices->map(fn($d) => [
                'id' => $d->id,
                'mac_address' => $d->mac_address,
                'branch_id' => $d->branch_id,
                'branch_name' => $d->branch?->name ?? null,
            ])->toArray();

            $this->selectedDevices = [];
            $this->selectAll = false;
            $this->targetBranchId = '';

            $this->branchAllocations = Branch::where('is_active', true)->orderBy('name')->get()->map(function ($b) use ($allDevices) {
                return ['branch_name' => $b->name, 'count' => $allDevices->where('branch_id', $b->id)->count()];
            })->toArray();
        } else {
            $this->devices = [];
            $this->selectedDevices = [];
            $this->selectAll = false;
            $this->targetBranchId = '';

            $inventories = BranchInventory::where('product_id', $this->selectedProductId)
                ->pluck('allocated_quantity', 'branch_id');

            $this->globalStock = (float) $this->selectedProduct->current_stock;
            $this->alreadyAllocated = (float) $inventories->sum();
            $this->available = $this->globalStock - $this->alreadyAllocated;

            $this->quantityAllocations = Branch::where('is_active', true)->orderBy('name')->get()->map(function ($b) use ($inventories) {
                return [
                    'branch_id' => $b->id,
                    'branch_name' => $b->name,
                    'current_allocated' => (float) ($inventories[$b->id] ?? 0),
                    'new_quantity' => 0,
                ];
            })->toArray();

            $this->branchAllocations = collect($this->quantityAllocations)->map(fn($a) => [
                'branch_name' => $a['branch_name'],
                'count' => $a['current_allocated'],
            ])->toArray();
        }
    }

    public function toggleSelectAll()
    {
        $this->selectAll = !$this->selectAll;
        $availableIds = collect($this->devices)->whereNull('branch_id')->pluck('id')->toArray();
        $this->selectedDevices = $this->selectAll ? $availableIds : [];
    }

    public function toggleDevice($id)
    {
        if (in_array($id, $this->selectedDevices)) {
            $this->selectedDevices = array_values(array_filter($this->selectedDevices, fn($d) => $d != $id));
            $this->selectAll = false;
        } else {
            $this->selectedDevices[] = $id;
        }
    }

    public function assign()
    {
        $requiresDevice = $this->selectedProduct->category?->requires_device_registration ?? false;

        if ($requiresDevice) {
            $hasAvailableDevices = Device::where('product_id', $this->selectedProductId)
                ->whereNull('branch_id')->exists();

            if (!$hasAvailableDevices) {
                $this->dispatch('show-toast', type: 'error', message: 'Este producto requiere registro de MAC. No hay dispositivos disponibles para distribuir. Registrá las MACs en /devices primero.');
                return;
            }

            $this->assignDevices();
        } else {
            $this->assignQuantity();
        }
    }

    private function assignDevices()
    {
        if (!$this->targetBranchId) {
            $this->dispatch('show-toast', type: 'error', message: 'Seleccioná una sucursal de destino.');
            return;
        }

        if (empty($this->selectedDevices)) {
            $this->dispatch('show-toast', type: 'error', message: 'Seleccioná al menos un dispositivo.');
            return;
        }

        $devicesToAssign = Device::whereIn('id', $this->selectedDevices)
            ->whereNull('branch_id')
            ->get();

        if ($devicesToAssign->isEmpty()) {
            $this->dispatch('show-toast', type: 'error', message: 'Los dispositivos seleccionados ya no están disponibles.');
            return;
        }

        $branch = Branch::find($this->targetBranchId);
        $count = $devicesToAssign->count();

        foreach ($devicesToAssign as $device) {
            $device->update([
                'branch_id' => $this->targetBranchId,
                'status' => 'in_stock',
            ]);
        }

        BranchInventory::firstOrCreate([
            'branch_id' => $this->targetBranchId,
            'product_id' => $this->selectedProductId,
        ])->increment('allocated_quantity', $count);

        Movement::create([
            'product_id' => $this->selectedProductId,
            'type' => 'branch_allocation',
            'quantity' => $count,
            'unit_cost' => $this->selectedProduct->average_cost ?? 0,
            'total_value' => ($count * ($this->selectedProduct->average_cost ?? 0)),
            'description' => 'Repartición a '.($branch?->name ?? 'Sucursal'),
            'user_id' => Auth::id(),
            'branch_id' => $this->targetBranchId,
        ]);

        $this->loadProductData();
        $this->dispatch('show-toast', type: 'success', message: $count . ' dispositivo(s) asignado(s) a ' . ($branch?->name ?? 'la sucursal'));
    }

    private function assignQuantity()
    {
        $sum = array_sum(array_map('floatval', array_column($this->quantityAllocations, 'new_quantity')));

        if ($sum <= 0) {
            $this->dispatch('show-toast', type: 'error', message: 'Ingresá al menos una cantidad para repartir.');
            return;
        }

        if ($sum > $this->available) {
            $this->dispatch('show-toast', type: 'error', message: "La suma a repartir ({$sum}) supera el disponible ({$this->available}).");
            return;
        }

        foreach ($this->quantityAllocations as $alloc) {
            if ($alloc['new_quantity'] > 0) {
                BranchInventory::firstOrCreate([
                    'branch_id' => $alloc['branch_id'],
                    'product_id' => $this->selectedProductId,
                ])->increment('allocated_quantity', $alloc['new_quantity']);

                $branch = Branch::find($alloc['branch_id']);

                Movement::create([
                    'product_id' => $this->selectedProductId,
                    'type' => 'branch_allocation',
                    'quantity' => $alloc['new_quantity'],
                    'unit_cost' => $this->selectedProduct->average_cost ?? 0,
                    'total_value' => ($alloc['new_quantity'] * ($this->selectedProduct->average_cost ?? 0)),
                    'description' => 'Repartición a '.($branch?->name ?? 'Sucursal'),
                    'user_id' => Auth::id(),
                    'branch_id' => $alloc['branch_id'],
                ]);
            }
        }

        $this->loadProductData();
        $this->dispatch('show-toast', type: 'success', message: 'Repartición guardada correctamente.');
    }

    public function render()
    {
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $requiresDevice = $this->selectedProduct?->category?->requires_device_registration ?? false;
        return view('livewire.inventory.distribution-form', compact('branches', 'requiresDevice'))->layout('components.layouts.app');
    }
}
