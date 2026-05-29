<?php

namespace App\Livewire\Admin\Clients;

use Livewire\Component;
use App\Models\Client;
use App\Models\ClientPhone;
use Illuminate\Support\Facades\DB;

class ClientForm extends Component
{
    public $clientId = null;
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
            'phones.*.number' => 'required_with:phones|string|max:20',
            'phones.*.type' => 'nullable|in:personal,casa,referencia,trabajo,otro',
        ];
    }

    public function mount($id = null)
    {
        if ($id) {
            $this->clientId = $id;
            $client = Client::with('phones')->findOrFail($id);
            $this->name = $client->name;
            $this->document_type = $client->document_type;
            $this->document_number = $client->document_number;
            $this->email = $client->email;
            $this->phone = $client->phone;
            $this->address = $client->address;
            $this->latitude = $client->latitude;
            $this->longitude = $client->longitude;
            $this->nro_luz = $client->nro_luz;
            $this->installation_address = $client->installation_address;
            $this->service = $client->service;
            $this->notes = $client->notes;
            $this->phones = $client->phones->toArray();
        } else {
            $this->phones = [
                ['number' => '', 'type' => 'personal']
            ];
        }
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

        DB::transaction(function () {
            if ($this->clientId) {
                $client = Client::findOrFail($this->clientId);
                if (!auth()->user()->can('edit clients')) {
                    abort(403);
                }
                $client->update([
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
            } else {
                if (!auth()->user()->can('create clients')) {
                    abort(403);
                }
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
            }

            // Sincronizar teléfonos
            $client->phones()->delete();
            foreach ($this->phones as $phone) {
                if (!empty($phone['number'])) {
                    $client->phones()->create([
                        'number' => $phone['number'],
                        'type' => $phone['type'] ?? 'personal',
                    ]);
                }
            }
        });

        session()->flash('message', $this->clientId ? 'Cliente actualizado correctamente.' : 'Cliente creado correctamente.');
        return redirect()->route('admin.clients.index');
    }

    public function render()
    {
        return view('livewire.admin.clients.client-form')->layout('components.layouts.app');
    }
}