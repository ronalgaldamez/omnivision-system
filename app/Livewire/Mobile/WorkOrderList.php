<?php

namespace App\Livewire\Mobile;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\WorkOrder;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;

class WorkOrderList extends Component
{
    use WithPagination;

    public $statusFilter = 'pending,in_progress,paused';
    public $search = '';

    // Modal de creación rápida de OT en campo
    public $showCreateModal = false;
    public $newClientSearch = '';
    public $newClientResults = [];
    public $newClientId = null;
    public $newClientName = '';
    public $newDescription = '';

    public function render()
    {
        $statusArray = explode(',', $this->statusFilter);
        $orders = WorkOrder::with('client')
            ->where('technician_id', Auth::id())
            ->whereIn('status', $statusArray)
            ->when($this->search, function ($q) {
                $q->whereHas('client', function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('address', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('scheduled_date', 'asc')
            ->paginate(10);

        return view('livewire.mobile.work-order-list', compact('orders'))->layout('components.layouts.app');
    }

    // Abrir modal de creación rápida
    public function openCreateModal()
    {
        $this->showCreateModal = true;
        $this->newClientSearch = '';
        $this->newClientResults = [];
        $this->newClientId = null;
        $this->newClientName = '';
        $this->newDescription = '';
    }

    // Cerrar modal
    public function closeCreateModal()
    {
        $this->showCreateModal = false;
    }

    // Búsqueda de cliente en el modal
    public function updatedNewClientSearch()
    {
        if (strlen($this->newClientSearch) >= 2) {
            $this->newClientResults = Client::where('name', 'like', '%' . $this->newClientSearch . '%')
                ->orWhere('phone', 'like', '%' . $this->newClientSearch . '%')
                ->limit(10)
                ->get();
        } else {
            $this->newClientResults = [];
        }
    }

    // Seleccionar cliente del modal
    public function selectNewClient($id, $name)
    {
        $this->newClientId = $id;
        $this->newClientName = $name;
        $this->newClientSearch = $name;
        $this->newClientResults = [];
    }

    // Crear la OT de campo
    public function createFieldOT()
    {
        $this->validate([
            'newClientId' => 'required|exists:clients,id',
            'newDescription' => 'required|string|min:5',
        ]);

        $client = Client::find($this->newClientId);

        $workOrder = WorkOrder::create([
            'technician_id' => Auth::id(),
            'client_id' => $client->id,
            'ticket_id' => null,
            'latitude' => $client->latitude,
            'longitude' => $client->longitude,
            'status' => 'in_progress',
            'scheduled_date' => now()->format('Y-m-d'),
            'notes' => $this->newDescription,
            'description' => $this->newDescription,
            'service_type' => 'trabajo_en_campo',
            'code' => $this->generateFieldWorkOrderCode(),
            'started_at' => now(),
            'assigned_at' => now(),
            'created_by' => Auth::id(),
        ]);

        // Vincular automáticamente a la requisición abierta del técnico (si existe)
        $openRequisition = \App\Models\Requisition::where('technician_id', Auth::id())
            ->where('status', 'open')
            ->first();
        if ($openRequisition) {
            $openRequisition->workOrders()->attach($workOrder->id);
        }

        $this->showCreateModal = false;
        $this->dispatch('show-toast', type: 'success', message: 'OT de campo creada e iniciada.');
        return redirect()->route('mobile.work-orders.show', $workOrder->id);
    }

    // Generar código para OT de campo
    private function generateFieldWorkOrderCode(): string
    {
        $user = Auth::user();
        $role = $user->roles()->first();
        $prefix = $role->prefix ?? 'TC';

        $lastCode = WorkOrder::where('code', 'like', "OT-{$prefix}-CAMPO-%")
            ->orderBy('id', 'desc')
            ->value('code');

        $nextNumber = 1;
        if ($lastCode) {
            $parts = explode('-', $lastCode);
            $lastNumber = (int) end($parts);
            $nextNumber = $lastNumber + 1;
        }

        return sprintf('OT-%s-CAMPO-%04d', $prefix, $nextNumber);
    }
}