<?php

namespace App\Livewire\Public;

use Livewire\Component;
use App\Models\Contract;
use App\Models\ContractSignature;
use App\Services\ContractSignatureService;

class SignContract extends Component
{
    public $token;
    public $contract = null;
    public $valid = false;
    public $alreadySigned = false;
    public $error = null;

    public $signatureData = null;

    public function mount($token)
    {
        $this->token = $token;

        $signature = ContractSignature::where('signature_token', $token)
            ->with('contract.client', 'contract.plan')
            ->first();

        if (!$signature) {
            $this->error = 'El enlace de firma no es válido o ha expirado.';
            return;
        }

        if ($signature->signed_at) {
            $this->alreadySigned = true;
            $this->contract = $signature->contract;
            return;
        }

        $this->contract = $signature->contract;
        $this->valid = true;
    }

    public function saveSignature($signatureData)
    {
        $this->validate([
            'signatureData' => 'required|string',
        ]);

        $signature = ContractSignature::where('signature_token', $this->token)->first();

        if (!$signature || $signature->signed_at) {
            $this->error = 'Esta firma ya fue registrada o el enlace no es válido.';
            return;
        }

        $service = app(ContractSignatureService::class);
        $service->saveSignature(
            $this->contract,
            'client',
            $signatureData
        );

        // Invalidar el token
        $signature->update(['signature_token' => null]);

        $this->alreadySigned = true;
        $this->dispatch('show-toast', type: 'success', message: 'Firma registrada correctamente.');
    }

    public function render()
    {
        return view('livewire.public.contract-signature')
            ->layout('components.layouts.blank');
    }
}
