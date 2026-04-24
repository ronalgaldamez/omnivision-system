<?php

namespace App\Livewire\Technicians;

use Livewire\Component;
use App\Models\TechnicianRequest;
use App\Models\Movement;
use App\Models\Product;
use App\Services\InventoryService;
use Illuminate\Support\Facades\Auth;

class RequestApprovalForm extends Component
{
    public $requestId;
    public $request;

    public function mount($id)
    {
        $this->requestId = $id;
        $this->request = TechnicianRequest::with('products.product')->findOrFail($id);
    }

    public function approve()
    {
        if ($this->request->status !== 'pending') {
            session()->flash('error', 'Esta solicitud ya fue procesada.');
            return redirect()->route('technician-requests.index');
        }

        $insufficient = [];
        foreach ($this->request->products as $rp) {
            $product = $rp->product;
            if ($product->current_stock < $rp->quantity_requested) {
                $insufficient[] = $product->name . " (stock: {$product->current_stock}, requiere: {$rp->quantity_requested})";
            }
        }

        if (count($insufficient) > 0) {
            session()->flash('error', 'Stock insuficiente: ' . implode(', ', $insufficient));
            return redirect()->route('technician-requests.index');
        }

        if (!$this->request->request_code) {
            $this->request->request_code = $this->request->generateUniqueCode();
        }

        $inventoryService = new InventoryService();

        foreach ($this->request->products as $rp) {
            $product = $rp->product;
            $movement = Movement::create([
                'product_id' => $rp->product_id,
                'type' => 'technician_out',
                'quantity' => $rp->quantity_requested,
                'description' => 'Aprobación de solicitud #' . $this->request->id,
                'user_id' => Auth::id(),
                'reference_type' => 'technician_request',
                'reference_id' => $this->request->id,
            ]);
            $inventoryService->processExit($product, $rp->quantity_requested, $movement);
            $rp->quantity_delivered = $rp->quantity_requested;
            $rp->save();
        }

        $this->request->status = 'delivered';
        $this->request->save();

        session()->flash('message', 'Solicitud aprobada y materiales entregados.');
        return redirect()->route('technician-requests.index');
    }

    public function reject()
    {
        if ($this->request->status !== 'pending') {
            session()->flash('error', 'Esta solicitud ya fue procesada.');
            return redirect()->route('technician-requests.index');
        }

        $this->request->status = 'rejected';
        $this->request->save();

        session()->flash('message', 'Solicitud rechazada.');
        return redirect()->route('technician-requests.index');
    }

    public function render()
    {
        return view('livewire.technicians.request-approval')->layout('components.layouts.app');
    }
}