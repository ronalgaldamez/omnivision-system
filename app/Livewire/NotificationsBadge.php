<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;

class NotificationsBadge extends Component
{
    public $pendingNocTickets = 0;
    public $notifications = [];
    public $pollingInterval = 30; // NUEVO: se cargará desde la configuración
    public $soundEnabled = true;

    public function mount()
    {
        $this->pollingInterval = (int) \App\Models\Setting::get('noc_polling_interval', 30);
        $this->soundEnabled = \App\Models\Setting::get('user_notif_sound_' . auth()->id(), 'enabled') !== 'silenced';
        $this->updateCount();
    }

    public function toggleSound()
    {
        $this->soundEnabled = !$this->soundEnabled;
        \App\Models\Setting::set('user_notif_sound_' . auth()->id(), $this->soundEnabled ? 'enabled' : 'silenced');
        $this->dispatch('notif-sound-setting-changed');
        $this->dispatch('show-toast', type: 'success', message: $this->soundEnabled ? 'Sonido de notificaciones activado.' : 'Sonido de notificaciones silenciado.');
    }

    public function updateCount()
    {
        if (Auth::check() && Auth::user()->can('access noc panel')) {
            $newCount = Ticket::where('requires_noc', true)
                ->where('status', 'pending')
                ->count();

            // Si la cuenta aumentó y no es la carga inicial, notificar
            if ($newCount > $this->pendingNocTickets && $this->pendingNocTickets > 0) {
                $this->dispatch('show-toast', type: 'info', message: 'Nuevo ticket requiere atención del NOC.');
            }

            $this->pendingNocTickets = $newCount;

            $this->notifications = Ticket::with('client')
                ->where('requires_noc', true)
                ->where('status', 'pending')
                ->latest()
                ->take(5)
                ->get(['id', 'ticket_code', 'client_id', 'description', 'created_at']);
        } else {
            $this->pendingNocTickets = 0;
            $this->notifications = [];
        }
    }

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