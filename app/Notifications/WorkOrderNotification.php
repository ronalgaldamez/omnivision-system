<?php

namespace App\Notifications;

use App\Models\WorkOrder;
use App\Models\Zone;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Notification;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Support\Facades\Log;

class WorkOrderNotification extends Notification implements ShouldBroadcast
{
    use Queueable;

    public function __construct(
        public WorkOrder $workOrder,
        public string $event,
        public int $recipientId,
    ) {}

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable): array
    {
        return [
            'work_order_id' => $this->workOrder->id,
            'code' => $this->workOrder->code,
            'event' => $this->event,
            'message' => $this->message(),
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
        return 'work-order.notification';
    }

    private function message(): string
    {
        return match ($this->event) {
            'created' => "Nueva OT {$this->workOrder->code} creada en tu zona.",
            'assigned' => "La OT {$this->workOrder->code} fue asignada a un técnico.",
            'assigned_to_technician' => "Se te asignó la OT {$this->workOrder->code}.",
            'started' => "La OT {$this->workOrder->code} fue iniciada por el técnico.",
            'paused' => "La OT {$this->workOrder->code} fue pausada.",
            'resumed' => "La OT {$this->workOrder->code} fue reanudada.",
            'completed' => "La OT {$this->workOrder->code} fue completada.",
            'cancelled' => "La OT {$this->workOrder->code} fue cancelada.",
            default => "Actualización de la OT {$this->workOrder->code}.",
        };
    }

    /**
     * Notifica a los supervisores efectivos de la zona de la OT.
     * Si la OT no tiene zona o no hay supervisores, no hace nada.
     */
    public static function notifySupervisors(WorkOrder $workOrder, string $event): void
    {
        if (!$workOrder->zone_id) {
            return;
        }

        $zone = Zone::with('supervisors')->find($workOrder->zone_id);
        if (!$zone) {
            return;
        }

        foreach ($zone->effectiveSupervisors() as $supervisor) {
            try {
                $supervisor->notify(new static($workOrder, $event, $supervisor->id));
            } catch (\Throwable $e) {
                Log::warning('No se pudo notificar al supervisor de OT: ' . $e->getMessage());
            }
        }
    }
}

