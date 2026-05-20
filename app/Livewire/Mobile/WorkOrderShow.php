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

    // Modal de consumo al completar
    public $showConsumptionModal = false;
    public $availableProducts = [];
    public $consumptionQuantities = [];

    // Modal de selección de OTs para vincular
    public $showWorkOrderSelectionModal = false;
    public $eligibleWorkOrders = [];
    public $selectedWorkOrdersForLink = [];

    public $technicianHasOpenRequisition = false;

    public function mount($id)
    {
        $this->workOrder = WorkOrder::with('technician', 'products.product', 'client')
            ->where('technician_id', Auth::id())
            ->findOrFail($id);

        $this->checkOpenRequisition();
        $this->checkAnotherInProgress();
        $this->loadAvailableProducts();

        $this->technicianHasOpenRequisition = Requisition::where('technician_id', Auth::id())
            ->where('status', 'open')
            ->exists();
    }

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

    // ========== VINCULACIÓN DE OTs ==========
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

        // Iniciar con la OT actual seleccionada por defecto
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

    // ========== CONFIRMACIONES ==========
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

    // ========== ACCIONES REALES ==========
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

        if ($this->hasOpenRequisition) {
            $this->loadAvailableProducts();
            $this->showConsumptionModal = true;
        } else {
            $this->dispatch('show-toast', type: 'success', message: 'Orden completada.');
        }
    }

    // ========== CONSUMO DE MATERIAL ==========
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

    public function render()
    {
        return view('livewire.mobile.work-order-show')->layout('components.layouts.app');
    }
}