<?php

namespace App\Livewire\WorkOrders;

use Livewire\Component;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\Client;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class WorkOrderForm extends Component
{
    public $orderId;
    public $technician_id = null;
    public $client_id;
    public $latitude;
    public $longitude;
    public $status = 'pending';
    public $scheduled_date;
    public $notes;

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

    // Cliente seleccionado (para mostrar info en solo lectura)
    public $selectedClient = null;

    // Búsqueda de clientes
    public $clientSearch = '';
    public $clientSearchResults = [];

    // Modal para crear cliente
    public $showClientModal = false;
    public $modalKey = ''; // Clave única para forzar nueva instancia

    // Permiso granular
    public $canAssign = false;

    // Buscador de técnicos
    public $technicianSearch = '';
    public $technicianResults = [];

    protected function rules()
    {
        $rules = [
            'client_id' => 'required|integer|exists:clients,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'scheduled_date' => 'required|date',
            'notes' => 'required|string|min:5',
            'wifi_name' => 'nullable|string|max:255',
            'wifi_password' => 'nullable|string|max:255',
            'profile_name' => 'nullable|string|max:255',
            'profile_password' => 'nullable|string|max:255',
            'mac' => 'nullable|string|max:255',
            'pon' => 'nullable|string|max:255',
            'mufa' => 'nullable|string|max:255',
            'installation_date' => 'nullable|date',
        ];

        if ($this->canAssign) {
            $rules['technician_id'] = 'required|integer|exists:users,id';
        } else {
            $rules['technician_id'] = 'nullable|integer|exists:users,id';
        }

        return $rules;
    }

    public function mount($id = null, ?int $ticket_id = null)
    {
        $user = Auth::user();
        $this->canAssign = $user->can('assign technicians');

        if ($id) {
            $order = WorkOrder::with('products')->findOrFail($id);
            $this->orderId = $order->id;
            $this->technician_id = $order->technician_id;
            $this->client_id = $order->client_id;
            if ($order->client) {
                $this->selectedClient = $order->client;
                $this->clientSearch = $order->client->name . ' (' . ($order->client->phone ?? 'Sin teléfono') . ')';
            }
            $this->latitude = $order->latitude;
            $this->longitude = $order->longitude;
            $this->status = $order->status;
            $this->scheduled_date = $order->scheduled_date?->format('Y-m-d');
            $this->notes = $order->notes;

            if ($order->technician) {
                $this->technicianSearch = $order->technician->name;
            }

            $this->wifi_name = $order->wifi_name;
            $this->wifi_password = $order->wifi_password;
            $this->profile_name = $order->profile_name;
            $this->profile_password = $order->profile_password;
            $this->mac = $order->mac;
            $this->pon = $order->pon;
            $this->mufa = $order->mufa;
            $this->installation_date = $order->installation_date?->format('Y-m-d');

            $this->canEditTech = $user->id === $order->technician_id
                && in_array($order->status, ['pending', 'in_progress']);
        } else {
            if ($user->cannot('create work_orders')) {
                abort(403, 'No tienes permiso para crear órdenes de trabajo.');
            }
            $this->client_id = null;
            $this->canEditTech = false;

            // Si viene de un ticket, cargar cliente automáticamente
            if ($ticket_id) {
                $ticket = Ticket::with('client')->find($ticket_id);
                if ($ticket) {
                    $client = $ticket->client;
                    if ($client instanceof \Illuminate\Database\Eloquent\Collection) {
                        $client = $client->first();
                    }
                    if ($client) {
                        $this->client_id = (int) $client->id;
                        $this->selectedClient = $client;
                        $this->clientSearch = $client->name . ' (' . ($client->phone ?? 'Sin teléfono') . ')';
                        $this->latitude = $client->latitude;
                        $this->longitude = $client->longitude;
                    }
                }
            }
        }
    }

    public function updatedTechnicianSearch()
    {
        if (strlen($this->technicianSearch) >= 2) {
            $this->technicianResults = User::role('technician')
                ->where('name', 'like', '%' . $this->technicianSearch . '%')
                ->orderBy('name')
                ->limit(10)
                ->get();
        } else {
            $this->technicianResults = [];
        }
    }

    public function selectTechnician($id)
    {
        $technician = User::find($id);
        if ($technician) {
            $this->technician_id = (int) $technician->id;
            $this->technicianSearch = $technician->name;
            $this->technicianResults = [];
        }
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

        $this->client_id = $id;
        $this->selectedClient = $client;
        $this->clientSearch = $name . ($phone ? ' (' . $phone . ')' : '');
        $this->clientSearchResults = [];

        // Cargar coordenadas del cliente
        $this->latitude = $client->latitude;
        $this->longitude = $client->longitude;
    }

    public function openClientModal()
    {
        $this->modalKey = 'client-modal-' . uniqid(); // Nueva clave única
        $this->showClientModal = true;
    }

    public function closeClientModal()
    {
        $this->showClientModal = false;
    }

    private function generateWorkOrderCode(): string
    {
        $user = Auth::user();
        $role = $user->roles()->first();
        $prefix = $role->prefix ?? 'OT';

        $originMap = [
            'Facebook Messenger' => 'FB',
            'SMS WhatsApp' => 'WH',
            'Llamada de WhatsApp' => 'WHL',
            'Llamada Telefónica' => 'LL',
            'SMS' => 'SMS',
            'Presencial' => 'PR',
            'Otros' => 'OT',
        ];

        $ticket = Ticket::find($this->orderId ? WorkOrder::find($this->orderId)->ticket_id : null);
        $origin = $ticket ? $originMap[$ticket->origin] ?? 'GEN' : 'GEN';

        $lastCode = WorkOrder::where('code', 'like', "OT-{$prefix}-{$origin}-%")
            ->orderBy('id', 'desc')
            ->value('code');

        $nextNumber = 1;
        if ($lastCode) {
            $parts = explode('-', $lastCode);
            $lastNumber = (int) end($parts);
            $nextNumber = $lastNumber + 1;
        }

        return sprintf('OT-%s-%s-%04d', $prefix, $origin, $nextNumber);
    }

    public function save()
    {
        try {
            $this->validate();
        } catch (ValidationException $e) {
            foreach ($e->errors() as $field => $messages) {
                foreach ($messages as $message) {
                    $this->dispatch('show-toast', type: 'error', message: $message);
                }
            }
            throw $e;
        }

        $orderData = [
            'technician_id' => $this->technician_id,
            'client_id' => $this->client_id,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'status' => $this->status,
            'scheduled_date' => $this->scheduled_date,
            'notes' => $this->notes,
            'wifi_name' => $this->wifi_name,
            'wifi_password' => $this->wifi_password,
            'profile_name' => $this->profile_name,
            'profile_password' => $this->profile_password,
            'mac' => $this->mac,
            'pon' => $this->pon,
            'mufa' => $this->mufa,
            'installation_date' => $this->installation_date,
        ];

        if ($this->orderId) {
            $order = WorkOrder::findOrFail($this->orderId);
            $order->update($orderData);
            session()->flash('message', 'Orden actualizada correctamente.');
        } else {
            $orderData['code'] = $this->generateWorkOrderCode();
            $orderData['created_by'] = Auth::id();
            $order = WorkOrder::create($orderData);
            session()->flash('message', 'Orden creada correctamente.');
        }

        return redirect()->route('work-orders.index');
    }

    public function render()
    {
        return view('livewire.work-orders.work-order-form')->layout('components.layouts.app');
    }
}