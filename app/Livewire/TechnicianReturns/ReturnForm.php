<?php

namespace App\Livewire\TechnicianReturns;

use Livewire\Component;
use App\Models\Product;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\TechnicianRequest;
use App\Models\TechnicianReturn;
use App\Models\Movement;
use App\Services\InventoryService;
use Illuminate\Support\Facades\Auth;

class ReturnForm extends Component
{
    public $type = 'surplus';
    public $technician_id = null;        // nuevo: para filtro por técnico (solo admin/warehouse)
    public $work_order_id;
    public $product_id;
    public $quantity;
    public $notes;
    public $productSearch = '';
    public $selectedRequestId = null;

    public $technicians = [];             // lista de técnicos (para admin/warehouse)
    public $workOrders = [];
    public $availableProducts = [];

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
        // Si es técnico, solo verá sus propias OT (sin selector de técnico)
        if ($user->hasRole('technician')) {
            $this->technician_id = $user->id;
            $this->loadWorkOrders();
        } else {
            // Admin o warehouse: cargar lista de técnicos
            $this->technicians = User::role('technician')->orderBy('name')->get();
        }
    }

    public function updatedTechnicianId()
    {
        $this->work_order_id = null;
        $this->product_id = null;
        $this->availableProducts = [];
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
        $this->selectedRequestId = null;
        $this->availableProducts = [];

        if ($this->work_order_id) {
            $requests = TechnicianRequest::where('work_order_id', $this->work_order_id)
                ->where('status', 'delivered')
                ->with('products.product')
                ->get();

            $products = [];
            foreach ($requests as $req) {
                foreach ($req->products as $rp) {
                    $alreadyReturned = TechnicianReturn::where('technician_request_id', $req->id)
                        ->where('product_id', $rp->product_id)
                        ->sum('quantity');
                    $available = $rp->quantity_delivered - $alreadyReturned;
                    if ($available > 0) {
                        $products[] = [
                            'product_id' => $rp->product_id,
                            'product_name' => $rp->product->name,
                            'product_sku' => $rp->product->sku,
                            'available' => $available,
                            'request_id' => $req->id,
                        ];
                    }
                }
            }
            $this->availableProducts = $products;
        }
    }

    public function selectProduct($productId, $requestId, $available)
    {
        $this->product_id = $productId;
        $this->selectedRequestId = $requestId;
        $this->dispatch('showToast', ['type' => 'info', 'message' => "Producto seleccionado. Cantidad máxima a devolver: $available"]);
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
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'Stock insuficiente para registrar dañado. Stock actual: ' . $product->current_stock]);
            $this->showConfirmModal = false;
            return;
        }

        // Obtener la solicitud asociada
        $technicianRequest = TechnicianRequest::where('work_order_id', $this->work_order_id)
            ->whereHas('products', function ($q) {
                $q->where('product_id', $this->product_id);
            })->first();

        $return = TechnicianReturn::create([
            'technician_request_id' => $technicianRequest ? $technicianRequest->id : null,
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

        $this->dispatch('showToast', ['type' => 'success', 'message' => 'Devolución registrada correctamente.']);
        $this->reset(['technician_id', 'work_order_id', 'product_id', 'quantity', 'notes', 'selectedRequestId']);
        $this->availableProducts = [];
        $this->showConfirmModal = false;
        return redirect()->route('technician-returns.index');
    }

    public function render()
    {
        return view('livewire.technician-returns.return-form')->layout('components.layouts.app');
    }
}