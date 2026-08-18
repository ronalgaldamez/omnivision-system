<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketPromotedToContract implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public ?string $ticketCode = null) {}

    public function broadcastOn(): Channel
    {
        return new Channel('contracts.tickets');
    }

    public function broadcastAs(): string
    {
        return 'ticket.promoted.contract';
    }
}
