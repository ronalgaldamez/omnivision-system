<?php

namespace App\Traits;

use App\Models\PackagingType;
use App\Models\Product;
use App\Models\ProductPackaging;

trait ManagesProductPackaging
{
    public $currentProductId = '';
    public $currentPackagingId = '';
    public $currentPackagings = [];
    public $newPackagingTypeId = '';
    public $newPackagingQuantity = 1;
    public $editingPackagingId = null;
    public $showPackagingForm = false;
    public $packagingTypes = [];

    protected function initPackagingState(): void
    {
        $this->packagingTypes = PackagingType::orderBy('name')->get();
    }

    public function updatedCurrentPackagingId(): void {}

    public function getSelectedPackagingProperty()
    {
        if (! $this->currentPackagingId) {
            return null;
        }
        return collect($this->currentPackagings)->firstWhere('id', $this->currentPackagingId);
    }

    public function loadPackagingsForProduct($productId): void
    {
        $product = Product::with('packagings')->find($productId);
        if ($product) {
            $this->currentPackagings = $product->packagings;
            $default = $product->packagings->firstWhere('is_default_for_purchase', true);
            $this->currentPackagingId = $default ? $default->id : ($product->packagings->first()?->id ?? '');
        }
    }

    public function savePackaging(): void
    {
        $this->validate([
            'newPackagingTypeId' => 'required|exists:packaging_types,id',
            'newPackagingQuantity' => 'required|numeric|min:1',
        ]);

        $type = PackagingType::find($this->newPackagingTypeId);
        $name = $type->name.' x'.rtrim(rtrim(number_format($this->newPackagingQuantity, 4), '0'), '.');

        if ($this->editingPackagingId) {
            $pkg = ProductPackaging::find($this->editingPackagingId);
            if ($pkg && $pkg->product_id == $this->currentProductId) {
                $pkg->update([
                    'packaging_type_id' => $type->id,
                    'name' => $name,
                    'quantity_in_base_unit' => $this->newPackagingQuantity,
                ]);
            }
        } else {
            ProductPackaging::where('product_id', $this->currentProductId)
                ->update(['is_default_for_purchase' => false]);

            ProductPackaging::create([
                'product_id' => $this->currentProductId,
                'packaging_type_id' => $type->id,
                'name' => $name,
                'quantity_in_base_unit' => $this->newPackagingQuantity,
                'is_default_for_purchase' => true,
            ]);
        }

        $this->loadPackagingsForProduct($this->currentProductId);
        $this->editingPackagingId = null;
        $this->showPackagingForm = false;
        $this->currentPackagingId = $this->currentPackagings->last()?->id ?? '';
        $this->newPackagingTypeId = '';
        $this->newPackagingQuantity = 1;
        $this->dispatch('show-toast', type: 'success', message: 'Empaque guardado.');
    }

    public function editPackaging($id): void
    {
        $this->showPackagingForm = true;
        if ($id) {
            $pkg = ProductPackaging::find($id);
            if ($pkg && $pkg->product_id == $this->currentProductId) {
                $this->editingPackagingId = $pkg->id;
                $this->newPackagingTypeId = $pkg->packaging_type_id;
                $this->newPackagingQuantity = (float) $pkg->quantity_in_base_unit;
            }
        } else {
            $this->editingPackagingId = null;
            $this->newPackagingTypeId = '';
            $this->newPackagingQuantity = 1;
        }
    }

    public function cancelEditPackaging(): void
    {
        $this->editingPackagingId = null;
        $this->showPackagingForm = false;
        $this->newPackagingTypeId = '';
        $this->newPackagingQuantity = 1;
    }

    public function deletePackaging($id): void
    {
        $pkg = ProductPackaging::find($id);
        if ($pkg && $pkg->product_id == $this->currentProductId) {
            $pkg->delete();
            $this->loadPackagingsForProduct($this->currentProductId);
            $this->dispatch('show-toast', type: 'success', message: 'Empaque eliminado.');
        }
    }
}
