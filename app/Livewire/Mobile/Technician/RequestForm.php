<?php

namespace App\Livewire\Mobile\Technician;

use Livewire\Component;
use App\Models\Product;
use App\Models\WorkOrder;
use App\Models\TechnicianRequest;
use App\Models\RequestProduct;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;

class RequestForm extends Component
{
    public $requestId;
    public $workOrderId;
    public $notes = '';
    public $items = [];

    public $workOrders = [];

    protected function rules()
    {
        $otRequired = Setting::get('ot_required', 'false') === 'true';
        $rules = [
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity_requested' => 'required|integer|min:1',
        ];
        if ($otRequired) {
            $rules['workOrderId'] = 'required|exists:work_orders,id';
        } else {
            $rules['workOrderId'] = 'nullable|exists:work_orders,id';
        }
        return $rules;
    }

    public function mount($id = null, $work_order_id = null)
    {
        $this->requestId = $id;
        $this->workOrders = WorkOrder::where('technician_id', Auth::id())
            ->whereIn('status', ['pending', 'in_progress'])
            ->orderBy('scheduled_date')
            ->get();

        if ($work_order_id && !$id) {
            $exists = $this->workOrders->contains('id', $work_order_id);
            if ($exists) {
                $this->workOrderId = $work_order_id;
            }
        }

        if ($id) {
            $request = TechnicianRequest::where('id', $id)
                ->where('technician_id', Auth::id())
                ->where('status', 'pending')
                ->firstOrFail();
            $this->workOrderId = $request->work_order_id;
            $this->notes = $request->notes;
            foreach ($request->products as $rp) {
                $this->items[] = [
                    'product_id' => $rp->product_id,
                    'quantity_requested' => $rp->quantity_requested,
                ];
            }
        } else {
            $this->items = [];
            if (!$work_order_id) {
                $this->workOrderId = null;
            }
        }
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
        // Convertir cadena vacía a null
        if ($this->workOrderId === '') {
            $this->workOrderId = null;
        }

        $this->validate();

        if ($this->requestId) {
            $request = TechnicianRequest::where('id', $this->requestId)
                ->where('technician_id', Auth::id())
                ->where('status', 'pending')
                ->firstOrFail();
            $request->update([
                'work_order_id' => $this->workOrderId,
                'notes' => $this->notes,
            ]);
            $request->products()->delete();
            foreach ($this->items as $item) {
                RequestProduct::create([
                    'technician_request_id' => $request->id,
                    'product_id' => $item['product_id'],
                    'quantity_requested' => $item['quantity_requested'],
                ]);
            }
            session()->flash('message', 'Solicitud actualizada.');
        } else {
            $request = TechnicianRequest::create([
                'technician_id' => Auth::id(),
                'work_order_id' => $this->workOrderId,
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
            session()->flash('message', 'Solicitud creada. Código QR generado.');
        }

        return redirect()->route('mobile.technician.requests');
    }

    public function render()
    {
        $products = Product::orderBy('name')->get();
        return view('livewire.mobile.technician.request-form', compact('products'));
    }
}