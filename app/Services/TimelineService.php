<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\WorkOrder;
use Carbon\Carbon;

class TimelineService
{
    /**
     * Construye el timeline jerárquico para un ticket.
     *
     * Padre: Tiempo Global (L1) — desde created_at hasta resolved_at
     * Áreas: SAC, NOC, Supervisor, Técnico — cada una con sub-segmentos (espera + atención)
     */
    public function buildFromTicket(Ticket $ticket): array
    {
        $now = now();

        // El padre está completo solo cuando el ticket se resolvió Y no hay OT activa
        $hasActiveWorkOrder = $ticket->workOrder
            && !in_array($ticket->workOrder->status, ['completed', 'cancelled']);
        $isCancelled = $ticket->status === 'cancelled';
        $isResolved = ($ticket->resolved_at !== null && !$hasActiveWorkOrder) || $isCancelled;
        $endTime = $isResolved ? ($ticket->resolved_at ?? $ticket->cancelled_at ?? $now) : $now;

        $parentSeconds = $ticket->created_at->diffInSeconds($endTime);

        $areas = [];

        // ==========================================
        // ÁREA: SAC
        // ==========================================
        $sacSub = [];

        // Espera SAC (desde que se crea el ticket hasta que SAC lo abre)
        if ($ticket->started_at && $ticket->started_at->gt($ticket->created_at)) {
            $wait = $ticket->created_at->diffInSeconds($ticket->started_at);
            $sacSub[] = $this->makeSubSegment('Espera', $ticket->created_at, $ticket->started_at, $wait, true, true);
        }

        // Atención SAC
        if ($ticket->started_at) {
            $sacEnd = $ticket->l1_ended_at ?? $ticket->escalated_at ?? $endTime;
            $active = $ticket->started_at->diffInSeconds($sacEnd);
            $isSacActive = !$ticket->l1_ended_at && !$isResolved;
            $sacSub[] = $this->makeSubSegment(
                'Atención',
                $ticket->started_at,
                $ticket->l1_ended_at ?? $ticket->escalated_at,
                $active,
                $isSacActive,
                (bool)($ticket->l1_ended_at ?? $isResolved),
            );
        }

        if (!empty($sacSub)) {
            $totalSac = array_sum(array_column($sacSub, 'durationSeconds'));
            $sacCompleted = $ticket->l1_ended_at || $isResolved;
            $areas[] = $this->makeArea(
                key: 'sac',
                label: 'SAC (Atención al Cliente)',
                responsible: $ticket->createdBy?->name,
                icon: 'support_agent',
                color: 'emerald',
                totalSeconds: $totalSac,
                subSegments: $sacSub,
                isActive: !$sacCompleted && !$isResolved,
                isCompleted: $sacCompleted,
            );
        }

        // ==========================================
        // ÁREA: NOC
        // ==========================================
        if ($ticket->requires_noc && $ticket->escalated_at) {
            $nocSub = [];

            // Espera NOC (desde escalado hasta que abren el ticket)
            if ($ticket->l2_started_at && $ticket->l2_started_at->gt($ticket->escalated_at)) {
                $waitNoc = $ticket->escalated_at->diffInSeconds($ticket->l2_started_at);
                $nocSub[] = $this->makeSubSegment('Espera', $ticket->escalated_at, $ticket->l2_started_at, $waitNoc, true, true);
            }

            // Atención NOC
            if ($ticket->l2_started_at) {
                $nocEnd = $ticket->l2_ended_at ?? $endTime;
                $activeNoc = $ticket->l2_started_at->diffInSeconds($nocEnd);
                $nocSub[] = $this->makeSubSegment(
                    'Atención',
                    $ticket->l2_started_at,
                    $ticket->l2_ended_at,
                    $activeNoc,
                    !$ticket->l2_ended_at && !$isResolved,
                    (bool)($ticket->l2_ended_at ?? $isResolved),
                );
            } elseif (!$ticket->l2_started_at) {
                $waitNocPending = $ticket->escalated_at->diffInSeconds($now);
                $nocSub[] = $this->makeSubSegment('Pendiente', $ticket->escalated_at, null, $waitNocPending, true, false);
            }

            $totalNoc = array_sum(array_column($nocSub, 'durationSeconds'));
            $areas[] = $this->makeArea(
                key: 'noc',
                label: 'NOC (Soporte Técnico L2)',
                responsible: $ticket->resolvedBy?->name,
                icon: 'settings_overscan',
                color: 'violet',
                totalSeconds: $totalNoc,
                subSegments: $nocSub,
                isActive: !$ticket->l2_ended_at && !$isResolved,
                isCompleted: (bool)($ticket->l2_ended_at ?? $isResolved),
            );
        }

        // ==========================================
        // ÁREA: OT / Supervisor + Técnico
        // ==========================================
        if ($ticket->workOrder) {
            $wo = $ticket->workOrder;

            // --- Supervisor: Espera de asignación ---
            $techStart = $wo->started_at ?? $wo->assigned_at;
            if ($wo->assigned_at && $wo->assigned_at->gt($wo->created_at)) {
                $waitAssign = $wo->created_at->diffInSeconds($wo->assigned_at);
                $areas[] = $this->makeArea(
                    key: 'supervisor',
                    label: 'Supervisor (Asignación)',
                    responsible: $wo->createdBy?->name,
                    icon: 'supervisor_account',
                    color: 'cyan',
                    totalSeconds: $waitAssign,
                    subSegments: [
                        $this->makeSubSegment('Espera de asignación', $wo->created_at, $wo->assigned_at, $waitAssign, true, true),
                    ],
                    isActive: false,
                    isCompleted: $wo->assigned_at !== null,
                    technician: null,
                );
            }

            // --- Técnico ---
            $techSub = [];

            // Espera técnico (desde asignado hasta que inicia)
            if ($wo->assigned_at && $wo->started_at && $wo->started_at->gt($wo->assigned_at)) {
                $waitTech = $wo->assigned_at->diffInSeconds($wo->started_at);
                $techSub[] = $this->makeSubSegment('Espera', $wo->assigned_at, $wo->started_at, $waitTech, true, true);
            }

            // Trabajo técnico
            if ($wo->started_at) {
                $techEnd = $wo->completed_date ?? ($isResolved ? $endTime : $now);
                $workTech = $wo->started_at->diffInSeconds($techEnd);
                $techSub[] = $this->makeSubSegment(
                    'Trabajo en campo',
                    $wo->started_at,
                    $wo->completed_date,
                    $workTech,
                    $wo->status === 'in_progress',
                    (bool)$wo->completed_date,
                );
            } elseif (!$wo->assigned_at) {
                $waitSecs = $wo->created_at->diffInSeconds($now);
                $techSub[] = $this->makeSubSegment('Pendiente de asignación', $wo->created_at, null, $waitSecs, true, false);
            } elseif (!$wo->started_at) {
                $waitSecs = $wo->assigned_at->diffInSeconds($now);
                $techSub[] = $this->makeSubSegment('Pendiente de inicio', $wo->assigned_at, null, $waitSecs, true, false);
            }

            $totalTech = array_sum(array_column($techSub, 'durationSeconds'));
            $areas[] = $this->makeArea(
                key: 'technician',
                label: 'Técnico en Campo',
                responsible: $wo->technician?->name,
                icon: 'handyman',
                color: 'orange',
                totalSeconds: $totalTech,
                subSegments: $techSub,
                isActive: $wo->status === 'in_progress' || ($wo->status === 'pending' && !$isResolved),
                isCompleted: (bool)$wo->completed_date,
                technician: $wo->technician?->name,
            );

            // Pausas
            $pausesSeconds = 0;
            if ($wo->pauses->isNotEmpty()) {
                foreach ($wo->pauses as $pause) {
                    if ($pause->paused_at && $pause->resumed_at) {
                        $pausesSeconds += $pause->paused_at->diffInSeconds($pause->resumed_at);
                    }
                }
            }
        }

        // ==========================================
        // SLA
        // ==========================================
        $slaInfo = null;
        if ($ticket->sla_deadline_at) {
            $isOver = $now > $ticket->sla_deadline_at;
            $totalSlaSeconds = $ticket->slaGoal?->minutes * 60 ?? 1;
            $elapsed = $ticket->created_at->diffInSeconds($endTime);
            $progress = min(100, ($elapsed / max($totalSlaSeconds, 1)) * 100);
            $remaining = $isOver
                ? $now->diffInSeconds($ticket->sla_deadline_at, false)
                : $now->diffInSeconds($ticket->sla_deadline_at, true);

            $slaInfo = [
                'goal' => $ticket->slaGoal,
                'deadline' => $ticket->sla_deadline_at,
                'met' => $ticket->sla_met,
                'evaluated_at' => $ticket->sla_evaluated_at,
                'progressPercent' => round($progress, 1),
                'isOver' => $isOver,
                'remainingSeconds' => $remaining,
                'remainingFormatted' => $this->formatDuration($remaining),
                'isActive' => is_null($ticket->sla_met),
            ];
        }

        return [
            'parent' => [
                'start' => $ticket->created_at,
                'end' => $isResolved ? ($ticket->resolved_at ?? $ticket->cancelled_at) : null,
                'durationSeconds' => $parentSeconds,
                'durationFormatted' => $this->formatDuration($parentSeconds),
                'isActive' => !$isResolved,
                'isCompleted' => $isResolved,
            ],
            'sla' => $slaInfo,
            'areas' => $areas,
            'pausesSeconds' => $pausesSeconds ?? 0,
            'pausesFormatted' => ($pausesSeconds ?? 0) > 0 ? $this->formatDuration($pausesSeconds) : null,
            'ticket' => $ticket,
            'workOrder' => $ticket->workOrder,
        ];
    }

    /**
     * Construye el timeline para una OT pura (sin ticket).
     */
    public function buildFromWorkOrder(WorkOrder $workOrder): array
    {
        $now = now();
        $isCompleted = $workOrder->status === 'completed' || $workOrder->status === 'cancelled';
        $endTime = $workOrder->completed_date ?? $now;
        $parentSeconds = $workOrder->created_at->diffInSeconds($endTime);

        $areas = [];

        // Supervisor
        if ($workOrder->assigned_at && $workOrder->assigned_at->gt($workOrder->created_at)) {
            $waitAssign = $workOrder->created_at->diffInSeconds($workOrder->assigned_at);
            $areas[] = $this->makeArea(
                key: 'supervisor',
                label: 'Supervisor (Asignación)',
                responsible: $workOrder->createdBy?->name,
                icon: 'supervisor_account',
                color: 'cyan',
                totalSeconds: $waitAssign,
                subSegments: [
                    $this->makeSubSegment('Espera de asignación', $workOrder->created_at, $workOrder->assigned_at, $waitAssign, true, true),
                ],
                isActive: false,
                isCompleted: true,
            );
        }

        // Técnico
        $techSub = [];
        if ($workOrder->assigned_at && $workOrder->started_at && $workOrder->started_at->gt($workOrder->assigned_at)) {
            $waitTech = $workOrder->assigned_at->diffInSeconds($workOrder->started_at);
            $techSub[] = $this->makeSubSegment('Espera', $workOrder->assigned_at, $workOrder->started_at, $waitTech, true, true);
        }
        if ($workOrder->started_at) {
            $techEnd = $workOrder->completed_date ?? $endTime;
            $work = $workOrder->started_at->diffInSeconds($techEnd);
            $techSub[] = $this->makeSubSegment(
                'Trabajo en campo',
                $workOrder->started_at,
                $workOrder->completed_date,
                $work,
                $workOrder->status === 'in_progress',
                (bool)$workOrder->completed_date,
            );
            } elseif (!$workOrder->assigned_at) {
                $waitSecs = $workOrder->created_at->diffInSeconds($now);
                $techSub[] = $this->makeSubSegment('Pendiente de asignación', $workOrder->created_at, null, $waitSecs, true, false);
            } elseif (!$workOrder->started_at) {
                $waitSecs = $workOrder->assigned_at->diffInSeconds($now);
                $techSub[] = $this->makeSubSegment('Pendiente de inicio', $workOrder->assigned_at, null, $waitSecs, true, false);
        }

        if (!empty($techSub)) {
            $totalTech = array_sum(array_column($techSub, 'durationSeconds'));
            $areas[] = $this->makeArea(
                key: 'technician',
                label: 'Técnico en Campo',
                responsible: $workOrder->technician?->name,
                icon: 'handyman',
                color: 'orange',
                totalSeconds: $totalTech,
                subSegments: $techSub,
                isActive: $workOrder->status === 'in_progress',
                isCompleted: (bool)$workOrder->completed_date,
                technician: $workOrder->technician?->name,
            );
        }

        // Pausas
        $pausesSeconds = 0;
        if ($workOrder->pauses->isNotEmpty()) {
            foreach ($workOrder->pauses as $pause) {
                if ($pause->paused_at && $pause->resumed_at) {
                    $pausesSeconds += $pause->paused_at->diffInSeconds($pause->resumed_at);
                }
            }
        }

        return [
            'parent' => [
                'start' => $workOrder->created_at,
                'end' => $workOrder->completed_date,
                'durationSeconds' => $parentSeconds,
                'durationFormatted' => $this->formatDuration($parentSeconds),
                'isActive' => !$isCompleted,
                'isCompleted' => $isCompleted,
            ],
            'sla' => null,
            'areas' => $areas,
            'pausesSeconds' => $pausesSeconds,
            'pausesFormatted' => $pausesSeconds > 0 ? $this->formatDuration($pausesSeconds) : null,
            'ticket' => null,
            'workOrder' => $workOrder,
        ];
    }

    private function makeArea(
        string $key,
        string $label,
        ?string $responsible,
        string $icon,
        string $color,
        int $totalSeconds,
        array $subSegments,
        bool $isActive,
        bool $isCompleted,
        ?string $technician = null,
    ): array {
        return [
            'key' => $key,
            'label' => $label,
            'responsible' => $responsible,
            'icon' => $icon,
            'color' => $color,
            'totalSeconds' => $totalSeconds,
            'totalFormatted' => $this->formatDuration($totalSeconds),
            'subSegments' => $subSegments,
            'isActive' => $isActive,
            'isCompleted' => $isCompleted,
            'technician' => $technician,
        ];
    }

    private function makeSubSegment(
        string $label,
        ?Carbon $start,
        ?Carbon $end,
        int $durationSeconds,
        bool $isActive,
        bool $isCompleted,
    ): array {
        return [
            'label' => $label,
            'start' => $start,
            'end' => $end,
            'durationSeconds' => $durationSeconds,
            'durationFormatted' => $this->formatDuration($durationSeconds),
            'isActive' => $isActive,
            'isCompleted' => $isCompleted,
        ];
    }

    private function formatDuration(int $seconds): string
    {
        $abs = abs($seconds);
        if ($abs < 60) return "{$abs}s";
        if ($abs < 3600) {
            $m = intdiv($abs, 60);
            $s = $abs % 60;
            return $s > 0 ? "{$m}m {$s}s" : "{$m}m";
        }
        $h = intdiv($abs, 3600);
        $m = intdiv($abs % 3600, 60);
        $s = $abs % 60;
        $parts = ["{$h}h"];
        if ($m > 0) $parts[] = "{$m}m";
        if ($s > 0) $parts[] = "{$s}s";
        return implode(' ', $parts);
    }
}
