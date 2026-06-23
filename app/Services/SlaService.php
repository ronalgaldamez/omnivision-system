<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\SlaGoal;
use Carbon\Carbon;

class SlaService
{
    /**
     * Asigna la meta SLA y fecha límite a un ticket según su prioridad y tipo de servicio.
     */
    public function assignSlaToTicket(Ticket $ticket, ?Carbon $referenceTime = null): void
    {
        $serviceType = $ticket->service_type;
        $serviceTypeId = null;
        if ($serviceType) {
            $serviceTypeModel = \App\Models\ServiceType::where('name', $serviceType)->first();
            $serviceTypeId = $serviceTypeModel?->id;
        }

        $goal = SlaGoal::forTicket($ticket->priority, $serviceTypeId)->first();

        if ($goal) {
            $referenceTime ??= now();
            $deadline = $referenceTime->copy()->addMinutes($goal->minutes);
            $ticket->update([
                'sla_goal_id' => $goal->id,
                'sla_deadline_at' => $deadline,
            ]);
        }
    }

    /**
     * Evalúa si el ticket cumplió o no el SLA basado en resolved_at vs sla_deadline_at.
     */
    public function evaluateSla(Ticket $ticket): void
    {
        if (!$ticket->sla_deadline_at) {
            $this->assignSlaToTicket($ticket, $ticket->created_at);
            $ticket->refresh();
        }

        if (!$ticket->sla_deadline_at) {
            return;
        }

        $resolvedAt = $ticket->resolved_at ?? $ticket->l2_ended_at ?? now();
        $slaMet = $resolvedAt <= $ticket->sla_deadline_at;

        $ticket->update([
            'sla_met' => $slaMet,
            'sla_evaluated_at' => now(),
        ]);
    }

    /**
     * Obtiene estadísticas de cumplimiento SLA.
     */
    public function getStats(): array
    {
        $total = Ticket::whereNotNull('sla_evaluated_at')->count();
        $met = Ticket::where('sla_met', true)->count();
        $notMet = Ticket::where('sla_met', false)->count();
        $pending = Ticket::whereNotNull('sla_deadline_at')
            ->whereNull('sla_evaluated_at')
            ->whereNotIn('status', ['cancelled'])
            ->count();

        $byPriority = [];
        foreach (['P1', 'P2', 'P3', 'P4'] as $p) {
            $totalP = Ticket::where('priority', $p)->whereNotNull('sla_evaluated_at')->count();
            $metP = Ticket::where('priority', $p)->where('sla_met', true)->count();
            $byPriority[$p] = [
                'total' => $totalP,
                'met' => $metP,
                'not_met' => $totalP - $metP,
                'percentage' => $totalP > 0 ? round(($metP / $totalP) * 100, 1) : 0,
            ];
        }

        $overallPercentage = $total > 0 ? round(($met / $total) * 100, 1) : 0;

        return compact('total', 'met', 'notMet', 'pending', 'byPriority', 'overallPercentage');
    }

    /**
     * Obtiene tickets próximos a exceder SLA (dentro de los próximos X minutos).
     */
    public function getAtRiskTickets(int $thresholdMinutes = 30)
    {
        return Ticket::whereNotNull('sla_deadline_at')
            ->whereNull('sla_evaluated_at')
            ->whereNotIn('status', ['cancelled', 'resolved', 'closed'])
            ->where('sla_deadline_at', '<=', now()->addMinutes($thresholdMinutes))
            ->where('sla_deadline_at', '>', now())
            ->orderBy('sla_deadline_at')
            ->get();
    }

    /**
     * Obtiene tickets que ya excedieron el SLA.
     */
    public function getOverdueTickets()
    {
        return Ticket::whereNotNull('sla_deadline_at')
            ->whereNull('sla_evaluated_at')
            ->whereNotIn('status', ['cancelled', 'resolved', 'closed'])
            ->where('sla_deadline_at', '<', now())
            ->orderBy('sla_deadline_at')
            ->get();
    }
}
