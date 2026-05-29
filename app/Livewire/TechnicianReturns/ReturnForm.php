<?php

namespace App\Livewire\TechnicianReturns;

use Livewire\Component;
use App\Models\Product;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\TechnicianReturn;
use App\Models\Movement;
use App\Services\InventoryService;
use Illuminate\Support\Facades\Auth;

class ReturnForm extends Component
{
    public $type = 'surplus';
    public $technician_id = null;
    public $work_order_id;
    public $product_id;
    public $quantity;
    public $notes;
    public $productSearch = '';
    public $selectedRequestId = null; // ya no se usa, pero lo conservamos por si acaso

    public $technicians = [];
    public $workOrders = [];
    public $searchResults = [];       // para el buscador de productos
    public $showConfirmModal = false;
    public $confirmMessage = '';

    protected $rules = [
        'type' => 'required|in:surplus,damage',
        'technician_id' => 'nullable|exists:users,id',
        'work_order_id' => 'required|exists:work_orders,id',
        'product_id' => 'required|exists:products,id',
        'quantity' => 'required|integer|min:1',
        'notes' => 'nullable|string',
    ];

    public function mount()
    {
        $user = Auth::user();
        if ($user->can('assign any technician in returns')) {
            $this->technicians = User::role('technician')->orderBy('name')->get();
        } else {
            // Se autoasigna el técnico actual
            $this->technician_id = $user->id;
            $this->loadWorkOrders();
        }
    }

    public function updatedTechnicianId()
    {
        $this->work_order_id = null;
        $this->product_id = null;
        $this->searchResults = [];
        if ($this->technician_id) {
            $this->loadWorkOrders();
        }
    }

    public function loadWorkOrders()
    {
        if ($this->technician_id) {
            $this->workOrders = WorkOrder::where('technician_id', $this->technician_id)
                ->where('status', '!=', 'cancelled')
                ->orderBy('scheduled_date', 'desc')
                ->get();
        } else {
            $this->workOrders = [];
        }
    }

    public function updatedWorkOrderId()
    {
        $this->product_id = null;
        $this->quantity = null;
        $this->searchResults = [];
    }

    // Buscador de productos (nuevo)
    public function updatedProductSearch()
    {
        if (strlen($this->productSearch) >= 2) {
            $this->searchResults = Product::where('name', 'like', '%' . $this->productSearch . '%')
                ->orWhere('sku', 'like', '%' . $this->productSearch . '%')
                ->limit(10)
                ->get();
        } else {
            $this->searchResults = [];
        }
    }

    public function selectProduct($id, $name)
    {
        $this->product_id = $id;
        $this->productSearch = $name;
        $this->searchResults = [];
    }

    public function confirmSave()
    {
        $this->validate();
        $this->confirmMessage = '¿Estás seguro de registrar esta devolución? El stock se actualizará automáticamente.';
        $this->showConfirmModal = true;
    }

    public function save()
    {
        $product = Product::find($this->product_id);
        $inventoryService = new InventoryService();

        if ($this->type === 'damage' && $product->current_stock < $this->quantity) {
            $this->dispatch('show-toast', type: 'error', message: 'Stock insuficiente para registrar dañado. Stock actual: ' . $product->current_stock);
            $this->showConfirmModal = false;
            return;
        }

        $return = TechnicianReturn::create([
            'technician_request_id' => null,           // ya no depende de una solicitud
            'work_order_id' => $this->work_order_id,
            'type' => $this->type,
            'product_id' => $this->product_id,
            'quantity' => $this->quantity,
            'notes' => $this->notes,
            'user_id' => Auth::id(),
        ]);

        $movement = Movement::create([
            'product_id' => $this->product_id,
            'type' => $this->type === 'surplus' ? 'technician_return' : 'damage',
            'quantity' => $this->quantity,
            'description' => $this->type === 'surplus' ? 'Devolución de sobrante' : 'Reporte de dañado',
            'user_id' => Auth::id(),
            'reference_type' => 'technician_return',
            'reference_id' => $return->id,
        ]);

        if ($this->type === 'surplus') {
            $costToUse = $product->average_cost ?? 0;
            $movement->unit_cost = $costToUse;
            $movement->total_value = $this->quantity * $costToUse;
            $movement->save();
            $inventoryService->processPurchaseEntry($product, $this->quantity, $costToUse, $movement);
        } else {
            $inventoryService->processExit($product, $this->quantity, $movement);
        }

        $this->dispatch('show-toast', type: 'success', message: 'Devolución registrada correctamente.');
        $this->reset(['technician_id', 'work_order_id', 'product_id', 'quantity', 'notes', 'productSearch']);
        $this->searchResults = [];
        $this->showConfirmModal = false;
        return redirect()->route('technician-returns.index');
    }

    public function render()
    {
        return view('livewire.technician-returns.return-form')->layout('components.layouts.app');
    }
}