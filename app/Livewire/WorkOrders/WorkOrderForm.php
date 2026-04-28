<?php

namespace App\Livewire\WorkOrders;

use Livewire\Component;
use App\Models\User;
use App\Models\Product;
use App\Models\WorkOrder;
use App\Models\OrderProduct;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;

class WorkOrderForm extends Component
{
    public $orderId;
    public $technician_id;
    public $client_id;
    public $latitude;
    public $longitude;
    public $status = 'pending';
    public $scheduled_date;
    public $notes;

    // Búsqueda de clientes
    public $clientSearch = '';
    public $clientSearchResults = [];

    // Productos de la orden
    public $products = [];
    public $currentProductSearch = '';
    public $currentProductId = '';
    public $currentQuantity = 1;
    public $searchResults = [];

    // Modal para crear cliente
    public $showClientModal = false;

    protected $rules = [
        'technician_id' => 'required|exists:users,id',
        'client_id' => 'required|exists:clients,id',
        'latitude' => 'nullable|numeric|between:-90,90',
        'longitude' => 'nullable|numeric|between:-180,180',
        'status' => 'required|in:pending,in_progress,completed,cancelled',
        'scheduled_date' => 'nullable|date',
        'notes' => 'nullable|string',
    ];

    public function mount($id = null)
    {
        $user = Auth::user();

        // Verificar permiso para asignar técnicos (al crear o editar)
        if ($user->cannot('assign technicians')) {
            abort(403, 'No tienes permiso para asignar técnicos a órdenes de trabajo.');
        }

        if ($id) {
            $order = WorkOrder::with('products')->findOrFail($id);
            $this->orderId = $order->id;
            $this->technician_id = $order->technician_id;
            $this->client_id = $order->client_id;
            if ($order->client) {
                $this->clientSearch = $order->client->name . ' (' . ($order->client->phone ?? 'Sin teléfono') . ')';
            }
            $this->latitude = $order->latitude;
            $this->longitude = $order->longitude;
            $this->status = $order->status;
            $this->scheduled_date = $order->scheduled_date?->format('Y-m-d');
            $this->notes = $order->notes;

            foreach ($order->products as $item) {
                $this->products[] = [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'product_sku' => $item->product->sku,
                    'quantity' => $item->quantity,
                    'unit_cost_at_time' => $item->unit_cost_at_time,
                ];
            }
        } else {
            $this->products = [];
            $this->client_id = null;
        }
    }

    // Búsqueda de clientes
    public function updatedClientSearch()
    {
        if (strlen($this->clientSearch) >= 2) {
            $this->clientSearchResults = Client::where('name', 'like', '%' . $this->clientSearch . '%')
                ->orWhere('phone', 'like', '%' . $this->clientSearch . '%')
                ->limit(10)
                ->get();
        } else {
            $this->clientSearchResults = [];
        }
    }

    public function selectClient($id, $name, $phone = null)
    {
        $this->client_id = $id;
        $this->clientSearch = $name . ($phone ? ' (' . $phone . ')' : '');
        $this->clientSearchResults = [];
    }

    // Modal de cliente
    public function openClientModal()
    {
        $this->showClientModal = true;
    }

    public function closeClientModal()
    {
        $this->showClientModal = false;
    }

    // Productos
    public function updatedCurrentProductSearch()
    {
        if (strlen($this->currentProductSearch) >= 2) {
            $this->searchResults = Product::where('name', 'like', '%' . $this->currentProductSearch . '%')
                ->orWhere('sku', 'like', '%' . $this->currentProductSearch . '%')
                ->limit(10)
                ->get();
        } else {
            $this->searchResults = [];
        }
    }

    public function selectProduct($id)
    {
        $product = Product::find($id);
        if ($product) {
            $this->currentProductId = $product->id;
            $this->currentProductSearch = $product->name . ' (' . $product->sku . ')';
            $this->searchResults = [];
        }
    }

    public function addProduct()
    {
        $this->validate([
            'currentProductId' => 'required|exists:products,id',
            'currentQuantity' => 'required|numeric|min:0.01',
        ]);

        $product = Product::find($this->currentProductId);

        $this->products[] = [
            'product_id' => $this->currentProductId,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'quantity' => $this->currentQuantity,
            'unit_cost_at_time' => $product->current_stock > 0 ? $product->movements()->latest()->value('unit_cost') : null,
        ];

        $this->currentProductSearch = '';
        $this->currentProductId = '';
        $this->currentQuantity = 1;
    }

    public function removeProduct($index)
    {
        unset($this->products[$index]);
        $this->products = array_values($this->products);
    }

    public function save()
    {
        $this->validate();

        $orderData = [
            'technician_id' => $this->technician_id,
            'client_id' => $this->client_id,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'status' => $this->status,
            'scheduled_date' => $this->scheduled_date,
            'notes' => $this->notes,
        ];

        if ($this->orderId) {
            $order = WorkOrder::findOrFail($this->orderId);
            $oldStatus = $order->status;
            $order->update($orderData);

            if ($oldStatus !== 'completed' && $this->status === 'completed') {
                $this->completeOrder($order);
            }
            $order->products()->delete();
            foreach ($this->products as $prod) {
                OrderProduct::create([
                    'work_order_id' => $order->id,
                    'product_id' => $prod['product_id'],
                    'quantity' => $prod['quantity'],
                    'unit_cost_at_time' => $prod['unit_cost_at_time'],
                ]);
            }
            session()->flash('message', 'Orden actualizada correctamente.');
        } else {
            $order = WorkOrder::create($orderData);
            foreach ($this->products as $prod) {
                OrderProduct::create([
                    'work_order_id' => $order->id,
                    'product_id' => $prod['product_id'],
                    'quantity' => $prod['quantity'],
                    'unit_cost_at_time' => $prod['unit_cost_at_time'],
                ]);
            }
            session()->flash('message', 'Orden creada correctamente.');
        }

        return redirect()->route('work-orders.index');
    }

    protected function completeOrder(WorkOrder $order)
    {
        foreach ($order->products as $item) {
            $product = $item->product;
            if ($product->current_stock < $item->quantity) {
                session()->flash('error', "Stock insuficiente para {$product->name}. Stock actual: {$product->current_stock}");
                return;
            }
        }

        foreach ($order->products as $item) {
            $product = $item->product;
            \App\Models\Movement::create([
                'product_id' => $product->id,
                'type' => 'technician_out',
                'quantity' => $item->quantity,
                'description' => 'Orden de trabajo #' . $order->id,
                'user_id' => Auth::id(),
                'reference_type' => 'work_order',
                'reference_id' => $order->id,
            ]);
            $product->updateStock($item->quantity, 'technician_out');
            $product->save();
        }
        $order->completed_date = now();
        $order->save();
    }

    public function render()
    {
        $technicians = User::role('technician')->orderBy('name')->get();
        return view('livewire.work-orders.work-order-form', compact('technicians'))->layout('components.layouts.app');
    }
}