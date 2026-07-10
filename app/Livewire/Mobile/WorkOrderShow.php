<?php

namespace App\Livewire\Mobile;

use Livewire\Component;
use App\Models\WorkOrder;
use App\Models\WorkOrderPause;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\TechnicianInventory;
use App\Models\WorkOrderMaterial;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class WorkOrderShow extends Component
{
    public $workOrder;
    public $confirmingAction = null;
    public $confirmingMessage = '';
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
        $this->workOrder = WorkOrder::with('technician', 'products.product', 'client', 'ticket.createdBy', 'createdBy')
            ->where('technician_id', Auth::id())
            ->findOrFail($id);

        $this->checkOpenRequisition();
        $this->checkAnotherInProgress();
        $this->loadAvailableProducts();

        $this->technicianHasOpenRequisition = Requisition::where('technician_id', Auth::id())
            ->whereIn('status', ['open', 'pending', 'approved'])
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
            ($this->longitude ?? null) != ($this->workOrder->longitude ?? null)
        );
    }

    private function checkTechnicalDataComplete()
    {
        $wo = $this->workOrder;
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
        ]);

        $this->updateDraftStatus();
    }

    public function saveTechnicalData()
    {
        if (!$this->canEditTech || !$this->isEditing) {
            abort(403);
        }

        try {
            $this->validate([
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
            ]);
        } catch (ValidationException $e) {
            foreach ($e->errors() as $field => $messages) {
                foreach ($messages as $message) {
                    $this->dispatch('show-toast', type: 'error', message: $message);
                }
            }
            throw $e;
        }

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

        $client = $this->workOrder->client;
        if ($client) {
            $client->update([
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
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
        $this->hasOpenRequisition = $this->workOrder->requisitions()
            ->whereIn('status', ['open', 'pending', 'approved'])
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
            $q->whereIn('status', ['open', 'approved'])
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
                $q->where('status', 'open');
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
            ->where('status', 'open')
            ->first();

        if (!$openRequisition) {
            $this->dispatch('show-toast', type: 'error', message: 'No tienes una requisición abierta.');
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

        $totalSeconds = $this->workOrder->accumulated_seconds;
        if ($this->workOrder->started_at) {
            $totalSeconds += $this->workOrder->started_at->diffInSeconds(now());
        }

        $this->workOrder->status = 'completed';
        $this->workOrder->completed_date = now();
        $this->workOrder->accumulated_seconds = $totalSeconds;
        $this->workOrder->save();

        // Cerrar el ticket asociado
        if ($this->workOrder->ticket) {
            $this->workOrder->ticket->update([
                'status' => 'resolved',
                'resolved_at' => now(),
            ]);
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

    public function render()
    {
        // Actualizar cronómetro en cada render si la OT está en progreso
        if ($this->workOrder->status === 'in_progress' && $this->workOrder->started_at) {
            $this->updateTimers();
        }

        return view('livewire.mobile.work-order-show')->layout('components.layouts.app');
    }
}