<?php

namespace App\Livewire\Tickets;

use Livewire\Component;
use App\Models\Client;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;

class TicketForm extends Component
{
    public $description;
    public $client_id;
    public $service_type;
    public $requires_noc = false;

    // Búsqueda de clientes
    public $clientSearch = '';
    public $clientSearchResults = [];

    // Modal de cliente
    public $showClientModal = false;

    protected $rules = [
        'client_id' => 'required|exists:clients,id',
        'description' => 'required|string|min:5',
        'service_type' => 'required|string|in:instalacion,traslado,revision,cobro_pendiente,reconexion,desconexion',
        'requires_noc' => 'boolean',
    ];

    public function updatedClientSearch()
    {
        if (strlen($this->clientSearch) >= 2) {
            $this->clientSearchResults = Client::where('name', 'like', '%' . $this->clientSearch . '%')
                ->orWhere('phone', 'like', '%' . $this->clientSearch . '%')
                ->limit(10)
                ->get();
        } else {
            $this->clientSearchResults = [];
        }
    }

    public function selectClient($id, $name)
    {
        $this->client_id = $id;
        $this->clientSearch = $name;
        $this->clientSearchResults = [];
    }

    public function openClientModal()
    {
        $this->showClientModal = true;
    }

    public function closeClientModal()
    {
        $this->showClientModal = false;
    }

    public function save()
    {
        $this->validate();

        $ticket = Ticket::create([
            'client_id' => $this->client_id,
            'description' => $this->description,
            'service_type' => $this->service_type,
            'requires_noc' => $this->requires_noc,
            'created_by' => Auth::id(),
            'status' => 'pending',
        ]);

        // Si no requiere NOC, crear OT automáticamente
        if (!$this->requires_noc) {
            $this->createWorkOrder($ticket);
        }

        $this->dispatch('showToast', ['type' => 'success', 'message' => 'Ticket creado correctamente.']);
        return redirect()->route('tickets.index');
    }

    protected function createWorkOrder($ticket)
    {
        \App\Models\WorkOrder::create([
            'ticket_id' => $ticket->id,
            'client_id' => $ticket->client_id,
            'description' => $ticket->description,
            'service_type' => $ticket->service_type,
            'status' => 'pending_assignment',
        ]);
    }

    public function render()
    {
        return view('livewire.tickets.ticket-form')->layout('components.layouts.app');
    }
}