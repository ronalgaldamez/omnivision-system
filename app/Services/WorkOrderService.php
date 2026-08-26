<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\Ticket;
use App\Models\User;
use App\Models\WorkOrder;
use App\Notifications\WorkOrderNotification;
use Illuminate\Support\Facades\Auth;

class WorkOrderService
{
    /**
     * Genera el código de una OT en el formato OT-{prefix}-{origen}-{0001}.
     * Es el ÚNICO punto de generación de códigos de OT.
     */
    public function generateCode(User $creator, ?Ticket $ticket = null): string
    {
        $role = $creator->roles()->first();
        $prefix = $role->prefix ?? 'OT';

        $originMap = [
            'Facebook Messenger' => 'FB',
            'SMS WhatsApp' => 'WH',
            'Llamada de WhatsApp' => 'WHL',
            'Llamada Telefónica' => 'LL',
            'SMS' => 'SMS',
            'Presencial' => 'PR',
            'Otros' => 'OT',
        ];

        $origin = $ticket ? ($originMap[$ticket->origin] ?? 'GEN') : 'GEN';

        $lastCode = WorkOrder::where('code', 'like', "OT-{$prefix}-{$origin}-%")
            ->orderBy('id', 'desc')
            ->value('code');

        $nextNumber = 1;
        if ($lastCode) {
            $parts = explode('-', $lastCode);
            $lastNumber = (int) end($parts);
            $nextNumber = $lastNumber + 1;
        }

        return sprintf('OT-%s-%s-%04d', $prefix, $origin, $nextNumber);
    }

    /**
     * Crea una WorkOrder a partir de un Ticket.
     * Es el ÚNICO punto de creación de OTs en todo el sistema.
     * Incluye guard anti-duplicado: si el ticket ya tiene OT, retorna la existente.
     *
     * @param Ticket $ticket
     * @param array $extra Datos adicionales para mergear (ej: started_at, status override)
     * @return WorkOrder
     */
    public function createFromTicket(Ticket $ticket, array $extra = []): WorkOrder
    {
        // Guard anti-duplicado central (protege SAC, NOC, Contratos y cualquier flujo futuro)
        $existing = WorkOrder::where('ticket_id', $ticket->id)->first();
        if ($existing) {
            return $existing;
        }

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
            'sla_started_at' => now(),
            'created_by'   => Auth::id(),
        ], $extra);

        if (!isset($data['code'])) {
            $data['code'] = $this->generateCode(Auth::user(), $ticket);
        }

        $workOrder = WorkOrder::create($data);

        WorkOrderNotification::notifySupervisors($workOrder, 'created');

        return $workOrder;
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
        // Guard anti-duplicado por tipo de servicio: si el ticket ya tiene una OT
        // del MISMO service_type (ej. instalacion), se reutiliza. Permite que un
        // ticket tenga OTs de fases distintas (verificación + instalación).
        if ($contract->ticket_id) {
            $existing = WorkOrder::where('ticket_id', $contract->ticket_id)
                ->where('service_type', $contract->service_type)
                ->first();
            if ($existing) {
                return $existing;
            }
        }

        $data = array_merge([
            'client_id'    => $contract->client_id,
            'description'  => 'Contrato #' . $contract->id . ' - Seguimiento',
            'service_type' => $contract->service_type,
            'zone_id'      => $contract->zone_id,
            'plan_id'      => $contract->plan_id,
            'latitude'     => $contract->latitude,
            'longitude'    => $contract->longitude,
            'status'       => 'pending',
            'sla_started_at' => now(),
            'created_by'   => Auth::id(),
        ], $extra);

        if ($contract->ticket_id) {
            $data['ticket_id'] = $contract->ticket_id;
        }

        if (!isset($data['code'])) {
            $ticket = $contract->ticket_id ? Ticket::find($contract->ticket_id) : null;
            $data['code'] = $this->generateCode(Auth::user(), $ticket);
        }

        $workOrder = WorkOrder::create($data);

        WorkOrderNotification::notifySupervisors($workOrder, 'created');

        return $workOrder;
    }
}
