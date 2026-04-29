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
    public $status = 'pending';

    // Búsqueda de clientes
    public $clientSearch = '';
    public $clientSearchResults = [];

    // Modal de cliente
    public $showClientModal = false;

    // NUEVO: propiedad para controlar la confirmación antes de guardar
    public $confirmingSave = false;

    protected $rules = [
        'client_id' => 'required|exists:clients,id',
        'description' => 'required|string|min:5',
        'service_type' => 'required|string|in:instalacion,traslado,revision,cobro_pendiente,reconexion,desconexion',
        'requires_noc' => 'boolean',
        'status' => 'in:pending,in_progress,resolved,closed',
    ];

    protected function getListeners()
    {
        return [
            'clientCreated' => 'handleClientCreated',
        ];
    }

    public function mount($id = null)
    {
        $user = Auth::user();

        if ($id) {
            $this->ticketId = $id;
            $ticket = Ticket::findOrFail($id);
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

    public function handleClientCreated($clientId, $clientName)
    {
        $this->selectClient($clientId, $clientName);
        $this->closeClientModal();
    }

    private function generateTicketCode($ticketId)
    {
        $user = Auth::user();
        if ($user->hasRole('secretary')) {
            $prefix = 'TK-S-';
        } elseif ($user->hasRole('noc')) {
            $prefix = 'TK-N-';
        } else {
            $prefix = 'TK-A-';
        }
        return $prefix . $ticketId;
    }

    // NUEVO: método que valida los datos y activa la confirmación
    public function promptSave()
    {
        $this->validate(); // validamos antes de mostrar el modal

        // Si se pasa la validación, mostramos el modal de confirmación
        $this->confirmingSave = true;
    }

    // NUEVO: método que se ejecuta al confirmar en el modal
    public function executeSave()
    {
        $this->confirmingSave = false;
        $this->save();
    }

    // NUEVO: cancelar la confirmación
    public function cancelSave()
    {
        $this->confirmingSave = false;
    }

    // Método save ORIGINAL (sin cambios en su lógica, solo se quitó session()->flash)
    public function save()
    {
        $data = [
            'client_id' => $this->client_id,
            'description' => $this->description,
            'service_type' => $this->service_type,
            'requires_noc' => $this->requires_noc,
        ];

        if ($this->ticketId) {
            $ticket = Ticket::findOrFail($this->ticketId);
            $data['status'] = $this->status;
            $ticket->update($data);
            session()->flash('message', 'Ticket actualizado correctamente.');
        } else {
            $data['created_by'] = Auth::id();
            $data['status'] = 'pending';
            $ticket = Ticket::create($data);

            // Generar código de asistencia
            $ticket->ticket_code = $this->generateTicketCode($ticket->id);
            $ticket->save();

            if (!$this->requires_noc) {
                $this->createWorkOrder($ticket);
            }
            session()->flash('message', 'Ticket creado correctamente. Código: ' . $ticket->ticket_code);
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