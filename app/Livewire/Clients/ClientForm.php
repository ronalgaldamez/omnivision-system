<?php

namespace App\Livewire\Clients;

use Livewire\Component;
use App\Models\Client;
use Illuminate\Support\Facades\DB;

class ClientForm extends Component
{
    public $name = '';
    public $document_type = null;
    public $document_number = '';
    public $email = '';
    public $phone = '';
    public $address = '';
    public $latitude = null;
    public $longitude = null;
    public $nro_luz = '';
    public $installation_address = '';
    public $service = '';
    public $notes = '';

    public $phones = [];

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'document_type' => 'nullable|in:dui,cedula,ruc,pasaporte',
            'document_number' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'nro_luz' => 'nullable|string|max:50',
            'installation_address' => 'nullable|string',
            'service' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'phones' => 'nullable|array',
            'phones.*.number' => 'required_with:phones.*.number|string|max:20',
            'phones.*.type' => 'nullable|in:personal,casa,referencia,trabajo,otro',
        ];
    }

    public function mount()
    {
        $this->phones = [];
    }

    public function addPhone()
    {
        $this->phones[] = ['number' => '', 'type' => 'personal'];
    }

    public function removePhone($index)
    {
        unset($this->phones[$index]);
        $this->phones = array_values($this->phones);
    }

    public function save()
    {
        $this->latitude = is_numeric($this->latitude) ? (float) $this->latitude : null;
        $this->longitude = is_numeric($this->longitude) ? (float) $this->longitude : null;

        $errors = [];
        if ($this->latitude !== null) {
            $latStr = (string) $this->latitude;
            if (!preg_match('/^-?\d{1,2}\.\d+$/', $latStr)) {
                $errors[] = 'Latitud: debe tener punto decimal (ej. 13.6929).';
            } elseif ($this->latitude < -90 || $this->latitude > 90) {
                $errors[] = 'Latitud: valor fuera de rango (-90 a 90).';
            }
        }
        if ($this->longitude !== null) {
            $lonStr = (string) $this->longitude;
            if (!preg_match('/^-?\d{1,3}\.\d+$/', $lonStr)) {
                $errors[] = 'Longitud: debe tener punto decimal (ej. -89.1825).';
            } elseif ($this->longitude < -180 || $this->longitude > 180) {
                $errors[] = 'Longitud: valor fuera de rango (-180 a 180).';
            }
        }
        if (!empty($errors)) {
            $this->dispatch('show-toast', type: 'error', message: implode(' ', $errors));
            return;
        }

        $this->validate();

        $client = Client::create([
            'name' => $this->name,
            'document_type' => $this->document_type,
            'document_number' => $this->document_number,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'nro_luz' => $this->nro_luz,
            'installation_address' => $this->installation_address,
            'service' => $this->service,
            'notes' => $this->notes,
        ]);

        DB::transaction(function () use ($client) {
            foreach ($this->phones as $phone) {
                if (!empty($phone['number'])) {
                    $client->phones()->create([
                        'number' => $phone['number'],
                        'type' => $phone['type'] ?? 'personal',
                    ]);
                }
            }
        });

        // Dispatch global — funciona para cualquier componente que escuche con #[On('clientCreated')]
        $this->dispatch('clientCreated',
            id: $client->id,
            name: $client->name,
            phone: $client->phone
        );
    }

    public function render()
    {
        return view('livewire.clients.client-form');
    }
}