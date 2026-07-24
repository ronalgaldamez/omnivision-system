<?php

namespace App\Livewire\Public;

use Livewire\Component;
use App\Models\Client;

class SignContract extends Component
{
    public $token;
    public $client = null;
    public $valid = false;
    public $alreadySigned = false;
    public $expired = false;
    public $error = null;

    public $signatureData = null;

    public function mount($token)
    {
        $this->token = $token;

        $this->client = Client::where('signature_token', $token)->first();

        if (!$this->client) {
            $this->error = 'El enlace de firma no es válido o ha expirado.';
            return;
        }

        if ($this->client->signature_token_expires_at && $this->client->signature_token_expires_at->isPast()) {
            $this->expired = true;
            return;
        }

        if ($this->client->client_signature_data) {
            $this->alreadySigned = true;
            return;
        }

        $this->valid = true;
    }

    public function saveSignature($signatureData)
    {
        if (!$signatureData || !is_string($signatureData)) {
            $this->error = 'No se recibieron datos de firma válidos.';
            return;
        }

        $client = Client::where('signature_token', $this->token)->first();

        if (!$client || $client->client_signature_data) {
            $this->error = 'Esta firma ya fue registrada o el enlace no es válido.';
            return;
        }

        $client->update([
            'client_signature_data' => $signatureData,
            'signature_token' => null,
            'signature_token_expires_at' => null,
        ]);

        $this->alreadySigned = true;
        $this->valid = false;
        $this->dispatch('show-toast', type: 'success', message: 'Firma registrada correctamente.');
    }

    public function render()
    {
        return view('livewire.public.contract-signature')
            ->layout('components.layouts.blank');
    }
}
