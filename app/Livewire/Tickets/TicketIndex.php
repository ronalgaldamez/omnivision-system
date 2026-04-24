<?php

namespace App\Livewire\Tickets;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;

class TicketIndex extends Component
{
    use WithPagination;

    public $statusFilter = '';
    public $search = '';

    public function render()
    {
        $user = Auth::user();
        $query = Ticket::with('client', 'createdBy');

        if ($user->hasRole('secretary')) {
            $query->where('created_by', $user->id);
        }
        // NOC ve todos los tickets con requires_noc = true
        if ($user->hasRole('noc')) {
            $query->where('requires_noc', true);
        }
        // Supervisor y admin ven todos
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }
        if ($this->search) {
            $query->whereHas('client', fn($q) => $q->where('name', 'like', '%'.$this->search.'%'))
                  ->orWhere('description', 'like', '%'.$this->search.'%');
        }
        $tickets = $query->orderBy('created_at', 'desc')->paginate(15);
        return view('livewire.tickets.ticket-index', compact('tickets'))->layout('components.layouts.app');
    }
}