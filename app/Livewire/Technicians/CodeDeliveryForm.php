<?php

namespace App\Livewire\Technicians;

use Livewire\Component;
use App\Models\TechnicianRequest;

class CodeDeliveryForm extends Component
{
    public $code = '';

    protected $rules = [
        'code' => 'required|string|exists:technician_requests,request_code',
    ];

    public function search()
    {
        $this->validate();

        $request = TechnicianRequest::where('request_code', $this->code)->first();

        if (!$request) {
            $this->addError('code', 'Código no encontrado.');
            return;
        }

        if ($request->status !== 'pending') {
            $this->addError('code', 'Esta solicitud ya fue procesada (estado: ' . $request->status . ').');
            return;
        }

        // Redirigir a la vista de aprobación
        return redirect()->route('technician-requests.approve', $request->id);
    }

    public function render()
    {
        return view('livewire.technicians.code-delivery-form')->layout('components.layouts.app');
    }
}