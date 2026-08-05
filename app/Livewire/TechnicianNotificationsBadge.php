<?php

namespace App\Livewire;

use App\Models\Setting;
use Livewire\Component;

class TechnicianNotificationsBadge extends Component
{
    public $count = 0;
    public $notifications = [];
    public $soundEnabled = true;

    public function mount()
    {
        $this->soundEnabled = Setting::get('user_notif_sound_' . auth()->id(), 'enabled') !== 'silenced';
        $this->updateCount();
    }

    public function updateCount()
    {
        $user = auth()->user();
        $this->count = $user->unreadNotifications->count();
        $this->notifications = $user->notifications()->latest()->take(10)->get();
    }

    public function markAsRead($id)
    {
        auth()->user()->notifications()->where('id', $id)->update(['read_at' => now()]);
        $this->updateCount();
    }

    public function markAllRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        $this->updateCount();
        $this->dispatch('show-toast', type: 'success', message: 'Notificaciones marcadas como leídas.');
    }

    protected $listeners = [
        'refresh-notifications-badge' => 'updateCount',
    ];

    public function toggleSound()
    {
        $this->soundEnabled = !$this->soundEnabled;
        Setting::set('user_notif_sound_' . auth()->id(), $this->soundEnabled ? 'enabled' : 'silenced');
        $this->dispatch('notif-sound-setting-changed');
        $this->dispatch('show-toast', type: 'success', message: $this->soundEnabled ? 'Sonido de notificaciones activado.' : 'Sonido de notificaciones silenciado.');
    }

    public function render()
    {
        return view('livewire.technician-notifications-badge');
    }
}
