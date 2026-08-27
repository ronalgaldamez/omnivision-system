<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class TicketRequiresNocNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $ticketCode,
    ) {}

    public function via($notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification): WebPushMessage
    {
        return (new WebPushMessage)
            ->title("Ticket {$this->ticketCode}")
            ->body('Nuevo ticket requiere atención del NOC.')
            ->icon('/android-chrome-192x192.png')
            ->badge('/favicon-32x32.png')
            ->data(['url' => '/noc']);
    }

    public static function notifyNoc(string $ticketCode): void
    {
        foreach (User::role('noc')->get() as $nocUser) {
            try {
                $nocUser->notify(new static($ticketCode));
            } catch (\Throwable $e) {
                Log::warning('No se pudo notificar al NOC (push): ' . $e->getMessage());
            }
        }
    }
}
