<?php

namespace App\Livewire\Mobile;

use Livewire\Component;
use App\Models\WorkOrder;
use App\Models\WorkOrderPause;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\TechnicianInventory;
use App\Models\WorkOrderMaterial;
use App\Models\ServiceRule;
use App\Models\PlanRule;
use App\Services\VerificationPricingService;
use App\Services\VerificationPromotionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class WorkOrderShow extends Component
{
    public $workOrder;
    public $confirmingAction = null;
    public $confirmingMessage = '';
    public $promptingRejection = false;
    public $rejectionReason = '';
    public $hasOpenRequisition = false;

    public $hasApprovedRequisition = false;

    public $hasAnotherInProgress = false;
    public $showConsumptionModal = false;
    public $availableProducts = [];
    public $consumptionQuantities = [];

    public $showWorkOrderSelectionModal = false;
    public $eligibleWorkOrders = [];
    public $selectedWorkOrdersForLink = [];

    public $technicianHasOpenRequisition = false;

    // Datos técnicos
    public $wifi_name;
    public $wifi_password;
    public $profile_name;
    public $profile_password;
    public $mac;
    public $pon;
    public $mufa;
    public $installation_date;
    public $canEditTech = false;
    public $isEditing = false;

    // Coordenadas
    public $latitude = null;
    public $longitude = null;

    // Datos técnicos del contrato
    public $access_type = '';
    public $speed = '';
    public $technology = '';
    public $modem_serial = '';
    public $installation_cost = '';
    public $payment_date = '';
    public $extra_tvs = 0;

    // Verificación de instalación
    public $mufa_has_space = null;
    public $drop_distance = null;
    public $verification_price = null;
    public $customer_accepts_cost = null;

    public function getVerificationRulesProperty(): array
    {
        $ticket = $this->workOrder->ticket;
        if (!$this->isVerificationOt() || !$ticket) return [];

        return app(VerificationPricingService::class)->rulesFor($ticket);
    }

    public function getSuggestedVerificationPriceProperty(): float
    {
        $ticket = $this->workOrder->ticket;
        $drop = (float) $this->drop_distance;
        if (!$ticket) return 0;

        $service = app(VerificationPricingService::class);

        // Costo de instalación según la tarifa por zona + campañas (instalación gratis)
        if ($this->isVerificationOt()) {
            return $service->suggestedInstallCostFor($ticket, $drop);
        }

        return 0;
    }

    // Búsqueda de dispositivo
    public $deviceSearch = '';
    public $deviceResults = [];
    public $showDeviceModal = false;
    public $deviceList = [];
    public $deviceListSearch = '';

    // Indicador de borrador
    public $isDraft = false;

    // Indicador de datos técnicos completos (guardados)
    public $technicalDataComplete = false;

    // Tiempos
    public $elapsedSeconds = 0;
    public $totalWorkedSeconds = 0;
    public $pauses = [];

    public function mount($id)
    {
        $this->workOrder = WorkOrder::with('technician', 'vehicle', 'products.product', 'client', 'ticket.createdBy', 'createdBy')
            ->where('technician_id', Auth::id())
            ->findOrFail($id);

        $this->checkOpenRequisition();
        $this->checkAnotherInProgress();
        $this->loadAvailableProducts();

        $this->technicianHasOpenRequisition = Requisition::where('technician_id', Auth::id())
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        // Recuperar borrador de la sesión si existe
        $draft = session()->get('work_order_draft_' . $this->workOrder->id);

        $this->wifi_name = $draft['wifi_name'] ?? $this->workOrder->wifi_name;
        $this->wifi_password = $draft['wifi_password'] ?? $this->workOrder->wifi_password;
        $this->profile_name = $draft['profile_name'] ?? $this->workOrder->profile_name;
        $this->profile_password = $draft['profile_password'] ?? $this->workOrder->profile_password;
        $this->mac = $draft['mac'] ?? $this->workOrder->mac;
        $this->pon = $draft['pon'] ?? $this->workOrder->pon;
        $this->mufa = $draft['mufa'] ?? $this->workOrder->mufa;
        $this->installation_date = $draft['installation_date'] ?? $this->workOrder->installation_date?->format('Y-m-d');

        $this->mufa_has_space = $draft['mufa_has_space'] ?? $this->workOrder->mufa_has_space;
        $this->drop_distance = $draft['drop_distance'] ?? $this->workOrder->drop_distance;
        $this->verification_price = $draft['verification_price'] ?? $this->workOrder->verification_price;
        $this->customer_accepts_cost = $draft['customer_accepts_cost'] ?? $this->workOrder->customer_accepts_cost;

        $contract = $this->workOrder->ticket?->contract;
        $this->access_type = $contract->access_type ?? '';
        $this->speed = $contract->speed ?? '';
        $this->technology = $contract->technology ?? '';
        $this->modem_serial = $contract->modem_serial ?? '';
        $this->installation_cost = $contract->installation_cost ?? '';
        $this->payment_date = $draft['payment_date'] ?? $contract->payment_date ?? '';
        $this->extra_tvs = $draft['extra_tvs'] ?? $contract->extra_tvs ?? 0;

        $client = $this->workOrder->client;
        $this->latitude = $draft['latitude'] ?? $this->workOrder->latitude ?? $client->latitude ?? null;
        $this->longitude = $draft['longitude'] ?? $this->workOrder->longitude ?? $client->longitude ?? null;

        // Determinar si hay cambios sin guardar (badge de borrador)
        $this->updateDraftStatus();
        // Verificar si los datos técnicos ya están completos en la BD
        $this->checkTechnicalDataComplete();

        $user = Auth::user();
        $this->canEditTech = $user->id === $this->workOrder->technician_id
            && in_array($this->workOrder->status, ['in_progress']);

        // Si la OT está en progreso y no tiene datos guardados, iniciar en modo edición
        if ($this->canEditTech && !$this->technicalDataComplete) {
            $this->isEditing = true;
        } else {
            $this->isEditing = false;
        }

        $this->updateTimers();
        $this->loadPauses();
    }

    public function updateTimers()
    {
        if ($this->workOrder->status === 'in_progress' && $this->workOrder->started_at) {
            $this->elapsedSeconds = Carbon::parse($this->workOrder->started_at)->diffInSeconds(now());
        } else {
            $this->elapsedSeconds = 0;
        }

        $this->totalWorkedSeconds = ($this->workOrder->accumulated_seconds ?? 0) + $this->elapsedSeconds;
    }

    public function loadPauses()
    {
        $this->pauses = $this->workOrder->pauses()
            ->orderBy('paused_at', 'asc')
            ->get();
    }

    private function updateDraftStatus()
    {
        if (!$this->workOrder) {
            $this->isDraft = false;
            return;
        }

        $this->isDraft = (
            ($this->wifi_name ?? '') !== ($this->workOrder->wifi_name ?? '') ||
            ($this->wifi_password ?? '') !== ($this->workOrder->wifi_password ?? '') ||
            ($this->profile_name ?? '') !== ($this->workOrder->profile_name ?? '') ||
            ($this->profile_password ?? '') !== ($this->workOrder->profile_password ?? '') ||
            ($this->mac ?? '') !== ($this->workOrder->mac ?? '') ||
            ($this->pon ?? '') !== ($this->workOrder->pon ?? '') ||
            ($this->mufa ?? '') !== ($this->workOrder->mufa ?? '') ||
            ($this->installation_date ?? '') !== ($this->workOrder->installation_date?->format('Y-m-d') ?? '') ||
            ($this->latitude ?? null) != ($this->workOrder->latitude ?? null) ||
            ($this->longitude ?? null) != ($this->workOrder->longitude ?? null) ||
            ($this->mufa_has_space ?? null) != ($this->workOrder->mufa_has_space ?? null) ||
            ($this->drop_distance ?? null) != ($this->workOrder->drop_distance ?? null) ||
            ($this->verification_price ?? null) != ($this->workOrder->verification_price ?? null) ||
            ($this->customer_accepts_cost ?? null) != ($this->workOrder->customer_accepts_cost ?? null)
        );
    }

    /**
     * Indica si la OT es de verificación de instalación (no requiere datos de router).
     * Se evalúa sobre el service_type de la OT, no del ticket: una OT de instalación
     * vinculada a un ticket de verificación debe pedir los datos del router.
     */
    public function isVerificationOt(): bool
    {
        return $this->workOrder?->service_type === 'verificacion_instalacion';
    }

    private function checkTechnicalDataComplete()
    {
        $wo = $this->workOrder;

        // OT de verificación: solo requiere la evaluación de mufa/distancia (y coordenadas).
        if ($this->isVerificationOt()) {
            $this->technicalDataComplete = (
                !is_null($wo->mufa_has_space) &&
                !is_null($wo->drop_distance) &&
                !is_null($wo->latitude) &&
                !is_null($wo->longitude)
            );
            return;
        }

        $this->technicalDataComplete = (
            !empty($wo->wifi_name) &&
            !empty($wo->wifi_password) &&
            !empty($wo->profile_name) &&
            !empty($wo->profile_password) &&
            !empty($wo->mac) &&
            !empty($wo->pon) &&
            !empty($wo->mufa) &&
            !empty($wo->installation_date) &&
            !is_null($wo->latitude) &&
            !is_null($wo->longitude)
        );
    }

    public function enableEditing()
    {
        $this->isEditing = true;
        $this->dispatch('$refresh');
    }

    public function updated($property)
    {
        session()->put('work_order_draft_' . $this->workOrder->id, [
            'wifi_name' => $this->wifi_name,
            'wifi_password' => $this->wifi_password,
            'profile_name' => $this->profile_name,
            'profile_password' => $this->profile_password,
            'mac' => $this->mac,
            'pon' => $this->pon,
            'mufa' => $this->mufa,
            'installation_date' => $this->installation_date,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'mufa_has_space' => $this->mufa_has_space,
            'drop_distance' => $this->drop_distance,
            'verification_price' => $this->verification_price,
            'customer_accepts_cost' => $this->customer_accepts_cost,
            'access_type' => $this->access_type,
            'speed' => $this->speed,
            'technology' => $this->technology,
            'modem_serial' => $this->modem_serial,
            'installation_cost' => $this->installation_cost,
            'payment_date' => $this->payment_date,
            'extra_tvs' => $this->extra_tvs,
        ]);

        $this->updateDraftStatus();
    }

    public function saveTechnicalData()
    {
        if (!$this->canEditTech || !$this->isEditing) {
            abort(403);
        }

        try {
            if ($this->isVerificationOt()) {
                $freeDistance = (int) ($this->verificationRules['free_distance'] ?? 150);
                $hasExcess = (float) ($this->drop_distance ?? 0) > $freeDistance;

                $rules = [
                    'mufa_has_space' => 'required|in:0,1',
                    'drop_distance' => 'required|numeric|min:0',
                    'verification_price' => 'nullable|numeric|min:0',
                    'latitude' => 'nullable|numeric|between:-90,90',
                    'longitude' => 'nullable|numeric|between:-180,180',
                ];

                if ($hasExcess) {
                    $rules['customer_accepts_cost'] = 'required|in:0,1';
                }
            } else {
                $rules = [
                    'wifi_name' => 'required|string|max:255',
                    'wifi_password' => 'required|string|max:255',
                    'profile_name' => 'required|string|max:255',
                    'profile_password' => 'required|string|max:255',
                    'mac' => ['required', 'string', 'max:17', 'regex:/^([0-9A-Fa-f]{2}:){5}[0-9A-Fa-f]{2}$/'],
                    'pon' => 'required|string|max:255',
                    'mufa' => 'required|string|max:255',
                    'installation_date' => 'required|date',
                    'latitude' => 'required|numeric|between:-90,90',
                    'longitude' => 'required|numeric|between:-180,180',
                ];
            }
            $this->validate($rules);
        } catch (ValidationException $e) {
            foreach ($e->errors() as $field => $messages) {
                foreach ($messages as $message) {
                    $this->dispatch('show-toast', type: 'error', message: $message);
                }
            }
            throw $e;
        }

        if ($this->isVerificationOt()) {
            $this->workOrder->update([
                'mufa_has_space' => $this->mufa_has_space,
                'drop_distance' => $this->drop_distance,
                'verification_price' => $this->verification_price ?: null,
                'customer_accepts_cost' => $this->customer_accepts_cost,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
            ]);
        } else {
            $this->workOrder->update([
                'wifi_name' => $this->wifi_name,
                'wifi_password' => $this->wifi_password,
                'profile_name' => $this->profile_name,
                'profile_password' => $this->profile_password,
                'mac' => $this->mac,
                'pon' => $this->pon,
                'mufa' => $this->mufa,
                'installation_date' => $this->installation_date,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
            ]);
        }

        // Sincronizar datos técnicos al contrato asociado
        $contract = $this->workOrder->ticket?->contract;
        if ($contract) {
            $extraTvs = max(0, (int) $this->extra_tvs);

            $contract->update([
                'access_type' => $this->access_type,
                'speed' => $this->speed,
                'technology' => $this->technology,
                'modem_serial' => $this->modem_serial,
                'installation_cost' => $this->installation_cost ?: null,
                'payment_date' => $this->payment_date ?: null,
                'extra_tvs' => $extraTvs,
                'tv_install_fee' => $extraTvs * 6,
                'monthly_extra_fee' => $extraTvs * 1,
            ]);

            // Sincronizar cobros de TV extra (técnico puede registrar en campo)
            $this->syncContractTvCharges($contract, $extraTvs);
        }

        $client = $this->workOrder->client;
        if ($client) {
            $client->update([
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'mufa_has_space' => $this->mufa_has_space,
            'drop_distance' => $this->drop_distance,
            'verification_price' => $this->verification_price ?: null,
        ]);
        }

        session()->forget('work_order_draft_' . $this->workOrder->id);
        $this->isDraft = false;
        $this->isEditing = false;
        $this->checkTechnicalDataComplete();

        $this->dispatch('show-toast', type: 'success', message: 'Datos técnicos y coordenadas actualizados.');
        $this->dispatch('$refresh');
    }

    // ==================== BÚSQUEDA DE DISPOSITIVO ====================
    public function updatedDeviceSearch()
    {
        if (strlen($this->deviceSearch) >= 2) {
            $this->deviceResults = \App\Models\Device::with('product')
                ->where('technician_id', auth()->id())
                ->whereIn('status', ['assigned'])
                ->where(function ($q) {
                    $q->where('mac_address', 'like', '%'.$this->deviceSearch.'%')
                        ->orWhere('pon_sn', 'like', '%'.$this->deviceSearch.'%');
                })
                ->limit(10)->get();
        } else {
            $this->deviceResults = [];
        }
    }

    public function openDeviceModal()
    {
        $this->deviceListSearch = '';
        $this->deviceList = \App\Models\Device::with('product')
            ->where('technician_id', auth()->id())
            ->whereIn('status', ['assigned'])
            ->orderBy('mac_address')
            ->take(50)->get();
        $this->showDeviceModal = true;
    }

    public function closeDeviceModal()
    {
        $this->showDeviceModal = false;
        $this->deviceListSearch = '';
        $this->deviceList = [];
    }

    public function updatedDeviceListSearch()
    {
        if (strlen($this->deviceListSearch) >= 2) {
            $this->deviceList = \App\Models\Device::with('product')
                ->where('technician_id', auth()->id())
                ->whereIn('status', ['assigned'])
                ->where(function ($q) {
                    $q->where('mac_address', 'like', '%'.$this->deviceListSearch.'%')
                        ->orWhere('pon_sn', 'like', '%'.$this->deviceListSearch.'%');
                })
                ->orderBy('mac_address')->take(50)->get();
        } else {
            $this->deviceList = \App\Models\Device::with('product')
                ->where('technician_id', auth()->id())
                ->whereIn('status', ['assigned'])
                ->orderBy('mac_address')->take(50)->get();
        }
    }

    public function selectDevice($id)
    {
        $device = \App\Models\Device::find($id);
        if ($device) {
            $this->mac = $device->mac_address;
            $this->pon = $device->pon_sn ?? '';
            $this->profile_name = $device->default_username ?: ($this->profile_name ?? '');
            $this->profile_password = $device->default_password ?: ($this->profile_password ?? '');
            $this->wifi_name = $device->default_ssid1 ?: ($this->wifi_name ?? '');
            $this->wifi_password = $device->default_lan_key ?: ($this->wifi_password ?? '');
            $this->dispatch('show-toast', type: 'success', message: 'Datos del dispositivo cargados: ' . $device->mac_address);
        }
        $this->closeDeviceModal();
    }

    protected function checkOpenRequisition()
    {
        // En una OT de verificación no se pide requisición de productos.
        if ($this->isVerificationOt()) {
            $this->hasOpenRequisition = false;
            $this->hasApprovedRequisition = false;
            return;
        }

        $this->hasOpenRequisition = $this->workOrder->requisitions()
            ->whereIn('status', ['pending', 'approved'])
            ->exists();
        $this->hasApprovedRequisition = $this->workOrder->requisitions()
            ->whereIn('status', ['approved'])
            ->exists();
    }

    protected function checkAnotherInProgress()
    {
        $this->hasAnotherInProgress = WorkOrder::where('technician_id', Auth::id())
            ->where('status', 'in_progress')
            ->where('id', '!=', $this->workOrder->id)
            ->exists();
    }

    protected function loadAvailableProducts()
    {
        if (!$this->hasOpenRequisition) {
            $this->availableProducts = [];
            return;
        }

        $this->availableProducts = RequisitionItem::whereHas('requisition', function ($q) {
            $q->whereIn('status', ['approved'])
              ->whereHas('workOrders', fn($w) => $w->where('work_order_id', $this->workOrder->id));
        })
            ->with('product')
            ->get()
            ->groupBy('product_id')
            ->map(function ($items) {
                $first = $items->first();
                $inventoryQty = \App\Models\TechnicianInventory::where('technician_id', auth()->id())
                    ->where('product_id', $first->product_id)
                    ->value('quantity_in_hand') ?? 0;
                return [
                    'product_id' => $first->product_id,
                    'product_name' => $first->product->name,
                    'product_sku' => $first->product->sku,
                    'available' => max(0, $inventoryQty),
                    'requisition_item_ids' => $items->pluck('id')->toArray(),
                    'quantity' => 0,
                ];
            })
            ->values()
            ->toArray();
    }

    public function openWorkOrderSelectionModal()
    {
        $userId = Auth::id();
        $this->eligibleWorkOrders = WorkOrder::where('technician_id', $userId)
            ->whereIn('status', ['pending', 'in_progress'])
            ->whereDoesntHave('requisitions', function ($q) {
                $q->whereIn('status', ['approved']);
            })
            ->with('client')
            ->get()
            ->map(function ($wo) {
                return [
                    'id' => $wo->id,
                    'name' => 'OT #' . $wo->id . ' - ' . ($wo->client->name ?? 'N/A'),
                ];
            })
            ->toArray();

        $this->selectedWorkOrdersForLink = [$this->workOrder->id];
        $this->showWorkOrderSelectionModal = true;
    }

    public function linkSelectedWorkOrders()
    {
        $openRequisition = Requisition::where('technician_id', Auth::id())
            ->whereIn('status', ['approved'])
            ->latest('id')
            ->first();

        if (!$openRequisition) {
            $this->dispatch('show-toast', type: 'error', message: 'No tienes una requisición activa (abierta o aprobada).');
            return;
        }

        if (empty($this->selectedWorkOrdersForLink)) {
            $this->dispatch('show-toast', type: 'error', message: 'Selecciona al menos una OT.');
            return;
        }

        foreach ($this->selectedWorkOrdersForLink as $woId) {
            if (!$openRequisition->workOrders()->where('work_order_id', $woId)->exists()) {
                $openRequisition->workOrders()->attach($woId);
            }
        }

        $this->checkOpenRequisition();
        $this->showWorkOrderSelectionModal = false;
        $this->dispatch('show-toast', type: 'success', message: 'OTs vinculadas correctamente.');
    }

    public function closeWorkOrderSelectionModal()
    {
        $this->showWorkOrderSelectionModal = false;
    }

    public function promptStartWorkOrder()
    {
        if ($this->workOrder->status !== 'pending') {
            $this->dispatch('show-toast', type: 'error', message: 'Esta orden ya está en progreso o finalizada.');
            return;
        }
        $this->confirmingAction = 'start';
        $this->confirmingMessage = '¿Estás seguro de iniciar esta orden de trabajo? El tiempo comenzará a registrarse.';
    }

    public function promptCompleteWorkOrder()
    {
        if (!Auth::user()->can('complete work_orders')) {
            $this->dispatch('show-toast', type: 'error', message: 'No tienes permiso para completar esta orden.');
            return;
        }
        if ($this->workOrder->status === 'completed') {
            $this->dispatch('show-toast', type: 'error', message: 'Esta orden ya está completada.');
            return;
        }
        $this->confirmingAction = 'complete';
        $this->confirmingMessage = '¿Marcar esta orden como completada? Esta acción no se puede deshacer.';
    }

    public function promptPauseWorkOrder()
    {
        if ($this->workOrder->status !== 'in_progress') {
            $this->dispatch('show-toast', type: 'error', message: 'Solo se puede pausar una orden en progreso.');
            return;
        }
        $this->confirmingAction = 'pause';
        $this->confirmingMessage = '¿Pausar esta orden? El tiempo trabajado se guardará. Podrás reanudarla más tarde.';
    }

    public function promptResumeWorkOrder()
    {
        if ($this->workOrder->status !== 'paused') {
            $this->dispatch('show-toast', type: 'error', message: 'Solo se puede reanudar una orden pausada.');
            return;
        }
        $this->confirmingAction = 'resume';
        $this->confirmingMessage = '¿Reanudar esta orden? El tiempo continuará registrándose desde ahora.';
    }

    public function promptApproveVerification()
    {
        $ticket = $this->workOrder->ticket;
        if (!$ticket || $ticket->service_type !== 'verificacion_instalacion') {
            $this->dispatch('show-toast', type: 'error', message: 'Esta OT no es de verificación de instalación.');
            return;
        }
        if ($this->workOrder->status !== 'in_progress') {
            $this->dispatch('show-toast', type: 'error', message: 'La OT debe estar en progreso para aprobar la verificación.');
            return;
        }
        if ($ticket->promotion_status) {
            $this->dispatch('show-toast', type: 'error', message: 'La verificación ya fue procesada.');
            return;
        }
        if (is_null($this->workOrder->mufa_has_space) && is_null($this->workOrder->drop_distance)) {
            $this->dispatch('show-toast', type: 'error', message: 'Completá y guardá la verificación (mufa y distancia) antes de aprobar.');
            return;
        }

        // Si la distancia excede la franja gratis, el cliente debe haber aceptado el costo.
        $freeDistance = (int) ($this->verificationRules['free_distance'] ?? 150);
        $hasExcess = (float) ($this->workOrder->drop_distance ?? 0) > $freeDistance;

        if ($hasExcess && !$this->workOrder->customer_accepts_cost) {
            $this->dispatch('show-toast', type: 'error', message: 'El cliente debe aceptar el costo adicional para poder aprobar la verificación.');
            return;
        }

        $this->confirmingAction = 'approve_verification';
        $this->confirmingMessage = '¿Aprobar la verificación y continuar a la fase de contratación? Se generará el contrato automáticamente.';
    }

    public function approveVerification()
    {
        if (!Auth::user()->can('complete work_orders')) {
            $this->dispatch('show-toast', type: 'error', message: 'No tienes permiso para aprobar la verificación.');
            return;
        }
        if ($this->workOrder->status === 'completed') {
            $this->dispatch('show-toast', type: 'error', message: 'Esta orden ya está completada.');
            return;
        }

        $price = (float) ($this->verification_price !== '' && $this->verification_price !== null
            ? $this->verification_price
            : $this->suggestedVerificationPrice);

        $contract = app(VerificationPromotionService::class)->approve($this->workOrder->fresh(['ticket']), $price);

        $this->workOrder->refresh();
        $this->canEditTech = false;
        $this->isEditing = false;
        $this->updateTimers();
        $this->loadPauses();

        $this->dispatch('show-toast', type: 'success', message: 'Verificación aprobada. Contrato ' . ($contract->contract_digital_code ?? '') . ' generado para contratación.');
        $this->dispatch('$refresh');
    }

    public function promptRejectVerification()
    {
        $ticket = $this->workOrder->ticket;
        if (!$ticket || $ticket->service_type !== 'verificacion_instalacion') {
            $this->dispatch('show-toast', type: 'error', message: 'Esta OT no es de verificación de instalación.');
            return;
        }
        if ($this->workOrder->status !== 'in_progress') {
            $this->dispatch('show-toast', type: 'error', message: 'La OT debe estar en progreso para rechazar la verificación.');
            return;
        }
        if ($ticket->promotion_status) {
            $this->dispatch('show-toast', type: 'error', message: 'La verificación ya fue procesada.');
            return;
        }

        // Precargar motivo: si hay excedente y el cliente no aceptó el costo, sugerir ese motivo.
        $freeDistance = (int) ($this->verificationRules['free_distance'] ?? 150);
        $hasExcess = (float) ($this->workOrder->drop_distance ?? 0) > $freeDistance;

        $this->rejectionReason = $hasExcess && !$this->workOrder->customer_accepts_cost
            ? 'Cliente no aceptó el costo adicional.'
            : '';
        $this->promptingRejection = true;
    }

    public function rejectVerification()
    {
        if (!Auth::user()->can('complete work_orders')) {
            $this->dispatch('show-toast', type: 'error', message: 'No tienes permiso para rechazar la verificación.');
            return;
        }
        if (empty(trim($this->rejectionReason))) {
            $this->dispatch('show-toast', type: 'error', message: 'Debés escribir el motivo del rechazo.');
            return;
        }
        if ($this->workOrder->status === 'completed') {
            $this->dispatch('show-toast', type: 'error', message: 'Esta orden ya está completada.');
            return;
        }

        app(VerificationPromotionService::class)->reject($this->workOrder->fresh(['ticket']), trim($this->rejectionReason));

        $this->workOrder->refresh();
        $this->canEditTech = false;
        $this->isEditing = false;
        $this->promptingRejection = false;
        $this->rejectionReason = '';
        $this->updateTimers();
        $this->loadPauses();

        $this->dispatch('show-toast', type: 'success', message: 'Verificación rechazada y ticket cerrado.');
        $this->dispatch('$refresh');
    }

    public function cancelRejection()
    {
        $this->promptingRejection = false;
        $this->rejectionReason = '';
    }

    public function executeConfirmedAction()
    {
        switch ($this->confirmingAction) {
            case 'start':
                $this->startWorkOrder();
                break;
            case 'complete':
                $this->completeWorkOrder();
                break;
            case 'pause':
                $this->pauseWorkOrder();
                break;
            case 'resume':
                $this->resumeWorkOrder();
                break;
            case 'approve_verification':
                $this->approveVerification();
                break;
        }
        $this->confirmingAction = null;
        $this->confirmingMessage = '';
    }

    public function cancelConfirmation()
    {
        $this->confirmingAction = null;
        $this->confirmingMessage = '';
    }

    public function startWorkOrder()
    {
        if ($this->workOrder->status !== 'pending') {
            $this->dispatch('show-toast', type: 'error', message: 'Esta orden ya está en progreso o finalizada.');
            return;
        }
        if ($this->hasAnotherInProgress) {
            $this->dispatch('show-toast', type: 'error', message: 'Ya tienes otra OT en progreso. Finalízala o pausala antes de iniciar esta.');
            return;
        }

        $this->workOrder->status = 'in_progress';
        $this->workOrder->started_at = now();
        $this->workOrder->save();

        $this->checkAnotherInProgress();
        $this->canEditTech = true;
        $this->isEditing = true;
        $this->checkTechnicalDataComplete();

        $this->updateTimers();
        $this->dispatch('show-toast', type: 'success', message: 'Orden iniciada correctamente.');
        $this->dispatch('$refresh');
    }

    public function pauseWorkOrder()
    {
        if ($this->workOrder->status !== 'in_progress')
            return;

        $now = now();
        $elapsed = $this->workOrder->started_at->diffInSeconds($now);
        $this->workOrder->accumulated_seconds += $elapsed;
        $this->workOrder->status = 'paused';
        $this->workOrder->started_at = null;
        $this->workOrder->save();

        // Registrar la pausa
        WorkOrderPause::create([
            'work_order_id' => $this->workOrder->id,
            'paused_at' => $now,
        ]);

        $this->canEditTech = false;
        $this->isEditing = false;
        $this->checkAnotherInProgress();
        $this->updateTimers();
        $this->loadPauses();
        $this->dispatch('show-toast', type: 'success', message: 'Orden pausada. Tiempo guardado.');
        $this->dispatch('$refresh');
    }

    public function resumeWorkOrder()
    {
        if ($this->workOrder->status !== 'paused')
            return;

        // Buscar la última pausa sin reanudar y marcarla
        $lastPause = WorkOrderPause::where('work_order_id', $this->workOrder->id)
            ->whereNull('resumed_at')
            ->orderBy('paused_at', 'desc')
            ->first();
        if ($lastPause) {
            $lastPause->update(['resumed_at' => now()]);
        }

        $this->workOrder->status = 'in_progress';
        $this->workOrder->started_at = now();
        $this->workOrder->save();

        $this->canEditTech = true;
        $this->isEditing = false;
        $this->checkTechnicalDataComplete();
        $this->checkAnotherInProgress();
        $this->updateTimers();
        $this->loadPauses();
        $this->dispatch('show-toast', type: 'success', message: 'Orden reanudada.');
        $this->dispatch('$refresh');
    }

    public function completeWorkOrder()
    {
        if (!Auth::user()->can('complete work_orders')) {
            $this->dispatch('show-toast', type: 'error', message: 'No tienes permiso para completar esta orden.');
            return;
        }
        if ($this->workOrder->status === 'completed')
            return;

        // Una OT de verificación no se cierra directamente: debe pasar por aprobar/rechazar.
        if ($this->isVerificationOt() && !$this->workOrder->ticket?->promotion_status) {
            $this->dispatch('show-toast', type: 'error', message: 'Completá la verificación aprobándola o rechazándola desde la OT.');
            return;
        }

        $totalSeconds = $this->workOrder->accumulated_seconds;
        if ($this->workOrder->started_at) {
            $totalSeconds += $this->workOrder->started_at->diffInSeconds(now());
        }

        $this->workOrder->status = 'completed';
        $this->workOrder->completed_date = now();
        $this->workOrder->accumulated_seconds = $totalSeconds;
        $this->workOrder->save();

        // Si es la OT de instalación, el contrato queda completo y listo para
        // que el agente lo revise y lo envíe al cliente.
        if ($this->workOrder->service_type === 'instalacion') {
            $contract = $this->workOrder->ticket?->contract;
            if ($contract && $contract->status !== 'active') {
                $contract->update([
                    'status' => 'ready_to_send',
                    'signed_at' => $contract->signed_at ?? now(),
                ]);
                try {
                    \App\Events\TicketPromotedToContract::dispatch($this->workOrder->ticket?->ticket_code);
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Broadcast de contrato listo omitido: ' . $e->getMessage());
                }
            }
        }

        // Cerrar el ticket asociado y evaluar su SLA con el cierre real
        if ($this->workOrder->ticket) {
            $ticket = $this->workOrder->ticket;
            $ticket->update([
                'status' => 'resolved',
                'resolved_at' => now(),
            ]);
            app(\App\Services\SlaService::class)->evaluateSla($ticket->fresh());
        }

        $this->canEditTech = false;
        $this->isEditing = false;
        $this->updateTimers();
        $this->loadPauses();

        if ($this->hasOpenRequisition) {
            $this->loadAvailableProducts();
            $this->showConsumptionModal = true;
        } else {
            $this->dispatch('show-toast', type: 'success', message: 'Orden completada y ticket cerrado.');
        }

        $this->dispatch('$refresh');
    }

    public function saveConsumption()
    {
        if (!$this->hasOpenRequisition) {
            $this->dispatch('show-toast', type: 'error', message: 'No hay requisición activa.');
            return;
        }

        foreach ($this->availableProducts as $index => $product) {
            $quantity = floatval($this->consumptionQuantities[$index] ?? 0);
            if ($quantity <= 0)
                continue;

            if ($quantity > $product['available']) {
                $this->dispatch('show-toast', type: 'error', message: "Cantidad excede el disponible para {$product['product_name']}.");
                return;
            }

            $remaining = $quantity;
            $reqItems = RequisitionItem::whereIn('id', $product['requisition_item_ids'])
                ->orderBy('requisition_id')
                ->get();

            foreach ($reqItems as $reqItem) {
                if ($remaining <= 0) break;

                $itemAvailable = $reqItem->quantity_requested - $reqItem->quantity_used;
                $take = min($remaining, $itemAvailable);

                if ($take > 0) {
                    WorkOrderMaterial::create([
                        'work_order_id' => $this->workOrder->id,
                        'product_id' => $product['product_id'],
                        'quantity_used' => $take,
                        'requisition_item_id' => $reqItem->id,
                    ]);

                    $reqItem->quantity_used += $take;
                    $reqItem->save();
                    $remaining -= $take;
                }
            }

            $inventory = TechnicianInventory::where('technician_id', Auth::id())
                ->where('product_id', $product['product_id'])
                ->first();
            if ($inventory) {
                $inventory->decrement('quantity_in_hand', $quantity);
            }

            $devices = \App\Models\Device::where('product_id', $product['product_id'])
                ->where('technician_id', Auth::id())
                ->where('status', 'assigned')
                ->take((int) $quantity)
                ->get();

            foreach ($devices as $device) {
                $device->update([
                    'status' => 'installed',
                    'work_order_id' => $this->workOrder->id,
                    'installed_at' => now(),
                ]);
            }
        }

        $this->showConsumptionModal = false;
        $this->dispatch('show-toast', type: 'success', message: 'Consumo registrado correctamente.');
    }

    public function closeConsumptionModal()
    {
        $this->showConsumptionModal = false;
        $this->dispatch('show-toast', type: 'success', message: 'Orden completada.');
    }

    public function getTicketOriginLabel()
    {
        $ticket = $this->workOrder->ticket;
        if (!$ticket)
            return null;
        $map = [
            'Facebook Messenger' => 'Facebook Messenger',
            'SMS WhatsApp' => 'SMS WhatsApp',
            'Llamada de WhatsApp' => 'Llamada de WhatsApp',
            'Llamada Telefónica' => 'Llamada Telefónica',
            'SMS' => 'SMS',
            'Presencial' => 'Presencial',
            'Otros' => 'Otros',
        ];
        return $map[$ticket->origin] ?? $ticket->origin ?? 'Desconocido';
    }

    /**
     * Sincroniza los cobros de TV extra en el contrato desde la instalación en campo.
     */
    protected function syncContractTvCharges(\App\Models\Contract $contract, int $count): void
    {
        $contract->charges()->where('type', 'extra_tv')->delete();

        if ($count <= 0) {
            return;
        }

        if ($contract->tv_install_fee > 0) {
            $contract->charges()->create([
                'client_id' => $contract->client_id,
                'type' => 'extra_tv',
                'description' => "Instalación de TV extra x{$count}",
                'amount' => $contract->tv_install_fee,
                'is_recurring' => false,
                'quantity' => $count,
            ]);
        }

        if ($contract->monthly_extra_fee > 0) {
            $contract->charges()->create([
                'client_id' => $contract->client_id,
                'type' => 'extra_tv',
                'description' => "Recargo mensual TV extra x{$count}",
                'amount' => $contract->monthly_extra_fee,
                'is_recurring' => true,
                'recurring_period' => 'monthly',
                'quantity' => $count,
            ]);
        }
    }

    public function render()
    {
        // Actualizar cronómetro en cada render si la OT está en progreso
        if ($this->workOrder->status === 'in_progress' && $this->workOrder->started_at) {
            $this->updateTimers();
        }

        return view('livewire.mobile.work-order-show')->layout('components.layouts.app');
    }
}