<?php

namespace App\Notifications;

use App\Models\Requisition;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Notification;
use Illuminate\Broadcasting\PrivateChannel;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class RequisitionStatusNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    public function __construct(
        public Requisition $requisition,
        public string $status,
        public ?string $reason = null,
    ) {}

    public function via($notifiable): array
    {
        return ['database', 'broadcast', WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification): WebPushMessage
    {
        return (new WebPushMessage)
            ->title("Requisición #{$this->requisition->id}")
            ->body($this->toArray($notifiable)['message'])
            ->icon('/android-chrome-192x192.png')
            ->badge('/favicon-32x32.png')
            ->data(['url' => "/requisitions/{$this->requisition->id}/show"]);
    }

    public function toArray($notifiable): array
    {
        return [
            'requisition_id' => $this->requisition->id,
            'status' => $this->status,
            'reason' => $this->reason,
            'message' => $this->status === 'approved'
                ? "Tu requisición #{$this->requisition->id} fue aprobada y los productos fueron entregados."
                : "Tu requisición #{$this->requisition->id} fue rechazada." . ($this->reason ? " Motivo: {$this->reason}" : ''),
        ];
    }

    public function toBroadcast($notifiable): array
    {
        return $this->toArray($notifiable);
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('App.Models.User.' . $this->requisition->technician_id);
    }

    public function broadcastAs(): string
    {
        return 'requisition.status';
    }
}
