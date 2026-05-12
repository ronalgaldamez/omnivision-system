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
    public $priority = '';
    public $origin = '';
    public $requires_noc = false;
    public $status = 'pending';

    public $clientSearch = '';
    public $clientSearchResults = [];
    public $showClientModal = false;
    public $confirmingSave = false;
    public $canRequestNoc = true;

    protected $rules = [
        'client_id' => 'required|exists:clients,id',
        'description' => 'required|string|min:5',
        'service_type' => 'required|string|in:instalacion,traslado,revision,cobro_pendiente,reconexion,desconexion',
        'priority' => 'required|in:P1,P2,P3,P4',
        'origin' => 'nullable|string|max:100',
        'requires_noc' => 'boolean',
        'status' => 'in:pending,in_progress,resolved,closed',
    ];

    protected $listeners = [
        'clientCreated' => 'handleClientCreated',
    ];

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
            $this->priority = $ticket->priority ?? '';
            $this->origin = $ticket->origin ?? '';
            $this->requires_noc = $ticket->requires_noc;
            $this->status = $ticket->status;
            $this->clientSearch = $ticket->client->name;
        } else {
            if ($user->cannot('create tickets')) {
                abort(403, 'No tienes permiso para crear tickets.');
            }
        }

        if ($user->hasRole('noc')) {
            $this->canRequestNoc = false;
            $this->requires_noc = false;
        }
    }

    public function updatedServiceType($value)
    {
        $priorityMap = [
            'instalacion' => 'P2',
            'traslado' => 'P3',
            'revision' => 'P3',
            'cobro_pendiente' => 'P3',
            'reconexion' => 'P1',
            'desconexion' => 'P2',
        ];
        if (array_key_exists($value, $priorityMap)) {
            $this->priority = $priorityMap[$value];
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

    public function promptSave()
    {
        $this->validate();
        $this->confirmingSave = true;
    }

    public function executeSave()
    {
        $this->confirmingSave = false;
        $this->save();
    }

    public function cancelSave()
    {
        $this->confirmingSave = false;
    }

    public function save()
    {
        $data = [
            'client_id' => $this->client_id,
            'description' => $this->description,
            'service_type' => $this->service_type,
            'priority' => $this->priority,
            'origin' => $this->origin,
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

            $ticket->ticket_code = $this->generateTicketCode($ticket->id);
            $ticket->save();

            if (!$this->requires_noc) {
                $this->createWorkOrder($ticket);
            }

            if ($this->requires_noc) {
                $this->dispatch('ticket-created-for-noc');
                $this->dispatch('show-toast', type: 'info', message: 'Nuevo ticket requiere atención del NOC.');
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