<?php

namespace App\Livewire\Suppliers;

use Livewire\Component;
use App\Models\Product;

class QuickProductForm extends Component
{
    public $name = '';
    public $sku = '';
    public $current_stock = 0;
    public $stock_min = 0;
    public $unit_measure = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'sku' => 'nullable|string|max:50|unique:products,sku',
        'current_stock' => 'required|numeric|min:0',
        'stock_min' => 'nullable|numeric|min:0',
        'unit_measure' => 'nullable|string|max:20',
    ];

    public function save()
    {
        $this->validate();

        $product = Product::create([
            'name' => $this->name,
            'sku' => $this->sku ?: $this->generateSku(),
            'current_stock' => $this->current_stock,
            'stock_min' => $this->stock_min,
            'unit_measure' => $this->unit_measure,
        ]);

        // Emitir evento para que el formulario de compra lo sepa
        $this->dispatch('productCreated', productId: $product->id, productName: $product->name);

        // Resetear campos
        $this->reset(['name', 'sku', 'current_stock', 'stock_min', 'unit_measure']);

        // Mostrar toast
        $this->dispatch('show-toast', type: 'success', message: 'Producto creado correctamente.');
    }

    private function generateSku(): string
    {
        return 'PROD-' . strtoupper(uniqid());
    }

    public function render()
    {
        return view('livewire.suppliers.quick-product-form');
    }
}