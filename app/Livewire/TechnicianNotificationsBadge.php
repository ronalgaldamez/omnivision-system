<?php

namespace App\Livewire;

use Livewire\Component;

class TechnicianNotificationsBadge extends Component
{
    public $count = 0;
    public $notifications = [];

    public function mount()
    {
        $this->updateCount();
    }

    public function updateCount()
    {
        $user = auth()->user();
        $this->count = $user->unreadNotifications->count();
        $this->notifications = $user->notifications()->latest()->take(5)->get();
    }

    public function markAllRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        $this->updateCount();
        $this->dispatch('show-toast', type: 'success', message: 'Notificaciones marcadas como leídas.');
    }

    public function render()
    {
        return view('livewire.technician-notifications-badge');
    }
}
