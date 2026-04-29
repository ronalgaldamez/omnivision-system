<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;

class NotificationsBadge extends Component
{
    public $pendingNocTickets = 0;

    public function mount()
    {
        $this->updateCount();
    }

    public function updateCount()
    {
        if (Auth::check() && Auth::user()->can('access noc panel')) {
            $this->pendingNocTickets = Ticket::where('requires_noc', true)
                ->where('status', 'pending')
                ->count();
        } else {
            $this->pendingNocTickets = 0;
        }
    }

    // Escucha el evento global 'ticket-created-for-noc'
    protected $listeners = [
        'ticket-created-for-noc' => 'refreshBadge',
    ];

    public function refreshBadge()
    {
        $this->updateCount();
    }

    public function render()
    {
        return view('livewire.notifications-badge');
    }
}