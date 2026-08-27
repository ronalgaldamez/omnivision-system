<?php

namespace App\Notifications;

use App\Models\Requisition;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Notification;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Support\Facades\Log;

class RequisitionSubmittedNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    public function __construct(
        public Requisition $requisition,
        public int $recipientId,
    ) {}

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable): array
    {
        return [
            'requisition_id' => $this->requisition->id,
            'status' => 'submitted',
            'technician' => $this->requisition->technician?->name,
            'message' => "Nueva requisición #{$this->requisition->id} de {$this->requisition->technician?->name} pendiente de aprobación.",
        ];
    }

    public function toBroadcast($notifiable): array
    {
        return $this->toArray($notifiable);
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('App.Models.User.' . $this->recipientId);
    }

    public function broadcastAs(): string
    {
        return 'requisition.submitted';
    }

    public static function notifyWarehouse(Requisition $requisition): void
    {
        foreach (User::role('warehouse')->get() as $warehouseUser) {
            try {
                $warehouseUser->notify(new static($requisition, $warehouseUser->id));
            } catch (\Throwable $e) {
                Log::warning('No se pudo notificar a bodega de la requisición: ' . $e->getMessage());
            }
        }
    }
}
