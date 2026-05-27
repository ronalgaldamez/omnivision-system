<?php

namespace App\Livewire\WorkOrders;

use Livewire\Component;
use App\Models\WorkOrder;
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
        $this->order = WorkOrder::with('technician', 'products.product', 'client', 'ticket', 'createdBy')->findOrFail($id);

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

        // TODO: Implementar la lógica real para estos flags
        $this->hasOpenRequisition = false;
        $this->hasAnotherInProgress = false;
        $this->technicianHasOpenRequisition = false;
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
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'No tienes permiso.']);
            return;
        }

        if ($this->order->status === 'completed') {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'Ya está completada.']);
            return;
        }

        $this->order->status = 'completed';
        $this->order->completed_date = now();
        $this->order->save();

        $this->dispatch('showToast', ['type' => 'success', 'message' => 'Orden completada.']);
        return redirect()->route('work-orders.index');
    }

    public function cancelWorkOrder()
    {
        if (!Auth::user()->can('cancel work orders')) {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'No tienes permiso para cancelar.']);
            return;
        }

        if (in_array($this->order->status, ['completed', 'cancelled'])) {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'No se puede cancelar una orden ya completada o cancelada.']);
            return;
        }

        $this->order->status = 'cancelled';
        $this->order->save();

        $this->dispatch('showToast', ['type' => 'success', 'message' => 'Orden cancelada.']);
        return redirect()->route('work-orders.index');
    }

    public function getTicketOriginLabel()
    {
        $ticket = $this->order->ticket;
        if (!$ticket) return null;
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
        $this->showConsumptionModal = true;
        // TODO: cargar availableProducts
    }

    public function closeConsumptionModal()
    {
        $this->showConsumptionModal = false;
    }

    public function saveConsumption()
    {
        $this->closeConsumptionModal();
        $this->dispatch('show-toast', type: 'success', message: 'Consumo guardado (demo).');
    }

    // Modal de selección de OTs para vincular
    public function openWorkOrderSelectionModal()
    {
        $this->showWorkOrderSelectionModal = true;
        // TODO: cargar eligibleWorkOrders
    }

    public function closeWorkOrderSelectionModal()
    {
        $this->showWorkOrderSelectionModal = false;
    }

    public function linkSelectedWorkOrders()
    {
        $this->closeWorkOrderSelectionModal();
        $this->dispatch('show-toast', type: 'success', message: 'OTs vinculadas (demo).');
    }

    public function render()
    {
        return view('livewire.work-orders.work-order-show', [
            'order' => $this->order,
            'workOrder' => $this->order,
        ])->layout('components.layouts.app');
    }
}