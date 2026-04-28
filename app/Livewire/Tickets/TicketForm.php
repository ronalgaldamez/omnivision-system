<?php

namespace App\Livewire\Tickets;

use Livewire\Component;
use App\Models\Client;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;

class TicketForm extends Component
{
    public $ticketId;
    public $description;
    public $client_id;
    public $service_type;
    public $requires_noc = false;
    public $status = 'pending';   // para edición

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
        'status' => 'in:pending,in_progress,resolved,closed',
    ];

    public function mount($id = null)
    {
        $user = Auth::user();

        if ($id) {
            // Modo edición
            $this->ticketId = $id;
            $ticket = Ticket::findOrFail($id);

            // Autorización para editar: necesita permiso 'update tickets' O ser el creador si tiene 'update own tickets'? Usamos 'update tickets' directamente.
            if ($user->cannot('update tickets')) {
                abort(403, 'No tienes permiso para editar tickets.');
            }

            $this->client_id = $ticket->client_id;
            $this->description = $ticket->description;
            $this->service_type = $ticket->service_type;
            $this->requires_noc = $ticket->requires_noc;
            $this->status = $ticket->status;
            $this->clientSearch = $ticket->client->name;
        } else {
            // Modo creación
            if ($user->cannot('create tickets')) {
                abort(403, 'No tienes permiso para crear tickets.');
            }
        }
    }

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

        $data = [
            'client_id' => $this->client_id,
            'description' => $this->description,
            'service_type' => $this->service_type,
            'requires_noc' => $this->requires_noc,
        ];

        if ($this->ticketId) {
            // Actualizar
            $ticket = Ticket::findOrFail($this->ticketId);
            $data['status'] = $this->status;
            $ticket->update($data);
            $this->dispatch('showToast', ['type' => 'success', 'message' => 'Ticket actualizado.']);
        } else {
            // Crear
            $data['created_by'] = Auth::id();
            $data['status'] = 'pending';
            $ticket = Ticket::create($data);

            // Si no requiere NOC, crear OT automáticamente
            if (!$this->requires_noc) {
                $this->createWorkOrder($ticket);
            }
            $this->dispatch('showToast', ['type' => 'success', 'message' => 'Ticket creado correctamente.']);
        }

        return redirect()->route('tickets.index');
    }

    protected function createWorkOrder($ticket)
    {
        \App\Models\WorkOrder::create([
            'ticket_id' => $ticket->id,
            'client_id' => $ticket->client_id,
            'description' => $ticket->description,
            'service_type' => $ticket->service_type,
            'status' => 'pending',
        ]);
    }

    public function render()
    {
        return view('livewire.tickets.ticket-form')->layout('components.layouts.app');
    }
}