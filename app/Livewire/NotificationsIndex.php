<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;

class NotificationsIndex extends Component
{
    use WithPagination;

    public $filter = '';

    public function markAsRead($id)
    {
        auth()->user()->notifications()->where('id', $id)->update(['read_at' => now()]);
        $this->dispatch('show-toast', type: 'success', message: 'Notificación marcada como leída.');
    }

    public function markAllRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        $this->dispatch('show-toast', type: 'success', message: 'Todas las notificaciones marcadas como leídas.');
        $this->dispatch('refresh-notifications-badge');
    }

    public function render()
    {
        $notifications = auth()->user()->notifications()
            ->when($this->filter === 'unread', fn($q) => $q->whereNull('read_at'))
            ->when($this->filter === 'read', fn($q) => $q->whereNotNull('read_at'))
            ->latest()
            ->paginate(20);

        $unreadCount = auth()->user()->unreadNotifications->count();

        return view('livewire.notifications-index', compact('notifications', 'unreadCount'))
            ->layout('components.layouts.app');
    }
}
