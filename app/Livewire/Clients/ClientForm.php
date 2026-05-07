<?php

namespace App\Livewire\Clients;

use Livewire\Component;
use App\Models\Client;
use App\Models\ClientPhone;

class ClientForm extends Component
{
    public $name = '';
    public $address = '';
    public $service_contracted = '';

    // Array dinámico de teléfonos
    public $phones = [];

    public function mount()
    {
        // Inicializar con un campo de teléfono personal
        $this->phones = [
            ['number' => '', 'type' => 'personal']
        ];
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
        $this->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'service_contracted' => 'nullable|string',
            'phones' => 'array|min:1',
            'phones.*.number' => 'required|string|max:20',
            'phones.*.type' => 'nullable|string|max:20',
        ], [
            'phones.*.number.required' => 'El número de teléfono es obligatorio.',
        ]);

        $client = Client::create([
            'name' => $this->name,
            'address' => $this->address,
            'service_contracted' => $this->service_contracted,
            // guardamos el primer teléfono en el campo legacy 'phone' por compatibilidad
            'phone' => $this->phones[0]['number'],
        ]);

        foreach ($this->phones as $phone) {
            ClientPhone::create([
                'client_id' => $client->id,
                'number' => $phone['number'],
                'type' => $phone['type'] ?? 'personal',
            ]);
        }

        $this->dispatch('clientCreated', $client->id, $client->name);
        $this->dispatch('showToast', ['type' => 'success', 'message' => 'Cliente creado correctamente.']);

        // Resetear el formulario
        $this->reset(['name', 'address', 'service_contracted']);
        $this->phones = [
            ['number' => '', 'type' => 'personal']
        ];
    }

    public function render()
    {
        return view('livewire.clients.client-form');
    }
}