<?php

namespace App\Livewire\WorkOrders;

use Livewire\Component;
use App\Models\WorkOrder;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\TechnicianInventory;
use App\Models\WorkOrderMaterial;
use Illuminate\Support\Facades\Auth;

class WorkOrderShow extends Component
{
    public $order;
    // Propiedades técnicas
    public $wifi_name;
    public $wifi_password;
    public $profile_name;
    public $profile_password;
    public $mac;
    public $pon;
    public $mufa;
    public $installation_date;
    public $canEditTech = false;

    // Variables requeridas por la vista (work-order-show.blade.php)
    public $hasOpenRequisition = false;
    public $hasAnotherInProgress = false;
    public $technicianHasOpenRequisition = false;

    // Confirmación de acciones
    public $confirmingAction = null;
    public $confirmingOrderId = null;
    public $confirmingMessage = '';

    // Modal de consumo de material
    public $showConsumptionModal = false;
    public $availableProducts = [];
    public $consumptionQuantities = [];

    // Modal de selección de OTs para vincular
    public $showWorkOrderSelectionModal = false;
    public $eligibleWorkOrders = [];
    public $selectedWorkOrdersForLink = [];

    public function mount($id)
    {
        $this->order = WorkOrder::with('technician', 'products.product', 'client.branch', 'client.phones', 'ticket', 'createdBy', 'zone', 'plan')->findOrFail($id);

        // Inicializar campos técnicos desde la orden
        $this->wifi_name = $this->order->wifi_name;
        $this->wifi_password = $this->order->wifi_password;
        $this->profile_name = $this->order->profile_name;
        $this->profile_password = $this->order->profile_password;
        $this->mac = $this->order->mac;
        $this->pon = $this->order->pon;
        $this->mufa = $this->order->mufa;
        $this->installation_date = $this->order->installation_date?->format('Y-m-d');

        // Determinar si el usuario actual puede editar los datos técnicos
        $user = Auth::user();
        $this->canEditTech = $user->id === $this->order->technician_id
            && in_array($this->order->status, ['pending', 'in_progress']);

        $this->checkOpenRequisition();
        $this->checkAnotherInProgress();

        $this->technicianHasOpenRequisition = Requisition::where('technician_id', Auth::id())
            ->whereIn('status', ['open', 'pending', 'approved'])
            ->exists();
    }

    protected function checkOpenRequisition()
    {
        $this->hasOpenRequisition = $this->order->requisitions()
            ->whereIn('status', ['open', 'pending', 'approved'])
            ->exists();
    }

    protected function checkAnotherInProgress()
    {
        $this->hasAnotherInProgress = WorkOrder::where('technician_id', Auth::id())
            ->where('status', 'in_progress')
            ->where('id', '!=', $this->order->id)
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
              ->whereHas('workOrders', fn($w) => $w->where('work_order_id', $this->order->id));
        })
            ->with('product')
            ->get()
            ->groupBy('product_id')
            ->map(function ($items) {
                $first = $items->first();
                $inventoryQty = TechnicianInventory::where('technician_id', auth()->id())
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
        ]);

        $this->order->update([
            'wifi_name' => $this->wifi_name,
            'wifi_password' => $this->wifi_password,
            'profile_name' => $this->profile_name,
            'profile_password' => $this->profile_password,
            'mac' => $this->mac,
            'pon' => $this->pon,
            'mufa' => $this->mufa,
            'installation_date' => $this->installation_date,
        ]);

        $this->dispatch('show-toast', type: 'success', message: 'Datos técnicos actualizados.');
    }

    // Métodos existentes
public function completeWorkOrder()
{
    if (!Auth::user()->can('complete work_orders')) {
        $this->dispatch('show-toast', ['type' => 'error', 'message' => 'No tienes permiso.']);
        return;
    }

    if ($this->order->status === 'completed') {
        $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Ya está completada.']);
        return;
    }

    $this->order->status = 'completed';
    $this->order->completed_date = now();
    $this->order->save();

    // Cerrar el ticket asociado
    if ($this->order->ticket) {
        $this->order->ticket->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);
    }

    $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Orden completada y ticket cerrado.']);
    return redirect()->route('work-orders.index');
}

    public function cancelWorkOrder()
    {
        if (!Auth::user()->can('cancel work orders')) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'No tienes permiso para cancelar.']);
            return;
        }

        if (in_array($this->order->status, ['completed', 'cancelled'])) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'No se puede cancelar una orden ya completada o cancelada.']);
            return;
        }

        $this->order->status = 'cancelled';
        $this->order->save();

        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Orden cancelada.']);
        return redirect()->route('work-orders.index');
    }

    public function getTicketOriginLabel()
    {
        $ticket = $this->order->ticket;
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

    // Métodos placeholder para los botones de acción
    public function promptStartWorkOrder()
    {
        $this->dispatch('show-toast', type: 'info', message: 'Funcionalidad en desarrollo');
    }

    public function promptPauseWorkOrder()
    {
        $this->dispatch('show-toast', type: 'info', message: 'Funcionalidad en desarrollo');
    }

    public function promptCompleteWorkOrder()
    {
        // Redirige al método existente de completar
        $this->completeWorkOrder();
    }

    public function promptResumeWorkOrder()
    {
        $this->dispatch('show-toast', type: 'info', message: 'Funcionalidad en desarrollo');
    }

    // Confirmación genérica
    public function executeConfirmedAction()
    {
        if ($this->confirmingAction === 'delete') {
            // ...
        }
        $this->confirmingAction = null;
        $this->confirmingOrderId = null;
    }

    public function cancelConfirmation()
    {
        $this->confirmingAction = null;
        $this->confirmingOrderId = null;
    }

    // Modal de consumo de material
    public function openConsumptionModal()
    {
        $this->loadAvailableProducts();
        $this->showConsumptionModal = true;
    }

    public function closeConsumptionModal()
    {
        $this->showConsumptionModal = false;
    }

    public function saveConsumption()
    {
        if (!$this->hasOpenRequisition) {
            $this->dispatch('show-toast', type: 'error', message: 'No hay requisición activa.');
            return;
        }

        foreach ($this->availableProducts as $index => $product) {
            $quantity = floatval($this->consumptionQuantities[$index] ?? 0);
            if ($quantity <= 0) continue;

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
                        'work_order_id' => $this->order->id,
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
        }

        $this->closeConsumptionModal();
        $this->dispatch('show-toast', type: 'success', message: 'Consumo registrado.');
    }

    // Modal de selección de OTs para vincular
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

        $this->selectedWorkOrdersForLink = [$this->order->id];
        $this->showWorkOrderSelectionModal = true;
    }

    public function closeWorkOrderSelectionModal()
    {
        $this->showWorkOrderSelectionModal = false;
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

    public function render()
    {
        return view('livewire.work-orders.work-order-show', [
            'order' => $this->order,
            'workOrder' => $this->order,
        ])->layout('components.layouts.app');
    }
}