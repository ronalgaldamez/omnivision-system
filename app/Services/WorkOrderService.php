<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\Ticket;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\Auth;

class WorkOrderService
{
    /**
     * Crea una WorkOrder a partir de un Ticket.
     * Es el ÚNICO punto de creación de OTs en todo el sistema.
     *
     * @param Ticket $ticket
     * @param array $extra Datos adicionales para mergear (ej: started_at, status override)
     * @return WorkOrder
     */
    public function createFromTicket(Ticket $ticket, array $extra = []): WorkOrder
    {
        $client = $ticket->client;

        $data = array_merge([
            'ticket_id'    => $ticket->id,
            'client_id'    => $ticket->client_id,
            'description'  => $ticket->description,
            'service_type' => $ticket->service_type,
            'requires_noc' => $ticket->requires_noc ?? false,
            'zone_id'      => $ticket->zone_id,
            'plan_id'      => $ticket->plan_id,
            'latitude'     => $client?->latitude,
            'longitude'    => $client?->longitude,
            'status'       => 'pending',
            'created_by'   => Auth::id(),
        ], $extra);

        return WorkOrder::create($data);
    }

    /**
     * Crea una WorkOrder a partir de un Contrato.
     *
     * @param Contract $contract
     * @param array $extra Datos adicionales para mergear
     * @return WorkOrder
     */
    public function createFromContract(Contract $contract, array $extra = []): WorkOrder
    {
        $data = array_merge([
            'client_id'    => $contract->client_id,
            'description'  => 'Contrato #' . $contract->id . ' - Seguimiento',
            'service_type' => $contract->service_type,
            'zone_id'      => $contract->zone_id,
            'plan_id'      => $contract->plan_id,
            'latitude'     => $contract->latitude,
            'longitude'    => $contract->longitude,
            'status'       => 'pending',
            'created_by'   => Auth::id(),
        ], $extra);

        if ($contract->ticket_id) {
            $data['ticket_id'] = $contract->ticket_id;
        }

        return WorkOrder::create($data);
    }
}
