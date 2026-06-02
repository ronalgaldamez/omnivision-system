<?php

namespace App\Livewire\Tickets;

use Livewire\Component;
use Livewire\Attributes\On;
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
    public $requires_noc = false;
    public $status = 'pending';

    public $clientSearch = '';
    public $clientSearchResults = [];
    public $selectedClient = null;
    public $showClientModal = false;
    public $modalKey = '';
    public $confirmingSave = false;

    // Nuevas propiedades
    public $isDraft = false;
    public $confirmingNewClient = false;

    public $knowledgeArticles = [];

    protected $rules = [
        'client_id' => 'required|exists:clients,id',
        'description' => 'required|string|min:5',
        'service_type_id' => 'required|exists:service_types,id',
        'origin' => 'nullable|string|max:100',
        'requires_noc' => 'boolean',
        'status' => 'in:pending,in_progress,resolved,closed',
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
            $this->selectedClient = $ticket->client;

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

            // Recuperar borrador de la sesión
            $draft = session()->get('ticket_draft');
            if ($draft) {
                $this->client_id = $draft['client_id'] ?? null;
                $this->description = $draft['description'] ?? '';
                $this->service_type_id = $draft['service_type_id'] ?? '';
                $this->priority = $draft['priority'] ?? '';
                $this->origin = $draft['origin'] ?? '';
                $this->requires_noc = $draft['requires_noc'] ?? false;
                $this->clientSearch = $draft['clientSearch'] ?? '';

                if (!empty($draft['client_id'])) {
                    $client = Client::find($draft['client_id']);
                    if ($client) {
                        $this->selectedClient = $client;
                    }
                }

                if ($this->service_type_id) {
                    $this->loadKnowledgeArticles($this->service_type_id);
                    $this->calculatePriorityFromArticles();
                }

                // Activar el badge de borrador
                $this->isDraft = true;
            }
        }
    }

    public function updated($property)
    {
        if (!$this->ticketId) {
            $this->saveDraft();
            // Si se modificó algún campo, aseguramos que el badge siga visible
            $this->isDraft = true;
        }
    }

    private function saveDraft()
    {
        session()->put('ticket_draft', [
            'client_id' => $this->client_id,
            'description' => $this->description,
            'service_type_id' => $this->service_type_id,
            'priority' => $this->priority,
            'origin' => $this->origin,
            'requires_noc' => $this->requires_noc,
            'clientSearch' => $this->clientSearch,
        ]);
    }

    public function updatedServiceTypeId($value)
    {
        if (empty($value)) {
            $this->requires_noc = false;
            $this->knowledgeArticles = collect();
            $this->priority = 'P3';
            return;
        }

        $serviceType = ServiceType::find($value);
        if ($serviceType) {
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

    public function selectClient($id, $name, $phone = null)
    {
        $id = (int) $id;
        $client = Client::find($id);

        if (!$client) {
            $this->dispatch('show-toast', type: 'error', message: 'El cliente seleccionado ya no existe.');
            return;
        }

        // Limpiar campos del ticket antes de asignar el nuevo cliente
        $this->resetTicketFields();

        $this->client_id = $id;
        $this->selectedClient = $client;
        $this->clientSearch = $name . ($phone ? ' (' . $phone . ')' : '');
        $this->clientSearchResults = [];
    }

    public function openClientModal()
    {
        // Si el formulario tiene contenido, pedir confirmación
        if ($this->hasDraftContent()) {
            $this->confirmingNewClient = true;
            return;
        }

        // Si no hay contenido, abrir el modal directamente
        $this->modalKey = 'client-modal-' . uniqid();
        $this->showClientModal = true;
    }

    /**
     * Determina si el formulario tiene datos que podrían perderse.
     */
    private function hasDraftContent()
    {
        return !empty(trim($this->description))
            || !empty($this->service_type_id)
            || !empty($this->origin)
            || $this->requires_noc;
    }

    public function proceedToNewClient()
    {
        $this->confirmingNewClient = false;
        $this->modalKey = 'client-modal-' . uniqid();
        $this->showClientModal = true;
    }

    public function cancelNewClient()
    {
        $this->confirmingNewClient = false;
    }

    public function closeClientModal()
    {
        $this->showClientModal = false;
    }

    #[On('clientCreated')]
    public function handleClientCreated($id, $name, $phone = null)
    {
        $this->selectClient($id, $name, $phone);
        $this->closeClientModal();
    }

    private function resetTicketFields()
    {
        $this->description = '';
        $this->service_type_id = '';
        $this->priority = '';
        $this->origin = '';
        $this->requires_noc = false;
        $this->knowledgeArticles = collect();

        if (!$this->ticketId) {
            $this->saveDraft();
        }
    }

    private function generateTicketCode($ticketId)
    {
        $user = Auth::user();
        $role = $user->roles()->first();
        $prefix = $role->prefix ?? 'TK';

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

        $nextNumber = $this->getNextTicketSequence($prefix, $originCode);

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

            // Limpiar borrador después de guardar exitosamente
            session()->forget('ticket_draft');
            $this->isDraft = false;
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