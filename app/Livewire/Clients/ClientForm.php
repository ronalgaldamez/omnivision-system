<?php

namespace App\Livewire\Clients;

use Livewire\Component;
use App\Models\Client;

class ClientForm extends Component
{
    public $name = '';
    public $phone = '';
    public $address = '';
    public $service_contracted = '';

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'service_contracted' => 'nullable|string',
        ]);

        $client = Client::create([
            'name' => $this->name,
            'phone' => $this->phone,
            'address' => $this->address,
            'service_contracted' => $this->service_contracted,
        ]);

        $this->dispatch('clientCreated', $client->id, $client->name);
        $this->dispatch('showToast', ['type' => 'success', 'message' => 'Cliente creado correctamente.']);
        $this->reset(['name', 'phone', 'address', 'service_contracted']);
    }

    public function render()
    {
        return view('livewire.clients.client-form');
    }
}