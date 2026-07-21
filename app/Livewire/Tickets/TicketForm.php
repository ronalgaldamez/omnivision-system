<?php

namespace App\Livewire\Tickets;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Client;
use App\Models\Ticket;
use App\Models\ServiceType;
use App\Models\Zone;
use App\Models\Plan;
use App\Services\SlaService;
use App\Services\WorkOrderService;
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
    public $requires_contract = false;
    public $requires_potential = false;
    public $canToggleNoc = false;
    public $canToggleOt = false;
    public $canToggleContract = false;
    public $canTogglePotential = false;
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

    // --- SERVICE TYPE SEARCH ---
    public $serviceTypeSearch = '';
    public $serviceTypeResults = [];
    public $showServiceTypeModal = false;
    public $serviceTypeListSearch = '';
    public $serviceTypeList = [];

    // --- QUICK SERVICE TYPE ---
    public $quickServiceTypeId = '';

    // --- ZONAS Y PLANES ---
    public $zone_id = '';
    public $plan_id = '';
    public $availableZones = [];
    public $availablePlans = [];
    public $selectedPlanPrice = null;

    // --- PLANES DE REFERENCIA (CLIENTE POTENCIAL) ---
    public $quickReferencePlans = [];
    public $isPotentialClient = false;

    // --- NUEVAS PROPIEDADES PARA CRONOMETRO Y FLUJO ---
    public $editingEnabled = false;      // Controla si los campos están habilitados
    public $ticketOpened = false;        // Indica si el ticket fue abierto (cronómetro corriendo)
    public $confirmingSolve = false;     // Modal de confirmación para Solucionar
    public $elapsedSeconds = 0;
    public $confirmingGenerate = false;
    public $confirmingGenerateContract = false;
    public $confirmingOpen = false;

    // --- CANCELACIÓN ---
    public $confirmingCancel = false;
    public $cancelReason = '';

    protected $rules = [
        'client_id' => 'required|exists:clients,id',
        'description' => 'required|string|min:5',
        'service_type_id' => 'required|exists:service_types,id',
        'origin' => 'nullable|string|max:100',
        'requires_noc' => 'boolean',
        'create_ot' => 'boolean',
        'requires_contract' => 'boolean',
        'status' => 'in:pending,in_progress,resolved,closed,open,cancelled',
    ];

    private function refreshTogglePermissions(): void
    {
        $user = auth()->user();
        $this->canToggleOt = $user?->can('manage_create_ot_toggle') ?? false;
        $this->canToggleNoc = $user?->can('manage_requires_noc_toggle') ?? false;
        $this->canToggleContract = $user?->can('manage_requires_contract_toggle') ?? false;
    }

    public function mount($id = null)
    {
        $this->refreshTogglePermissions();

        $user = Auth::user();

        $this->loadAvailableZones();

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
            $this->requires_contract = $ticket->requires_contract ?? false;
            $this->create_ot = $ticket->create_ot ?? false;
            $this->status = $ticket->status;
            $this->zone_id = $ticket->zone_id;
            $this->plan_id = $ticket->plan_id;
            $this->clientSearch = $ticket->client->name;
            $this->selectedClient = $ticket->client;

            $serviceType = ServiceType::where('name', $ticket->service_type)->first();
            $this->service_type_id = $serviceType ? $serviceType->id : '';

            if ($this->service_type_id) {
                $this->updatedServiceTypeId($this->service_type_id);
            }

            $this->loadAvailableZones();
            if ($this->zone_id) $this->updatedZoneId($this->zone_id);
            if ($this->plan_id) $this->updatedPlanId($this->plan_id);

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
                    $this->requires_contract = $ticket->requires_contract ?? false;
                    $this->create_ot = $ticket->create_ot ?? false;
                    $this->status = $ticket->status;
                    $this->zone_id = $ticket->zone_id;
                    $this->plan_id = $ticket->plan_id;
                    if ($ticket->client_id) {
                        $client = Client::with('branch', 'zone')->find($ticket->client_id);
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
                    $this->loadAvailableZones();
                    if ($this->zone_id) $this->updatedZoneId($this->zone_id);
                    if ($this->plan_id) $this->updatedPlanId($this->plan_id);
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
            $this->requires_contract = $draft['requires_contract'] ?? false;
            $this->requires_potential = $draft['requires_potential'] ?? false;
            $this->isPotentialClient = $draft['isPotentialClient'] ?? false;
            $this->zone_id = $draft['zone_id'] ?? '';
            $this->plan_id = $draft['plan_id'] ?? '';
            $this->clientSearch = $draft['clientSearch'] ?? '';
            $this->quickServiceTypeId = $draft['quickServiceTypeId'] ?? '';
            $this->serviceTypeSearch = $draft['serviceTypeSearch'] ?? '';

            if (!empty($draft['client_id'])) {
                $client = Client::with('branch', 'zone')->find($draft['client_id']);
                if ($client) {
                    $this->selectedClient = $client;
                    $this->loadAvailableZones();
                }
            }

            if ($this->service_type_id) {
                $this->updatedServiceTypeId($this->service_type_id);
            }

            if ($this->zone_id) {
                $this->updatedZoneId($this->zone_id);
            }
            if ($this->plan_id) {
                $this->updatedPlanId($this->plan_id);
            }

            // Re-derivar planes de referencia desde el tipo de servicio restaurado
            if ($this->isPotentialClient || $this->requires_contract) {
                $this->quickReferencePlans = Plan::where('is_active', true)->get();
            }

            $this->isDraft = true;
        }
    }

    public function updated($property)
    {
        if (in_array($property, ['description', 'origin', 'client_id', 'service_type_id'])) {
            $this->resetValidation($property);
        }

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
            'requires_contract' => $this->requires_contract,
            'requires_potential' => $this->requires_potential,
            'isPotentialClient' => $this->isPotentialClient,
            'zone_id' => $this->zone_id,
            'plan_id' => $this->plan_id,
            'clientSearch' => $this->clientSearch,
            'quickServiceTypeId' => $this->quickServiceTypeId,
            'serviceTypeSearch' => $this->serviceTypeSearch,
        ]);
    }

    public function updatedServiceTypeId($value)
    {
        if (empty($value)) {
            $this->requires_noc = false;
            $this->create_ot = false;
            $this->knowledgeArticles = collect();
            $this->priority = 'P3';
            $this->quickServiceTypeId = '';
            return;
        }

        $serviceType = ServiceType::find($value);
        if ($serviceType) {
            $this->requires_noc = $serviceType->requires_noc;
            $this->create_ot = $serviceType->requires_ot;
            $this->requires_contract = $serviceType->requires_contract;
            $this->requires_potential = $serviceType->requires_potential;
        } else {
            $this->requires_noc = false;
            $this->create_ot = false;
            $this->requires_contract = false;
            $this->requires_potential = false;
        }
        // Si requiere contrato, asegurar que noc y ot estén apagados
        if ($serviceType && $serviceType->requires_contract) {
            $this->requires_noc = false;
            $this->create_ot = false;
        }

        // Detectar si muestra Planes de Referencia (Cliente Potencial o Instalación)
        $this->isPotentialClient = $serviceType && $serviceType->requires_potential;
        $showQuickReferencePlans = $serviceType && ($serviceType->requires_potential || $serviceType->requires_contract);
        if ($showQuickReferencePlans) {
            $this->quickReferencePlans = Plan::where('is_active', true)->get();
        } else {
            $this->quickReferencePlans = collect();
        }

        $this->quickServiceTypeId = $value;
        $this->loadKnowledgeArticles($value);
        $this->calculatePriorityFromArticles();

        // Recargar planes disponibles según el nuevo tipo de servicio
        if ($this->zone_id) {
            $this->updatedZoneId($this->zone_id);
        }

        // Persistir el cambio en el ticket abierto
        if ($this->ticketOpened && $this->ticketId) {
            $this->persistOpenTicket('service_type_id');
        }
    }

    public function updatedCreateOt($value)
    {
        if ($value) {
            $this->requires_noc = false;
            $this->requires_contract = false;
        }
    }

    public function updatedRequiresNoc($value)
    {
        if ($value) {
            $this->create_ot = false;
            $this->requires_contract = false;
        }
    }

    public function updatedRequiresContract($value)
    {
        if ($value) {
            $this->requires_noc = false;
            $this->create_ot = false;
        }
    }

    public function updatedQuickServiceTypeId($value)
    {
        if ($value) {
            $this->service_type_id = $value;
        }
    }

    public function updatedServiceTypeSearch()
    {
        if (strlen($this->serviceTypeSearch) >= 1) {
            $this->serviceTypeResults = ServiceType::where('name', 'like', '%' . $this->serviceTypeSearch . '%')
                ->orderBy('name')
                ->take(10)
                ->get();
        } else {
            $this->serviceTypeResults = [];
        }
    }

    public function selectServiceType($id)
    {
        $serviceType = ServiceType::find($id);
        if (!$serviceType) return;

        $this->service_type_id = $id;
        $this->serviceTypeSearch = $serviceType->name;
        $this->serviceTypeResults = [];
        $this->showServiceTypeModal = false;

        $this->updatedServiceTypeId($id);

        if ($this->ticketOpened && $this->ticketId) {
            $this->persistOpenTicket('service_type_id');
        }
    }

    public function clearServiceType()
    {
        $this->service_type_id = '';
        $this->serviceTypeSearch = '';
        $this->serviceTypeResults = [];

        $this->updatedServiceTypeId('');

        if ($this->ticketOpened && $this->ticketId) {
            $this->persistOpenTicket('service_type_id');
        }
    }

    public function openServiceTypeModal()
    {
        $this->serviceTypeListSearch = '';
        $this->serviceTypeList = ServiceType::orderBy('name')->get();
        $this->showServiceTypeModal = true;
    }

    public function closeServiceTypeModal()
    {
        $this->showServiceTypeModal = false;
        $this->serviceTypeListSearch = '';
        $this->serviceTypeList = [];
    }

    public function updatedServiceTypeListSearch()
    {
        if (strlen($this->serviceTypeListSearch) >= 1) {
            $this->serviceTypeList = ServiceType::where('name', 'like', '%' . $this->serviceTypeListSearch . '%')
                ->orderBy('name')
                ->get();
        } else {
            $this->serviceTypeList = ServiceType::orderBy('name')->get();
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
        $client = Client::with('branch', 'zone')->find($id);

        if (!$client) {
            $this->dispatch('show-toast', type: 'error', message: 'El cliente seleccionado ya no existe.');
            return;
        }

        $this->resetTicketFields();

        $this->client_id = $id;
        $this->selectedClient = $client;
        $this->clientSearch = $name . ($phone ? ' (' . $phone . ')' : '');
        $this->clientSearchResults = [];

        // Copiar zona y plan desde el cliente
        $this->loadAvailableZones();
        if ($client->zone_id) {
            $this->zone_id = $client->zone_id;
            $this->updatedZoneId($client->zone_id);
        }
        if ($client->plan_id) {
            $this->plan_id = $client->plan_id;
            $this->updatedPlanId($client->plan_id);
        }

        // Si el ticket ya está abierto, actualizar BD inmediatamente
        if ($this->ticketOpened && $this->ticketId) {
            Ticket::where('id', $this->ticketId)->update([
                'client_id' => $id,
                'zone_id' => $client->zone_id,
                'plan_id' => $client->plan_id,
            ]);
        }
    }

    public function loadAvailableZones()
    {
        if ($this->selectedClient && $this->selectedClient->branch_id) {
            $this->availableZones = Zone::where('branch_id', $this->selectedClient->branch_id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        } else {
            $this->availableZones = Zone::where('is_active', true)
                ->orderBy('name')
                ->get();
        }
    }

    public function updatedZoneId($value)
    {
        $this->plan_id = '';
        $this->selectedPlanPrice = null;
        $this->availablePlans = [];

        if (!$value) return;

        $zone = Zone::with('branch')->find($value);
        if (!$zone) return;

        $serviceType = ServiceType::find($this->service_type_id);
        $serviceName = $serviceType?->name;

        // Filtrar planes según disponibilidad de la zona + tipo de servicio
        $plans = Plan::where('is_active', true);

        if ($serviceName === 'internet' && !$zone->has_internet) {
            $this->dispatch('show-toast', type: 'warning', message: 'Esta zona no tiene cobertura de internet.');
            return;
        }
        if ($serviceName === 'cable' && !$zone->has_cable) {
            $this->dispatch('show-toast', type: 'warning', message: 'Esta zona no tiene cobertura de cable.');
            return;
        }

        $plans = $plans->get()->filter(function ($plan) use ($serviceName) {
            if (!$serviceName) return true;
            if ($serviceName === 'internet') return in_array($plan->service_type, ['internet', 'internet_cable']);
            if ($serviceName === 'cable') return in_array($plan->service_type, ['cable', 'internet_cable']);
            return true;
        });

        $this->availablePlans = $plans->values();
    }

    public function updatedPlanId($value)
    {
        if (!$value || !$this->zone_id) {
            $this->selectedPlanPrice = null;
            return;
        }
        $zone = Zone::find($this->zone_id);
        $plan = Plan::find($value);
        if ($zone && $plan) {
            $this->selectedPlanPrice = $zone->getEffectivePriceForPlan($plan);
        }
    }

    /**
     * Agrega una referencia de plan a la descripción del ticket (Cliente Potencial).
     */
    public function addPlanReference($planId)
    {
        $plan = Plan::find($planId);
        if (!$plan) return;

        $price = $this->zone_id
            ? optional(Zone::find($this->zone_id))->getEffectivePriceForPlan($plan)
            : $plan->base_price;

        $line = "📋 Plan de referencia: {$plan->name}";
        if ($plan->speed) $line .= " | Velocidad: {$plan->speed}";
        if ($plan->channels) $line .= " | Canales: {$plan->channels}";
        $line .= ' | Precio: $' . number_format($price ?? $plan->base_price, 2);

        $this->description = trim($this->description . "\n" . $line);

        $this->dispatch('show-toast', type: 'success', message: "Plan «{$plan->name}» agregado como referencia.");
    }

    public $editingClientId = null;

    #[On('clientFormReady')]
    public function handleClientFormReady()
    {
        if ($this->editingClientId) {
            $this->dispatch('loadClientData', id: $this->editingClientId);
        }
    }

    public function openClientModal($clientId = null)
    {
        if (is_null($clientId) && !$this->ticketOpened && $this->hasDraftContent()) {
            $this->confirmingNewClient = true;
            return;
        }

        $this->editingClientId = $clientId;
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
        $this->editingClientId = null;
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
        $this->editingClientId = null;
        session()->forget('client_modal_draft');
    }

    #[On('clientCreated')]
    public function handleClientCreated($id, $name, $phone = null)
    {
        $id = (int) $id;
        $client = Client::with('branch', 'zone')->find($id);

        if (!$client) {
            $this->dispatch('show-toast', type: 'error', message: 'Error al crear el cliente.');
            return;
        }

        $this->client_id = $id;
        $this->selectedClient = $client;
        $this->clientSearch = $name . ($phone ? ' (' . $phone . ')' : '');
        $this->clientSearchResults = [];

        $this->loadAvailableZones();
        if ($client->zone_id) {
            $this->zone_id = $client->zone_id;
            $this->updatedZoneId($client->zone_id);
        }
        if ($client->plan_id) {
            $this->plan_id = $client->plan_id;
            $this->updatedPlanId($client->plan_id);
        }
        if ($client->branch_id) {
            $this->branch_id = $client->branch_id;
        }

        // Si el ticket ya está abierto, actualizar BD inmediatamente
        if ($this->ticketOpened && $this->ticketId) {
            Ticket::where('id', $this->ticketId)->update([
                'client_id' => $id,
                'zone_id' => $client->zone_id,
                'plan_id' => $client->plan_id,
            ]);
        }

        $this->closeClientModal();

        $this->dispatch('show-toast', type: 'success', message: 'Cliente creado y seleccionado correctamente.');
    }

    private function resetTicketFields()
    {
        $this->description = '';
        $this->service_type_id = '';
        $this->priority = '';
        $this->origin = '';
        $this->requires_noc = false;
        $this->create_ot = false;
        $this->requires_contract = false;
        $this->zone_id = '';
        $this->plan_id = '';
        $this->availablePlans = [];
        $this->selectedPlanPrice = null;
        $this->knowledgeArticles = collect();

        if (!$this->ticketId && !$this->ticketOpened) {
            $this->saveDraft();
        }
    }

    // ==================== NUEVOS MÉTODOS ====================

    public function confirmOpen()
    {
        $this->validate([
            'service_type_id' => 'required|exists:service_types,id',
        ]);
        $this->confirmingOpen = true;
    }

    public function executeOpen()
    {
        $this->confirmingOpen = false;
        $this->openTicket();
    }

    public function cancelOpen()
    {
        $this->confirmingOpen = false;
    }

    /**
     * Abre un nuevo ticket: crea el registro en BD, inicia cronómetro, habilita edición.
     */
    public function openTicket()
    {
        $serviceType = ServiceType::find($this->service_type_id);
        $serviceName = $serviceType ? $serviceType->name : '';

        $ticket = Ticket::create([
            'client_id' => $this->client_id,
            'description' => '',
            'service_type' => $serviceName,
            'priority' => $this->priority ?: 'P3',
            'origin' => $this->origin ?: '',
            'requires_noc' => $this->requires_noc,
            'requires_contract' => $this->requires_contract,
            'create_ot' => $this->create_ot,
            'zone_id' => $this->zone_id ?: null,
            'plan_id' => $this->plan_id ?: null,
            'status' => 'open',
            'started_at' => now(),
            'created_by' => Auth::id(),
        ]);

        // Generar y guardar el código de ticket
        $ticket->ticket_code = $this->generateTicketCode($ticket->id);
        $ticket->save();

        // Asignar meta SLA
        app(SlaService::class)->assignSlaToTicket($ticket);

        $this->ticketId = $ticket->id;
        $this->ticketOpened = true;
        $this->editingEnabled = true;

        session()->put('open_ticket_id', $this->ticketId);
        session()->forget('ticket_draft');

        // Cargar Planes de Referencia según el tipo de servicio seleccionado
        $this->updatedServiceTypeId($this->service_type_id);

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

        $zoneId = $this->zone_id ?: null;
        $planId = $this->plan_id ?: null;

        if ($this->create_ot) {
            $this->createWorkOrder($ticket);

            $ticket->update([
                'client_id' => $this->client_id,
                'description' => $this->description,
                'service_type' => $serviceName,
                'priority' => $this->priority,
                'origin' => $this->origin,
                'zone_id' => $zoneId,
                'plan_id' => $planId,
                'status' => 'in_progress',
                'l1_ended_at' => now(),
            ]);
        } else {
            $ticket->update([
                'client_id' => $this->client_id,
                'description' => $this->description,
                'service_type' => $serviceName,
                'priority' => $this->priority,
                'origin' => $this->origin,
                'zone_id' => $zoneId,
                'plan_id' => $planId,
                'requires_noc' => $this->requires_noc,
                'status' => 'resolved',
                'resolved_by' => auth()->id(),
                'resolved_at' => now(),
                'l1_ended_at' => now(),
            ]);
        }

        // Evaluar SLA
        app(SlaService::class)->evaluateSla($ticket);

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

    // ==================== CANCELACIÓN DE TICKET ====================
    public function confirmCancel()
    {
        $this->resetValidation();
        $this->cancelReason = '';
        $this->confirmingCancel = true;
    }

    public function executeCancel()
    {
        $this->validate(['cancelReason' => 'required|string|min:5']);
        $this->confirmingCancel = false;

        $ticket = Ticket::findOrFail($this->ticketId);

        $ticket->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $this->cancelReason,
        ]);

        session()->forget('open_ticket_id');
        session()->forget('ticket_draft');

        session()->flash('message', 'Ticket cancelado: ' . $this->cancelReason);
        return redirect()->route('tickets.index');
    }

    public function cancelCancel()
    {
        $this->confirmingCancel = false;
        $this->cancelReason = '';
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

    public function goBack()
    {
        if (!$this->ticketId) {
            session()->forget('ticket_draft');
        }
        return redirect()->route('tickets.index');
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
            'requires_contract' => $this->requires_contract,
            'zone_id' => $this->zone_id ?: null,
            'plan_id' => $this->plan_id ?: null,
        ];

        if ($this->ticketId) {
            $ticket = Ticket::findOrFail($this->ticketId);
            $data['status'] = $this->status;
            $ticket->update($data);

            app(SlaService::class)->assignSlaToTicket($ticket);
            if (in_array($ticket->status, ['resolved', 'closed'])) {
                app(SlaService::class)->evaluateSla($ticket);
            }

            session()->flash('message', 'Ticket actualizado correctamente.');
        } else {
            $data['created_by'] = Auth::id();
            $data['status'] = 'pending';
            $ticket = Ticket::create($data);

            $ticket->ticket_code = $this->generateTicketCode($ticket->id);
            $ticket->save();

            app(SlaService::class)->assignSlaToTicket($ticket);

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
        app(WorkOrderService::class)->createFromTicket($ticket, [
            'zone_id' => $ticket->zone_id ?? $this->zone_id,
            'plan_id' => $ticket->plan_id ?? $this->plan_id,
            'requires_noc' => $ticket->requires_noc ?? $this->requires_noc,
            'latitude' => $ticket->client?->latitude,
            'longitude' => $ticket->client?->longitude,
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
            'requires_contract' => 'requires_contract',
            'create_ot' => 'create_ot',
            'priority' => 'priority',
            'zone_id' => 'zone_id',
            'plan_id' => 'plan_id',
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

        // Evaluar SLA para el nivel L1
        app(SlaService::class)->evaluateSla($ticket);

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

    public function confirmGenerateContract()
    {
        $this->validate([
            'client_id' => 'required|exists:clients,id',
            'description' => 'required|string|min:5',
            'service_type_id' => 'required|exists:service_types,id',
        ]);

        $this->confirmingGenerateContract = true;
    }

    public function executeGenerateContract()
    {
        $this->confirmingGenerateContract = false;

        $ticket = Ticket::findOrFail($this->ticketId);
        $serviceType = ServiceType::find($this->service_type_id);
        $serviceName = $serviceType ? $serviceType->name : '';

        $ticket->update([
            'client_id' => $this->client_id,
            'description' => $this->description,
            'service_type' => $serviceName,
            'priority' => $this->priority,
            'origin' => $this->origin,
            'requires_contract' => true,
            'status' => 'pending',
            'l1_ended_at' => now(),
            'contracts_escalated_at' => now(),
        ]);

        app(SlaService::class)->evaluateSla($ticket);

        session()->forget('open_ticket_id');
        session()->forget('ticket_draft');

        session()->flash('message', 'Ticket enviado a Contratos para revisión.');

        return redirect()->route('contracts.inbox', ['ticket_id' => $this->ticketId]);
    }

    public function cancelGenerateContract()
    {
        $this->confirmingGenerateContract = false;
    }

    public function render()
    {
        if ($this->ticketOpened && $this->ticketId) {
            $this->updateElapsedSeconds();
        }

        $serviceTypes = ServiceType::orderBy('name')->get();
        $serviceType = $this->service_type_id ? ServiceType::find($this->service_type_id) : null;
        return view('livewire.tickets.ticket-form', compact('serviceTypes', 'serviceType'))->layout('components.layouts.app');
    }
}