<?php

namespace App\Livewire\Admin\Clients;

use Livewire\Component;
use App\Models\Client;
use App\Models\Contract;
use App\Models\WorkOrder;

class ClientShow extends Component
{
    public $client;
    public $tickets;
    public $completedWorkOrders;
    public $contracts;

    public function mount($id)
    {
        $this->client = Client::with('phones', 'branch', 'zone', 'plan')->findOrFail($id);
        $this->tickets = $this->client->tickets()
            ->orderBy('created_at', 'desc')
            ->get();
        $this->completedWorkOrders = WorkOrder::whereIn(
            'ticket_id',
            $this->client->tickets()->pluck('id')
        )
            ->where('status', 'completed')
            ->with('ticket')
            ->orderBy('completed_date', 'desc')
            ->get();
        $this->contracts = Contract::where('client_id', $id)
            ->with('plan', 'zone')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function render()
    {
        return view('livewire.admin.clients.client-show')
            ->layout('components.layouts.app');
    }
}