<?php

namespace App\Livewire\Public;

use Livewire\Component;
use App\Models\Client;
use App\Models\Contract;

class CaptureCoordinates extends Component
{
    public $token;
    public $client;
    public $latitude = '';
    public $longitude = '';
    public $captured = false;
    public $expired = false;
    public $error = null;
    public $privacyAccepted = false;
    public $showManualMode = false;

    protected $queryString = ['token'];

    public function mount()
    {
        $this->client = Client::where('gps_token', $this->token)->first();

        if (!$this->client) {
            abort(404, 'Enlace inválido o expirado.');
        }

        // Verificar si el enlace expiró (solo si NO tiene coordenadas capturadas aún)
        if (!$this->client->latitude && !$this->client->longitude) {
            if ($this->client->gps_token_expires_at && $this->client->gps_token_expires_at->isPast()) {
                $this->expired = true;
                return;
            }
        }

        // Si ya tiene coordenadas, mostrarlas
        if ($this->client->latitude && $this->client->longitude) {
            $this->latitude = $this->client->latitude;
            $this->longitude = $this->client->longitude;
            $this->captured = true;
        }
    }

    public function saveCoordinates($lat, $lng)
    {
        $this->client->update([
            'latitude' => $lat,
            'longitude' => $lng,
        ]);

        // También actualizar el contrato si existe uno asociado
        Contract::where('client_id', $this->client->id)
            ->whereNull('latitude')
            ->update([
                'latitude' => $lat,
                'longitude' => $lng,
            ]);

        $this->latitude = $lat;
        $this->longitude = $lng;
        $this->captured = true;
        $this->error = null;

        $this->dispatch('coordinates-saved');
    }

    public function saveManual()
    {
        $this->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $this->saveCoordinates($this->latitude, $this->longitude);
    }

    public function enableManualMode()
    {
        $this->showManualMode = true;
        $this->error = null;
    }

    public function render()
    {
        return view('livewire.public.capture-coordinates')
            ->layout('components.layouts.blank');
    }
}
