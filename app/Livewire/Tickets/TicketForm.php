<?php

namespace App\Livewire\Tickets;

use Livewire\Component;
use App\Models\Client;
use App\Models\Ticket;
use App\Models\ServiceType;
use Illuminate\Support\Facades\Auth;

class TicketForm extends Component
{
    public $ticketId;
    public $description;
    public $client_id;
    public $service_type_id = '';
    public $priority = '';
    public $origin = '';
    public $requires_noc = false;   // Switch manual (se inicializa según tipo de servicio)
    public $status = 'pending';

    public $clientSearch = '';
    public $clientSearchResults = [];
    public $showClientModal = false;
    public $confirmingSave = false;

    public $knowledgeArticles = [];

    protected $rules = [
        'client_id' => 'required|exists:clients,id',
        'description' => 'required|string|min:5',
        'service_type_id' => 'required|exists:service_types,id',
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
            $this->priority = $ticket->priority ?? '';
            $this->origin = $ticket->origin ?? '';
            $this->requires_noc = $ticket->requires_noc;
            $this->status = $ticket->status;
            $this->clientSearch = $ticket->client->name;

            $serviceType = ServiceType::where('name', $ticket->service_type)->first();
            $this->service_type_id = $serviceType ? $serviceType->id : '';

            if ($this->service_type_id) {
                $this->loadKnowledgeArticles($this->service_type_id);
                $this->calculatePriorityFromArticles();
            }
        } else {
            if ($user->cannot('create tickets')) {
                abort(403, 'No tienes permiso para crear tickets.');
            }
            // Al crear, si hay un tipo de servicio seleccionado por defecto (no hay), requires_noc se mantiene false.
            // Se actualizará al elegir tipo de servicio.
        }
    }

    public function selectServiceType($value)
    {
        $this->service_type_id = $value;

        $serviceType = ServiceType::find($value);
        if ($serviceType) {
            // Establecer automáticamente según configuración del tipo de servicio
            $this->requires_noc = $serviceType->requires_noc;
        } else {
            $this->requires_noc = false;
        }

        $this->loadKnowledgeArticles($value);
        $this->calculatePriorityFromArticles();
    }

    private function loadKnowledgeArticles($serviceTypeId = null)
    {
        if (!$serviceTypeId) {
            $this->knowledgeArticles = collect();
            return;
        }
        $serviceType = ServiceType::with('articles')->find($serviceTypeId);
        $this->knowledgeArticles = $serviceType ? $serviceType->articles()->orderBy('title')->get() : collect();
    }

    private function calculatePriorityFromArticles()
    {
        $priorityOrder = ['P1' => 1, 'P2' => 2, 'P3' => 3, 'P4' => 4];

        $highestArticle = $this->knowledgeArticles
            ->filter(fn($a) => !empty($a->priority))
            ->sortBy(fn($a) => $priorityOrder[$a->priority] ?? 999)
            ->first();

        $this->priority = $highestArticle ? $highestArticle->priority : 'P3';
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

    // Reemplaza el método existente
    private function generateTicketCode($ticketId)
    {
        $user = Auth::user();
        $role = $user->roles()->first();
        $prefix = $role->prefix ?? 'TK';   // Prefijo del rol, o 'TK' por defecto

        // Mapear origen del ticket a código corto
        $originMap = [
            'Facebook Messenger' => 'FB',
            'SMS WhatsApp' => 'WH',
            'Llamada de WhatsApp' => 'WHL',
            'Llamada Telefónica' => 'LL',
            'SMS' => 'SMS',
            'Presencial' => 'PR',
            'Otros' => 'OT',
        ];
        $originCode = $originMap[$this->origin] ?? 'GEN';

        // Calcular el número consecutivo considerando el formato TK-{PREFIX}-{ORIGIN}-{SEQ}
        $nextNumber = $this->getNextTicketSequence($prefix, $originCode);

        // Formato final: TK-SEC-LL-0001
        return sprintf('TK-%s-%s-%04d', $prefix, $originCode, $nextNumber);
    }

    private function getNextTicketSequence(string $prefix, string $originCode): int
    {
        $likePattern = "TK-{$prefix}-{$originCode}-%";
        $lastTicket = Ticket::where('ticket_code', 'like', $likePattern)
            ->orderBy('id', 'desc')
            ->first();

        if (!$lastTicket) {
            return 1;
        }

        $parts = explode('-', $lastTicket->ticket_code);
        $lastNumber = (int) end($parts);
        return $lastNumber + 1;
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
        $serviceType = ServiceType::find($this->service_type_id);
        $serviceName = $serviceType ? $serviceType->name : '';

        $data = [
            'client_id' => $this->client_id,
            'description' => $this->description,
            'service_type' => $serviceName,
            'priority' => $this->priority,
            'origin' => $this->origin,
            'requires_noc' => $this->requires_noc,   // Se guarda el valor del switch
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
        $serviceTypes = ServiceType::orderBy('name')->get();
        return view('livewire.tickets.ticket-form', compact('serviceTypes'))->layout('components.layouts.app');
    }
}