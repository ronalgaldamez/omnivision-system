<?php

namespace App\Livewire\Technicians;

use Livewire\Component;
use App\Models\Product;
use App\Models\TechnicianRequest;
use App\Models\RequestProduct;
use Illuminate\Support\Facades\Auth;

class TechnicianRequestForm extends Component
{
    public $notes = '';
    public $items = []; // cada item: product_id, quantity_requested

    protected $rules = [
        'notes' => 'nullable|string',
        'items' => 'required|array|min:1',
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.quantity_requested' => 'required|integer|min:1',
    ];

    public function mount()
    {
        $this->items = [];
    }

    public function addItem()
    {
        $this->items[] = ['product_id' => '', 'quantity_requested' => 1];
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function save()
    {
        $this->validate();

        $request = TechnicianRequest::create([
            'technician_id' => Auth::id(),
            'status' => 'pending',
            'notes' => $this->notes,
        ]);

        foreach ($this->items as $item) {
            RequestProduct::create([
                'technician_request_id' => $request->id,
                'product_id' => $item['product_id'],
                'quantity_requested' => $item['quantity_requested'],
            ]);
        }

        session()->flash('message', 'Solicitud enviada correctamente.');
        return redirect()->route('technician-requests.index');
    }

    public function render()
    {
        $products = Product::orderBy('name')->get();
        return view('livewire.technicians.request-form', compact('products'));
    }
}