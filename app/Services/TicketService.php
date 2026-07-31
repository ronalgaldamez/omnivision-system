<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\ServiceType;
use App\Models\ServiceRule;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\Auth;

class TicketService
{
    public function generateTicketCode(Ticket $ticket, string $origin): string
    {
        $user = Auth::user();
        $role = $user?->roles()->first();
        $prefix = $role->prefix ?? 'TK';

        $originMap = [
            'Facebook Messenger' => 'FB',
            'SMS WhatsApp' => 'WH',
            'Llamada de WhatsApp' => 'WHL',
            'Llamada Telefónica' => 'LL',
            'SMS' => 'SMS',
            'Presencial' => 'PR',
            'Otros' => 'OT',
        ];
        $originCode = $originMap[$origin] ?? 'GEN';

        $nextNumber = $this->getNextTicketSequence($prefix, $originCode);

        return sprintf('TK-%s-%s-%04d', $prefix, $originCode, $nextNumber);
    }

    public function getNextTicketSequence(string $prefix, string $originCode): int
    {
        $likePattern = "TK-{$prefix}-{$originCode}-%";
        $lastTicket = Ticket::where('ticket_code', 'like', $likePattern)
            ->orderBy('id', 'desc')
            ->first();

        if (!$lastTicket) {
            return 1;
        }

        $parts = explode('-', $lastTicket->ticket_code);
        $lastNumber = (int) end($parts);
        return $lastNumber + 1;
    }

    public function createWorkOrder(Ticket $ticket, array $extra = []): ?WorkOrder
    {
        if (!$ticket->client_id) {
            return null;
        }

        return app(WorkOrderService::class)->createFromTicket($ticket, $extra);
    }

    public function shouldAutoCreateOt(ServiceType $serviceType, bool $createOt, bool $requiresContract): bool
    {
        $autoOt = ServiceRule::getRule($serviceType->id, 'auto_create_ot', ['enabled' => false]);
        return (($autoOt['enabled'] ?? false) || $createOt) && !$requiresContract;
    }
}
