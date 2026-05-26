<?php

namespace App\Livewire\Clients;

use Livewire\Component;
use App\Models\Client;
use App\Models\ClientPhone;

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
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'nro_luz' => 'nullable|string|max:50',
            'installation_address' => 'nullable|string',
            'service' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'phones' => 'nullable|array',
            'phones.*.number' => 'required_with:phones|string|max:20',
            'phones.*.type' => 'nullable|in:personal,casa,referencia,trabajo,otro',
        ];
    }

    public function mount()
    {
        $this->phones = [['number' => '', 'type' => 'personal']];
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

        foreach ($this->phones as $phone) {
            if (!empty($phone['number'])) {
                $client->phones()->create([
                    'number' => $phone['number'],
                    'type' => $phone['type'] ?? 'personal',
                ]);
            }
        }

        // Emitir evento para que el componente padre (TicketForm / WorkOrderForm) pueda seleccionar el cliente
        $this->dispatch('clientCreated', $client->id, $client->name, $client->phone);
    }

    public function render()
    {
        return view('livewire.clients.client-form');
    }
}