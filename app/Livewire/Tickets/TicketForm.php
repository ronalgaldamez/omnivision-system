<?php

namespace App\Livewire\Tickets;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Client;
use App\Models\Ticket;
use App\Models\ServiceType;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TicketForm extends Component
{
    public $ticketId;
    public $description;
    public $client_id;
    public $service_type_id = '';
    public $priority = '';
    public $origin = '';
    public $requires_noc = false;
    public $create_ot = false;
    public $status = 'pending';

    public $clientSearch = '';
    public $clientSearchResults = [];
    public $selectedClient = null;
    public $showClientModal = false;
    public $modalKey = '';
    public $confirmingSave = false;

    public $isDraft = false;
    public $confirmingNewClient = false;

    public $knowledgeArticles = [];

    // --- NUEVAS PROPIEDADES PARA CRONOMETRO Y FLUJO ---
    public $editingEnabled = false;      // Controla si los campos están habilitados
    public $ticketOpened = false;        // Indica si el ticket fue abierto (cronómetro corriendo)
    public $confirmingSolve = false;     // Modal de confirmación para Solucionar
    public $elapsedSeconds = 0;
    public $confirmingGenerate = false;

    protected $rules = [
        'client_id' => 'required|exists:clients,id',
        'description' => 'required|string|min:5',
        'service_type_id' => 'required|exists:service_types,id',
        'origin' => 'nullable|string|max:100',
        'requires_noc' => 'boolean',
        'status' => 'in:pending,in_progress,resolved,closed,open',
    ];

    public function mount($id = null)
    {
        $user = Auth::user();

        if ($id) {
            // Modo edición de ticket existente (no cambia)
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

            // Si es edición, los campos se muestran editables (como siempre)
            $this->editingEnabled = true;
        } else {
            // Modo creación
            if ($user->cannot('create tickets')) {
                abort(403, 'No tienes permiso para crear tickets.');
            }

            // Verificar si hay un ticket abierto en sesión
            $openTicketId = session()->get('open_ticket_id');
            if ($openTicketId) {
                $ticket = Ticket::find($openTicketId);
                if ($ticket && in_array($ticket->status, ['open', 'in_progress'])) {
                    // Retomar ticket abierto
                    $this->ticketId = $ticket->id;
                    $this->client_id = $ticket->client_id;
                    $this->description = $ticket->description ?? '';
                    $this->priority = $ticket->priority ?? '';
                    $this->origin = $ticket->origin ?? '';
                    $this->requires_noc = $ticket->requires_noc;
                    $this->status = $ticket->status;
                    if ($ticket->client_id) {
                        $client = Client::find($ticket->client_id);
                        if ($client) {
                            $this->selectedClient = $client;
                            $this->clientSearch = $client->name;
                        }
                    }
                    $serviceType = ServiceType::where('name', $ticket->service_type)->first();
                    $this->service_type_id = $serviceType ? $serviceType->id : '';
                    if ($this->service_type_id) {
                        $this->loadKnowledgeArticles($this->service_type_id);
                        $this->calculatePriorityFromArticles();
                    }
                    $this->editingEnabled = true;
                    $this->ticketOpened = true;
                    // Calcular tiempo transcurrido
                    $this->updateElapsedSeconds();
                } else {
                    // Ticket no válido, limpiar sesión
                    session()->forget('open_ticket_id');
                    $this->checkDraft();
                }
            } else {
                // No hay ticket abierto, verificar borrador normal
                $this->checkDraft();
            }
        }
    }

    /**
     * Restaura el borrador tradicional (existente).
     */
    private function checkDraft()
    {
        $draft = session()->get('ticket_draft');
        if ($draft) {
            $this->client_id = $draft['client_id'] ?? null;
            $this->description = $draft['description'] ?? '';
            $this->service_type_id = $draft['service_type_id'] ?? '';
            $this->priority = $draft['priority'] ?? '';
            $this->origin = $draft['origin'] ?? '';
            $this->requires_noc = $draft['requires_noc'] ?? false;
            $this->create_ot = $draft['create_ot'] ?? false;
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

            $this->isDraft = true;
        }
    }

    public function updated($property)
    {
        if ($this->ticketOpened && $this->ticketId) {
            $this->persistOpenTicket($property);
        } elseif (!$this->ticketId && !$this->ticketOpened) {
            $this->saveDraft();
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
            'create_ot' => $this->create_ot,
            'clientSearch' => $this->clientSearch,
        ]);
    }

    public function updatedServiceTypeId($value)
    {
        if (empty($value)) {
            $this->requires_noc = false;
            $this->create_ot = false;
            $this->knowledgeArticles = collect();
            $this->priority = 'P3';
            return;
        }

        $serviceType = ServiceType::find($value);
        if ($serviceType) {
            $this->requires_noc = $serviceType->requires_noc;
            $this->create_ot = false; // si el servicio requiere NOC, anula crear OT
        } else {
            $this->requires_noc = false;
        }

        $this->loadKnowledgeArticles($value);
        $this->calculatePriorityFromArticles();

        // Persistir el cambio en el ticket abierto
        if ($this->ticketOpened && $this->ticketId) {
            $this->persistOpenTicket('service_type_id');
        }
    }

    public function updatedCreateOt($value)
    {
        if ($value) {
            $this->requires_noc = false;
        }
    }

    public function updatedRequiresNoc($value)
    {
        if ($value) {
            $this->create_ot = false;
        }
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

        $this->resetTicketFields();

        $this->client_id = $id;
        $this->selectedClient = $client;
        $this->clientSearch = $name . ($phone ? ' (' . $phone . ')' : '');
        $this->clientSearchResults = [];

        // Si el ticket ya está abierto, actualizar BD inmediatamente
        if ($this->ticketOpened && $this->ticketId) {
            Ticket::where('id', $this->ticketId)->update(['client_id' => $id]);
        }
    }

    public function openClientModal()
    {
        if ($this->hasDraftContent()) {
            $this->confirmingNewClient = true;
            return;
        }

        $this->modalKey = 'client-modal-' . uniqid();
        $this->showClientModal = true;
    }

    private function hasDraftContent()
    {
        return !empty(trim($this->description))
            || !empty($this->service_type_id)
            || !empty($this->origin)
            || $this->requires_noc
            || $this->create_ot;
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
        $this->create_ot = false;
        $this->knowledgeArticles = collect();

        if (!$this->ticketId && !$this->ticketOpened) {
            $this->saveDraft();
        }
    }

    // ==================== NUEVOS MÉTODOS ====================

    /**
     * Abre un nuevo ticket: crea el registro en BD, inicia cronómetro, habilita edición.
     */
    public function openTicket()
    {
        $ticket = Ticket::create([
            'client_id' => $this->client_id,
            'description' => '',
            'service_type' => '',
            'priority' => $this->priority ?: 'P3',
            'origin' => $this->origin ?: '',
            'requires_noc' => false,
            'status' => 'open',
            'started_at' => now(),
            'created_by' => Auth::id(),
        ]);

        // Generar y guardar el código de ticket
        $ticket->ticket_code = $this->generateTicketCode($ticket->id);
        $ticket->save();

        $this->ticketId = $ticket->id;
        $this->ticketOpened = true;
        $this->editingEnabled = true;

        session()->put('open_ticket_id', $this->ticketId);
        session()->forget('ticket_draft');

        $this->elapsedSeconds = 0;   // cronómetro arranca en 0
    }

    /**
     * Actualiza los segundos transcurridos desde started_at.
     */
    public function updateElapsedSeconds()
    {
        if ($this->ticketOpened && $this->ticketId) {
            $ticket = Ticket::find($this->ticketId);
            if ($ticket && $ticket->started_at) {
                $this->elapsedSeconds = Carbon::parse($ticket->started_at)->diffInSeconds(now());
            }
        }
    }
    /**
     * Muestra confirmación para solucionar ticket.
     */
    public function confirmSolve()
    {
        // Validar los campos requeridos para poder cerrar
        $this->validate([
            'client_id' => 'required|exists:clients,id',
            'description' => 'required|string|min:5',
            'service_type_id' => 'required|exists:service_types,id',
        ]);

        $this->confirmingSolve = true;
    }

    /**
     * Ejecuta la solución del ticket: guarda cambios, establece resolved, limpia sesión.
     */
    public function executeSolve()
    {
        $this->confirmingSolve = false;

        $ticket = Ticket::findOrFail($this->ticketId);

        $serviceType = ServiceType::find($this->service_type_id);
        $serviceName = $serviceType ? $serviceType->name : '';

        if ($this->create_ot) {
            // Crear OT sin resolver el ticket (igual que flujo NOC)
            $this->createWorkOrder($ticket);

            $ticket->update([
                'client_id' => $this->client_id,
                'description' => $this->description,
                'service_type' => $serviceName,
                'priority' => $this->priority,
                'origin' => $this->origin,
                'status' => 'in_progress',
                'l1_ended_at' => now(),
            ]);
        } else {
            // Solucionar ticket normalmente
            $ticket->update([
                'client_id' => $this->client_id,
                'description' => $this->description,
                'service_type' => $serviceName,
                'priority' => $this->priority,
                'origin' => $this->origin,
                'requires_noc' => $this->requires_noc,
                'status' => 'resolved',
                'resolved_at' => now(),
                'l1_ended_at' => now(),
            ]);
        }

        // Limpiar sesión
        session()->forget('open_ticket_id');
        session()->forget('ticket_draft');

        $message = $this->create_ot
            ? 'OT generada correctamente. Ticket en seguimiento.'
            : 'Ticket resuelto correctamente. Tiempo total: ' . gmdate('H:i:s', $this->elapsedSeconds);

        session()->flash('message', $message);

        return redirect()->route('tickets.index');
    }

    public function cancelSolve()
    {
        $this->confirmingSolve = false;
    }

    // ==================== FIN NUEVOS MÉTODOS ====================

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

            if ($this->create_ot) {
                $this->createWorkOrder($ticket);
            } elseif ($this->requires_noc) {
                $this->dispatch('ticket-created-for-noc');
                $this->dispatch('show-toast', type: 'info', message: 'Nuevo ticket requiere atención del NOC.');
            }

            session()->flash('message', 'Ticket creado correctamente. Código: ' . $ticket->ticket_code);

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
            'created_by' => Auth::id(),
        ]);
    }

    protected function persistOpenTicket($property)
    {
        $ticket = Ticket::find($this->ticketId);
        if (!$ticket)
            return;

        $map = [
            'description' => 'description',
            'origin' => 'origin',
            'requires_noc' => 'requires_noc',
            'priority' => 'priority',
        ];

        if (array_key_exists($property, $map)) {
            $ticket->update([$map[$property] => $this->{$property}]);
        }

        if ($property === 'service_type_id') {
            $serviceType = ServiceType::find($this->service_type_id);
            $ticket->update(['service_type' => $serviceType ? $serviceType->name : '']);
        }
    }

    public function confirmGenerate()
    {
        $this->validate([
            'client_id' => 'required|exists:clients,id',
            'description' => 'required|string|min:5',
            'service_type_id' => 'required|exists:service_types,id',
        ]);

        $this->confirmingGenerate = true;
    }

    public function executeGenerate()
    {
        $this->confirmingGenerate = false;

        $ticket = Ticket::findOrFail($this->ticketId);
        $serviceType = ServiceType::find($this->service_type_id);
        $serviceName = $serviceType ? $serviceType->name : '';

        $ticket->update([
            'client_id' => $this->client_id,
            'description' => $this->description,
            'service_type' => $serviceName,
            'priority' => $this->priority,
            'origin' => $this->origin,
            'requires_noc' => true,
            'status' => 'pending',
            'l1_ended_at' => now(),   // ← L1 termina su parte
            'escalated_at' => now(),   // ← momento del traspaso a NOC
        ]);

        session()->forget('open_ticket_id');
        session()->forget('ticket_draft');

        $this->dispatch('ticket-created-for-noc');
        session()->flash('message', 'Ticket generado y escalado a NOC correctamente.');

        return redirect()->route('tickets.index');
    }

    public function cancelGenerate()
    {
        $this->confirmingGenerate = false;
    }

    public function render()
    {
        // Actualizar el cronómetro en cada render si el ticket está abierto
        if ($this->ticketOpened && $this->ticketId) {
            $this->updateElapsedSeconds();
        }

        $serviceTypes = ServiceType::orderBy('name')->get();
        return view('livewire.tickets.ticket-form', compact('serviceTypes'))->layout('components.layouts.app');
    }
}