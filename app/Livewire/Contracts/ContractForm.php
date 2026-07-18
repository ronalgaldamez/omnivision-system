<?php

namespace App\Livewire\Contracts;

use Livewire\Component;
use App\Models\Client;
use App\Models\Contract;
use App\Models\Plan;
use App\Models\Ticket;
use App\Models\Zone;
use App\Models\ServiceType;
use App\Services\WorkOrderService;
use Illuminate\Support\Facades\Auth;

class ContractForm extends Component
{
    public $ticket_id = null;
    public $fromTicket = false;
    public $ticketData = null;

    public $client_id = '';
    public $clientSearch = '';
    public $clientSearchResults = [];
    public $selectedClient = null;

    public $plan_id = '';
    public $zone_id = '';
    public $service_type = '';
    public $price = '';
    public $installation_address = '';
    public $latitude = '';
    public $longitude = '';
    public $contract_date = '';
    public $status = 'active';

    public $availablePlans = [];
    public $availableZones = [];
    public $availableServiceTypes = [];

    public $showClientModal = false;
    public $showClientListModal = false;
    public $clientListSearch = '';
    public $clientListResults = [];

    protected function rules()
    {
        return [
            'plan_id' => 'nullable|exists:plans,id',
            'zone_id' => 'nullable|exists:zones,id',
            'price' => 'nullable|numeric|min:0',
            'installation_address' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'contract_date' => 'required|date',
            'status' => 'required|in:active,suspended,cancelled',
        ];
    }

    public function mount(?int $ticket_id = null)
    {
        $this->contract_date = now()->format('Y-m-d');
        $this->availablePlans = Plan::where('is_active', true)->orderBy('name')->get()->toArray();
        $this->availableZones = Zone::orderBy('name')->get(['id', 'name'])->toArray();
        $this->availableServiceTypes = ServiceType::where('requires_contract', true)
            ->orderBy('name')->get(['id', 'name'])->toArray();

        if ($ticket_id) {
            $ticket = Ticket::with('client')->find($ticket_id);
            if (!$ticket || !$ticket->requires_contract) {
                abort(404);
            }

            $this->fromTicket = true;
            $this->ticket_id = $ticket->id;
            $this->ticketData = $ticket;

            $client = $ticket->client;
            $this->selectedClient = $client;
            $this->client_id = $client->id;
            $this->clientSearch = $client->name;

            $this->service_type = $ticket->service_type;
            $this->plan_id = $ticket->plan_id ?? '';
            $this->zone_id = $ticket->zone_id ?? '';
            $this->installation_address = $client?->installation_address ?? $client?->address ?? '';
            $this->latitude = $client?->latitude ?? '';
            $this->longitude = $client?->longitude ?? '';
        }
    }

    public function updatedClientSearch($value)
    {
        if (strlen($value) < 2) {
            $this->clientSearchResults = [];
            return;
        }
        $this->clientSearchResults = Client::where('name', 'like', "%{$value}%")
            ->orWhere('phone', 'like', "%{$value}%")
            ->orWhere('document_number', 'like', "%{$value}%")
            ->limit(10)
            ->get()
            ->toArray();
    }

    public function selectClient($id)
    {
        $this->selectedClient = Client::find($id);
        $this->client_id = $id;
        $this->clientSearch = $this->selectedClient?->name ?? '';
        $this->installation_address = $this->selectedClient?->address ?? '';
        $this->clientSearchResults = [];
    }

    public function updatedClientListSearch($value)
    {
        if (strlen($value) < 2) {
            $this->clientListResults = [];
            return;
        }
        $this->clientListResults = Client::where('name', 'like', "%{$value}%")
            ->orWhere('phone', 'like', "%{$value}%")
            ->orWhere('document_number', 'like', "%{$value}%")
            ->limit(10)
            ->get()
            ->toArray();
    }

    public function selectFromList($id)
    {
        $this->selectClient($id);
        $this->showClientListModal = false;
    }

    #[On('clientCreated')]
    public function handleClientCreated($id, $name, $phone = null)
    {
        $this->selectClient((int) $id);
        $this->showClientModal = false;
        $this->dispatch('show-toast', type: 'success', message: 'Cliente creado correctamente.');
    }

    public function save()
    {
        $this->validate();

        $data = [
            'client_id' => $this->client_id,
            'plan_id' => $this->plan_id ?: null,
            'zone_id' => $this->zone_id ?: null,
            'service_type' => $this->fromTicket ? $this->ticketData->service_type : $this->service_type,
            'price' => $this->price ?: null,
            'installation_address' => $this->installation_address,
            'latitude' => $this->latitude ?: null,
            'longitude' => $this->longitude ?: null,
            'contract_date' => $this->contract_date,
            'status' => $this->status,
        ];

        if ($this->fromTicket) {
            $data['ticket_id'] = $this->ticket_id;
        }

        $contract = Contract::create($data);

        if ($this->fromTicket) {
            $ticket = Ticket::with('client')->find($this->ticket_id);
            if ($ticket) {
                app(WorkOrderService::class)->createFromTicket($ticket);

                $ticket->update([
                    'contracts_ended_at' => now(),
                    'status' => 'in_progress',
                ]);
                app(\App\Services\SlaService::class)->evaluateSla($ticket);
            }

            session()->flash('message', 'Contrato #' . $contract->id . ' creado correctamente.');
            return redirect()->route('contracts.inbox', ['ticket_id' => $this->ticket_id]);
        }

        session()->flash('message', 'Contrato #' . $contract->id . ' creado correctamente.');
        return redirect()->route('contracts.index');
    }

    public function render()
    {
        return view('livewire.contracts.contract-form')
            ->layout('components.layouts.app');
    }
}
