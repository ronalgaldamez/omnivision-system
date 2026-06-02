<?php

namespace App\Livewire\Mobile;

use Livewire\Component;
use App\Models\WorkOrder;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\TechnicianInventory;
use App\Models\WorkOrderMaterial;
use Illuminate\Support\Facades\Auth;

class WorkOrderShow extends Component
{
    public $workOrder;
    public $confirmingAction = null;
    public $confirmingMessage = '';
    public $hasOpenRequisition = false;
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

    // Coordenadas (se inicializan vacías)
    public $latitude = null;
    public $longitude = null;

    public function mount($id)
    {
        $this->workOrder = WorkOrder::with('technician', 'products.product', 'client', 'ticket.createdBy', 'createdBy')
            ->where('technician_id', Auth::id())
            ->findOrFail($id);

        $this->checkOpenRequisition();
        $this->checkAnotherInProgress();
        $this->loadAvailableProducts();

        $this->technicianHasOpenRequisition = Requisition::where('technician_id', Auth::id())
            ->where('status', 'open')
            ->exists();

        // Inicializar campos técnicos
        $this->wifi_name = $this->workOrder->wifi_name;
        $this->wifi_password = $this->workOrder->wifi_password;
        $this->profile_name = $this->workOrder->profile_name;
        $this->profile_password = $this->workOrder->profile_password;
        $this->mac = $this->workOrder->mac;
        $this->pon = $this->workOrder->pon;
        $this->mufa = $this->workOrder->mufa;
        $this->installation_date = $this->workOrder->installation_date?->format('Y-m-d');

        // Las coordenadas se dejan vacías para que el técnico las complete
        $this->latitude = null;
        $this->longitude = null;

        $user = Auth::user();
        $this->canEditTech = $user->id === $this->workOrder->technician_id
            && in_array($this->workOrder->status, ['pending', 'in_progress']);
    }

    public function saveTechnicalData()
    {
        if (!$this->canEditTech) {
            abort(403);
        }

        $this->validate([
            'wifi_name' => 'nullable|string|max:255',
            'wifi_password' => 'nullable|string|max:255',
            'profile_name' => 'nullable|string|max:255',
            'profile_password' => 'nullable|string|max:255',
            'mac' => 'nullable|string|max:255',
            'pon' => 'nullable|string|max:255',
            'mufa' => 'nullable|string|max:255',
            'installation_date' => 'nullable|date',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        // Actualizar OT
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

        // Actualizar también las coordenadas en el cliente
        $client = $this->workOrder->client;
        if ($client && ($this->latitude !== null || $this->longitude !== null)) {
            $client->update([
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
            ]);
        }

        $this->dispatch('show-toast', type: 'success', message: 'Datos técnicos y coordenadas actualizados.');
    }

    // ========== MÉTODOS EXISTENTES (SIN CAMBIOS) ==========
    protected function checkOpenRequisition()
    {
        $this->hasOpenRequisition = $this->workOrder->requisitions()
            ->where('status', 'open')
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

        $requisition = $this->workOrder->requisitions()->where('status', 'open')->first();
        if (!$requisition) {
            $this->availableProducts = [];
            return;
        }

        $this->availableProducts = RequisitionItem::where('requisition_id', $requisition->id)
            ->with('product')
            ->get()
            ->map(function ($item) {
                $available = $item->quantity_requested - $item->quantity_used;
                return [
                    'requisition_item_id' => $item->id,
                    'product_name' => $item->product->name,
                    'product_sku' => $item->product->sku,
                    'available' => max(0, $available),
                    'quantity' => 0,
                ];
            })
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
        $this->dispatch('show-toast', type: 'success', message: 'Orden iniciada correctamente.');
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

        $this->canEditTech = false;
        $this->checkAnotherInProgress();
        $this->dispatch('show-toast', type: 'success', message: 'Orden pausada. Tiempo guardado.');
    }

    public function resumeWorkOrder()
    {
        if ($this->workOrder->status !== 'paused')
            return;

        $this->workOrder->status = 'in_progress';
        $this->workOrder->started_at = now();
        $this->workOrder->save();

        $this->canEditTech = true;
        $this->checkAnotherInProgress();
        $this->dispatch('show-toast', type: 'success', message: 'Orden reanudada.');
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

        $this->canEditTech = false;

        if ($this->hasOpenRequisition) {
            $this->loadAvailableProducts();
            $this->showConsumptionModal = true;
        } else {
            $this->dispatch('show-toast', type: 'success', message: 'Orden completada.');
        }
    }

    public function saveConsumption()
    {
        $requisition = $this->workOrder->requisitions()->where('status', 'open')->first();
        if (!$requisition) {
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

            $reqItem = RequisitionItem::find($product['requisition_item_id']);

            WorkOrderMaterial::create([
                'work_order_id' => $this->workOrder->id,
                'product_id' => $reqItem->product_id,
                'quantity_used' => $quantity,
                'requisition_item_id' => $product['requisition_item_id'],
            ]);

            if ($reqItem) {
                $reqItem->quantity_used += $quantity;
                $reqItem->save();
            }

            $inventory = TechnicianInventory::where('technician_id', Auth::id())
                ->where('product_id', $reqItem->product_id)
                ->first();
            if ($inventory) {
                $inventory->decrement('quantity_in_hand', $quantity);
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
        return view('livewire.mobile.work-order-show')->layout('components.layouts.app');
    }
}