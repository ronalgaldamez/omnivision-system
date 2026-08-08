<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Contract;
use App\Models\DistributionShipment;
use App\Models\Movement;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Requisition;
use App\Models\TechnicianReturn;
use App\Models\Ticket;
use App\Models\User;
use App\Models\WorkOrder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Servicio de métricas para la vista de Rendimiento Global (/reports/performance).
 * Toda la lógica de negocio vive acá (nunca en el componente Livewire).
 *
 * Filtros soportados: fechaInicio (Y-m-d), fechaFin (Y-m-d), departamento, branchId, planId.
 */
class PerformanceReportService
{
    // ══════════════════════ FILTROS Y PERIODOS ══════════════════════

    public function dates(array $filters): array
    {
        $start = Carbon::parse($filters['fechaInicio'] ?? now()->startOfMonth()->toDateString())->startOfDay();
        $end = Carbon::parse($filters['fechaFin'] ?? now()->endOfMonth()->toDateString())->endOfDay();

        if ($end->lt($start)) {
            [$start, $end] = [$end, $start];
        }

        return [$start, $end];
    }

    /**
     * Aplica filtros de cliente (departamento, sucursal) y plan a un query.
     */
    private function scopeClient(Builder $q, array $filters): Builder
    {
        return $q
            ->when(!empty($filters['departamento']), fn(Builder $q) => $q->whereHas('client', fn($c) => $c->where('departamento', $filters['departamento'])))
            ->when(!empty($filters['branchId']), fn(Builder $q) => $q->whereHas('client', fn($c) => $c->where('branch_id', $filters['branchId'])))
            ->when(!empty($filters['planId']), fn(Builder $q) => $q->where('plan_id', $filters['planId']));
    }

    private function baseContract(array $filters): Builder
    {
        return $this->scopeClient(Contract::query(), $filters);
    }

    private function baseWorkOrder(array $filters): Builder
    {
        return $this->scopeClient(WorkOrder::query(), $filters);
    }

    private function baseTicket(array $filters): Builder
    {
        return $this->scopeClient(Ticket::query(), $filters);
    }

    /**
     * Series por día (rangos cortos <= 45 días) o por mes (rangos largos).
     */
    public function seriesKeys(array $filters): array
    {
        [$start, $end] = $this->dates($filters);
        $keys = [];

        if ($this->periodType($start, $end) === 'day') {
            $cursor = $start->copy();
            while ($cursor->lte($end)) {
                $keys[$cursor->format('Y-m-d')] = $cursor->translatedFormat('d/m');
                $cursor->addDay();
            }
        } else {
            $cursor = $start->copy()->startOfMonth();
            $last = $end->copy()->startOfMonth();
            while ($cursor->lte($last)) {
                $keys[$cursor->format('Y-m')] = $cursor->translatedFormat('M y');
                $cursor->addMonth();
            }
        }

        return $keys;
    }

    private function periodType(Carbon $start, Carbon $end): string
    {
        return $start->startOfDay()->diffInDays($end->startOfDay()) <= 45 ? 'day' : 'month';
    }

    private function sqlDateFormat(array $filters): string
    {
        [$start, $end] = $this->dates($filters);
        return $this->periodType($start, $end) === 'day' ? "%Y-%m-%d" : "%Y-%m";
    }

    private function fillSeries(mixed $map, array $keys): array
    {
        $map = $map instanceof \Illuminate\Support\Collection ? $map->toArray() : (array) $map;

        $out = [];
        foreach (array_keys($keys) as $key) {
            $out[] = isset($map[$key]) ? (float) $map[$key] : 0;
        }

        return $out;
    }

    /**
     * Período anterior de igual longitud, inmediatamente antes del rango actual.
     */
    private function previousRange(Carbon $start, Carbon $end): array
    {
        $days = (int) $start->startOfDay()->diffInDays($end->startOfDay()) + 1;
        $prevEnd = $start->copy()->subDay()->endOfDay();
        $prevStart = $prevEnd->copy()->startOfDay()->subDays($days - 1)->startOfDay();

        return [$prevStart, $prevEnd];
    }

    private function pct(float $current, float $previous): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    // ══════════════════════ 2.2 HERO KPIs ══════════════════════

    public function heroKpis(array $filters): array
    {
        [$start, $end] = $this->dates($filters);
        [$prevStart, $prevEnd] = $this->previousRange($start, $end);

        // Período actual + anterior en UNA query por métrica (CASE SUM condicional).
        [$contracts, $revenue, $prevContracts, $prevRevenue] = $this->salesBoth($start, $end, $prevStart, $prevEnd, $filters);
        [$installations, $prevInstallations, $assigned] = $this->installationsBoth($start, $end, $prevStart, $prevEnd, $filters);
        [$resolved, $avgHours, $prevResolved, $prevAvgHours] = $this->resolvedBoth($start, $end, $prevStart, $prevEnd, $filters);
        [$slaTotal, $slaMet] = $this->slaBoth($start, $end, $prevStart, $prevEnd, $filters);

        return [
            'revenue' => [
                'label' => 'Ingresos por ventas',
                'value' => $revenue,
                'previous' => $prevRevenue,
                'pct' => $this->pct($revenue, $prevRevenue),
                'suffix' => '$',
            ],
            'contracts' => [
                'label' => 'Contratos nuevos',
                'value' => (float) $contracts,
                'previous' => (float) $prevContracts,
                'pct' => $this->pct((float) $contracts, (float) $prevContracts),
                'suffix' => '',
            ],
            'avgTicket' => [
                'label' => 'Ticket promedio',
                'value' => $contracts > 0 ? $revenue / $contracts : 0,
                'previous' => $prevContracts > 0 ? $prevRevenue / $prevContracts : 0,
                'pct' => 0,
                'suffix' => '$',
            ],
            'installations' => [
                'label' => 'Instalaciones completadas',
                'value' => (float) $installations,
                'previous' => (float) $prevInstallations,
                'pct' => $this->pct((float) $installations, (float) $prevInstallations),
                'suffix' => '',
            ],
            'successRate' => [
                'label' => 'Tasa de éxito de instalación',
                'value' => $assigned > 0 ? round(($installations / $assigned) * 100, 1) : 0,
                'previous' => 0,
                'pct' => 0,
                'suffix' => '%',
            ],
            'resolved' => [
                'label' => 'Fallos resueltos',
                'value' => (float) $resolved,
                'previous' => (float) $prevResolved,
                'pct' => $this->pct((float) $resolved, (float) $prevResolved),
                'suffix' => '',
            ],
            'avgResolutionHours' => [
                'label' => 'Tiempo promedio de resolución',
                'value' => round((float) $avgHours, 1),
                'previous' => round((float) $prevAvgHours, 1),
                'pct' => 0,
                'suffix' => 'h',
            ],
            'slaCompliance' => [
                'label' => 'Cumplimiento SLA',
                'value' => $slaTotal > 0 ? round(($slaMet / $slaTotal) * 100, 1) : 0,
                'previous' => 0,
                'pct' => 0,
                'suffix' => '%',
            ],
        ];
    }

    private function salesBoth(Carbon $cs, Carbon $ce, Carbon $ps, Carbon $pe, array $filters): array
    {
        $row = $this->baseContract($filters)
            ->whereBetween('contract_date', [$ps, $ce])
            ->selectRaw("
                COALESCE(SUM(CASE WHEN contract_date BETWEEN ? AND ? THEN 1 ELSE 0 END), 0) as cur_count,
                COALESCE(SUM(CASE WHEN contract_date BETWEEN ? AND ? THEN price ELSE 0 END), 0) as cur_rev,
                COALESCE(SUM(CASE WHEN contract_date BETWEEN ? AND ? THEN 1 ELSE 0 END), 0) as prev_count,
                COALESCE(SUM(CASE WHEN contract_date BETWEEN ? AND ? THEN price ELSE 0 END), 0) as prev_rev
            ", [$cs, $ce, $cs, $ce, $ps, $pe, $ps, $pe])
            ->first();

        return [(int) $row->cur_count, (float) $row->cur_rev, (int) $row->prev_count, (float) $row->prev_rev];
    }

    private function installationsBoth(Carbon $cs, Carbon $ce, Carbon $ps, Carbon $pe, array $filters): array
    {
        // OTs de instalación CREADAS en el período (asignadas) y de esas, cuántas están completadas.
        // Ambas métricas usan created_at para que la tasa de éxito sea consistente
        // (completadas / asignadas dentro del mismo conjunto de OTs creadas en el período).
        $row = $this->baseWorkOrder($filters)
            ->where('service_type', 'instalacion')
            ->whereBetween('created_at', [$ps, $ce])
            ->selectRaw("
                COALESCE(SUM(CASE WHEN created_at BETWEEN ? AND ? THEN 1 ELSE 0 END), 0) as cur_assigned,
                COALESCE(SUM(CASE WHEN created_at BETWEEN ? AND ? AND status = 'completed' THEN 1 ELSE 0 END), 0) as cur_completed,
                COALESCE(SUM(CASE WHEN created_at BETWEEN ? AND ? THEN 1 ELSE 0 END), 0) as prev_assigned,
                COALESCE(SUM(CASE WHEN created_at BETWEEN ? AND ? AND status = 'completed' THEN 1 ELSE 0 END), 0) as prev_completed
            ", [$cs, $ce, $cs, $ce, $ps, $pe, $ps, $pe])
            ->first();

        return [
            (int) $row->cur_completed,
            (int) $row->prev_completed,
            (int) $row->cur_assigned,
        ];
    }


    private function resolvedBoth(Carbon $cs, Carbon $ce, Carbon $ps, Carbon $pe, array $filters): array
    {
        $row = $this->baseTicket($filters)
            ->where('status', 'resolved')
            ->whereBetween('resolved_at', [$ps, $ce])
            ->selectRaw("
                COALESCE(SUM(CASE WHEN resolved_at BETWEEN ? AND ? THEN 1 ELSE 0 END), 0) as cur_total,
                COALESCE(AVG(CASE WHEN resolved_at BETWEEN ? AND ? THEN TIMESTAMPDIFF(HOUR, created_at, resolved_at) END), 0) as cur_avg,
                COALESCE(SUM(CASE WHEN resolved_at BETWEEN ? AND ? THEN 1 ELSE 0 END), 0) as prev_total,
                COALESCE(AVG(CASE WHEN resolved_at BETWEEN ? AND ? THEN TIMESTAMPDIFF(HOUR, created_at, resolved_at) END), 0) as prev_avg
            ", [$cs, $ce, $cs, $ce, $ps, $pe, $ps, $pe])
            ->first();

        return [(int) $row->cur_total, (float) $row->cur_avg, (int) $row->prev_total, (float) $row->prev_avg];
    }

    private function slaBoth(Carbon $cs, Carbon $ce, Carbon $ps, Carbon $pe, array $filters): array
    {
        $row = $this->baseTicket($filters)
            ->whereNotNull('sla_evaluated_at')
            ->whereBetween('resolved_at', [$ps, $ce])
            ->selectRaw("
                COALESCE(SUM(CASE WHEN resolved_at BETWEEN ? AND ? THEN 1 ELSE 0 END), 0) as cur_total,
                COALESCE(SUM(CASE WHEN resolved_at BETWEEN ? AND ? AND sla_met = 1 THEN 1 ELSE 0 END), 0) as cur_met,
                COALESCE(SUM(CASE WHEN resolved_at BETWEEN ? AND ? THEN 1 ELSE 0 END), 0) as prev_total,
                COALESCE(SUM(CASE WHEN resolved_at BETWEEN ? AND ? AND sla_met = 1 THEN 1 ELSE 0 END), 0) as prev_met
            ", [$cs, $ce, $cs, $ce, $ps, $pe, $ps, $pe])
            ->first();

        return [(int) $row->cur_total, (int) $row->cur_met];
    }

    private function salesTotals(Carbon $a, Carbon $b, array $filters): array
    {
        $row = $this->baseContract($filters)
            ->whereBetween('contract_date', [$a, $b])
            ->selectRaw('COUNT(*) as total, COALESCE(SUM(price), 0) as revenue')
            ->first();

        return [(int) $row->total, (float) $row->revenue];
    }

    private function installationTotals(Carbon $a, Carbon $b, array $filters): int
    {
        return (int) $this->baseWorkOrder($filters)
            ->where('service_type', 'instalacion')
            ->where('status', 'completed')
            ->whereBetween('completed_date', [$a, $b])
            ->count();
    }

    // ══════════════════════ 2.3 VENTAS ══════════════════════

    public function salesMonthly(array $filters): array
    {
        [$start, $end] = $this->dates($filters);
        $fmt = $this->sqlDateFormat($filters);
        $keys = $this->seriesKeys($filters);

        $rows = $this->baseContract($filters)
            ->whereBetween('contract_date', [$start, $end])
            ->selectRaw("DATE_FORMAT(contract_date, '{$fmt}') as period, COUNT(*) as total, COALESCE(SUM(price), 0) as revenue")
            ->groupBy('period')
            ->get();

        $count = $rows->pluck('total', 'period');
        $revenue = $rows->pluck('revenue', 'period');

        return [
            'labels' => array_values($keys),
            'count' => $this->fillSeries($count, $keys),
            'revenue' => $this->fillSeries($revenue, $keys),
        ];
    }

    public function salesByAgent(array $filters, int $limit = 5): array
    {
        [$start, $end] = $this->dates($filters);

        $rows = $this->baseContract($filters)
            ->leftJoin('users', 'users.id', '=', 'contracts.created_by')
            ->whereBetween('contracts.contract_date', [$start, $end])
            ->selectRaw("COALESCE(users.name, 'Sin asignar') as name, COUNT(*) as total, COALESCE(SUM(contracts.price), 0) as revenue")
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get();


        return [
            'agents' => $rows->map(fn($r) => [
                'name' => $r->name,
                'revenue' => (float) $r->revenue,
                'count' => (int) $r->total,
            ])->values()->toArray(),
        ];
    }

    public function salesByPlan(array $filters): array
    {
        [$start, $end] = $this->dates($filters);

        $rows = $this->baseContract($filters)
            ->whereBetween('contract_date', [$start, $end])
            ->selectRaw('plan_id, COUNT(*) as total, COALESCE(SUM(price), 0) as revenue')
            ->groupBy('plan_id')
            ->orderByDesc('revenue')
            ->limit(8)
            ->get();

        $plans = Plan::pluck('name', 'id');

        return [
            'labels' => $rows->map(fn($r) => $r->plan_id ? ($plans[$r->plan_id] ?? 'Plan #' . $r->plan_id) : 'Sin plan')->values()->toArray(),
            'count' => $rows->pluck('total')->map(fn($v) => (int) $v)->values()->toArray(),
            'revenue' => $rows->pluck('revenue')->map(fn($v) => (float) $v)->values()->toArray(),
        ];
    }

    public function salesByZone(array $filters): array
    {
        [$start, $end] = $this->dates($filters);

        $rows = Contract::query()
            ->join('clients', 'clients.id', '=', 'contracts.client_id')
            ->whereBetween('contracts.contract_date', [$start, $end])
            ->when(!empty($filters['departamento']), fn($q) => $q->where('clients.departamento', $filters['departamento']))
            ->when(!empty($filters['branchId']), fn($q) => $q->where('clients.branch_id', $filters['branchId']))
            ->when(!empty($filters['planId']), fn($q) => $q->where('contracts.plan_id', $filters['planId']))
            ->selectRaw("COALESCE(NULLIF(clients.departamento, ''), 'Sin departamento') as label, COUNT(*) as total, COALESCE(SUM(contracts.price), 0) as revenue")
            ->groupBy('label')
            ->orderByDesc('revenue')
            ->limit(8)
            ->get();

        return [
            'labels' => $rows->pluck('label')->values()->toArray(),
            'count' => $rows->pluck('total')->map(fn($v) => (int) $v)->values()->toArray(),
            'revenue' => $rows->pluck('revenue')->map(fn($v) => (float) $v)->values()->toArray(),
        ];
    }

    public function salesByServiceType(array $filters): array
    {
        [$start, $end] = $this->dates($filters);

        $rows = $this->baseContract($filters)
            ->whereBetween('contract_date', [$start, $end])
            ->selectRaw("COALESCE(NULLIF(service_type, ''), 'Sin tipo') as label, COUNT(*) as total")
            ->groupBy('label')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        return [
            'labels' => $rows->pluck('label')->map(fn($l) => ucwords(str_replace('_', ' ', $l)))->values()->toArray(),
            'series' => $rows->pluck('total')->map(fn($v) => (int) $v)->values()->toArray(),
        ];
    }

    public function salesByStatus(array $filters): array
    {
        [$start, $end] = $this->dates($filters);

        $rows = $this->baseContract($filters)
            ->whereBetween('contract_date', [$start, $end])
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $order = ['active', 'suspended', 'cancelled'];
        $labels = ['Activos', 'Suspendidos', 'Cancelados'];

        return [
            'labels' => $labels,
            'series' => array_map(fn($s) => (int) ($rows[$s] ?? 0), $order),
        ];
    }


    // ══════════════════════ 2.4 INSTALACIONES ══════════════════════

    public function installationsMonthly(array $filters): array
    {
        [$start, $end] = $this->dates($filters);
        $fmt = $this->sqlDateFormat($filters);
        $keys = $this->seriesKeys($filters);

        $completed = $this->baseWorkOrder($filters)
            ->where('service_type', 'instalacion')
            ->where('status', 'completed')
            ->whereBetween('completed_date', [$start, $end])
            ->selectRaw("DATE_FORMAT(completed_date, '{$fmt}') as period, COUNT(*) as total")
            ->groupBy('period')
            ->pluck('total', 'period');

        $assigned = $this->baseWorkOrder($filters)
            ->where('service_type', 'instalacion')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw("DATE_FORMAT(created_at, '{$fmt}') as period, COUNT(*) as total")
            ->groupBy('period')
            ->pluck('total', 'period');

        return [
            'labels' => array_values($keys),
            'completed' => $this->fillSeries($completed, $keys),
            'assigned' => $this->fillSeries($assigned, $keys),
        ];
    }

    public function installationSuccessRate(array $filters): float
    {
        [$start, $end] = $this->dates($filters);

        // Consistente con installationsBoth(): ambas métricas sobre OTs de instalación
        // CREADAS en el período. La tasa = completadas / asignadas del mismo conjunto.
        $row = $this->baseWorkOrder($filters)
            ->where('service_type', 'instalacion')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw("
                COUNT(*) as assigned,
                COALESCE(SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END), 0) as completed
            ")
            ->first();

        $assigned = (int) $row->assigned;
        $completed = (int) $row->completed;

        return $assigned > 0 ? round(($completed / $assigned) * 100, 1) : 0;
    }


    public function installationsByTechnician(array $filters, int $limit = 5): array
    {
        [$start, $end] = $this->dates($filters);

        $rows = WorkOrder::query()
            ->join('users', 'users.id', '=', 'work_orders.technician_id')
            ->where('work_orders.service_type', 'instalacion')
            ->where('work_orders.status', 'completed')
            ->whereBetween('work_orders.completed_date', [$start, $end])
            ->when(!empty($filters['departamento']), fn($q) => $q->whereHas('client', fn($c) => $c->where('departamento', $filters['departamento'])))
            ->when(!empty($filters['branchId']), fn($q) => $q->whereHas('client', fn($c) => $c->where('branch_id', $filters['branchId'])))
            ->when(!empty($filters['planId']), fn($q) => $q->where('work_orders.plan_id', $filters['planId']))
            ->selectRaw('users.name as name, COUNT(*) as total')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total')
            ->limit($limit)
            ->get();

        return [
            'technicians' => $rows->map(fn($r) => [
                'name' => $r->name,
                'completed' => (int) $r->total,
            ])->values()->toArray(),
        ];
    }

    public function averageInstallationTime(array $filters): array
    {
        [$start, $end] = $this->dates($filters);
        $fmt = $this->sqlDateFormat($filters);
        $keys = $this->seriesKeys($filters);

        $hours = $this->baseWorkOrder($filters)
            ->where('service_type', 'instalacion')
            ->where('status', 'completed')
            ->whereNotNull('assigned_at')
            ->whereNotNull('completed_date')
            ->whereBetween('completed_date', [$start, $end])
            ->selectRaw("DATE_FORMAT(completed_date, '{$fmt}') as period, COALESCE(AVG(TIMESTAMPDIFF(HOUR, assigned_at, completed_date)), 0) as hours")
            ->groupBy('period')
            ->pluck('hours', 'period');

        return [
            'labels' => array_values($keys),
            'hours' => array_map(fn($v) => round($v, 1), $this->fillSeries($hours, $keys)),
        ];
    }

    public function installationsByZone(array $filters): array
    {
        [$start, $end] = $this->dates($filters);

        $rows = WorkOrder::query()
            ->join('clients', 'clients.id', '=', 'work_orders.client_id')
            ->where('work_orders.service_type', 'instalacion')
            ->where('work_orders.status', 'completed')
            ->whereBetween('work_orders.completed_date', [$start, $end])
            ->when(!empty($filters['departamento']), fn($q) => $q->where('clients.departamento', $filters['departamento']))
            ->when(!empty($filters['branchId']), fn($q) => $q->where('clients.branch_id', $filters['branchId']))
            ->when(!empty($filters['planId']), fn($q) => $q->where('work_orders.plan_id', $filters['planId']))
            ->selectRaw("COALESCE(NULLIF(clients.departamento, ''), 'Sin departamento') as label, COUNT(*) as total")
            ->groupBy('label')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        return [
            'labels' => $rows->pluck('label')->values()->toArray(),
            'series' => $rows->pluck('total')->map(fn($v) => (int) $v)->values()->toArray(),
        ];
    }

    public function installationsPending(array $filters): int
    {
        return (int) $this->baseWorkOrder($filters)
            ->where('service_type', 'instalacion')
            ->whereIn('status', ['pending', 'in_progress'])
            ->count();
    }

    // ══════════════════════ 2.5 FALLOS / SOPORTE ══════════════════════

    public function failuresMonthly(array $filters): array
    {
        [$start, $end] = $this->dates($filters);
        $fmt = $this->sqlDateFormat($filters);
        $keys = $this->seriesKeys($filters);

        $rows = $this->baseTicket($filters)
            ->where('status', 'resolved')
            ->whereBetween('resolved_at', [$start, $end])
            ->selectRaw("DATE_FORMAT(resolved_at, '{$fmt}') as period,
                COUNT(*) as total,
                COALESCE(SUM(CASE WHEN requires_noc = 1 THEN 1 ELSE 0 END), 0) as noc")
            ->groupBy('period')
            ->get();

        $total = $rows->pluck('total', 'period');
        $noc = $rows->pluck('noc', 'period');
        $l1 = $rows->mapWithKeys(fn($r) => [$r->period => (int) $r->total - (int) $r->noc]);

        return [
            'labels' => array_values($keys),
            'total' => $this->fillSeries($total, $keys),
            'noc' => $this->fillSeries($noc, $keys),
            'l1' => $this->fillSeries($l1, $keys),
        ];
    }

    public function failuresByPriority(array $filters): array
    {
        [$start, $end] = $this->dates($filters);

        $rows = $this->baseTicket($filters)
            ->where('status', 'resolved')
            ->whereBetween('resolved_at', [$start, $end])
            ->selectRaw('priority, COUNT(*) as total')
            ->groupBy('priority')
            ->pluck('total', 'priority')
            ->toArray();

        $order = ['P1', 'P2', 'P3', 'P4'];

        return [
            'labels' => $order,
            'series' => array_map(fn($p) => (int) ($rows[$p] ?? 0), $order),
        ];
    }

    public function failuresByResolver(array $filters, int $limit = 5): array
    {
        [$start, $end] = $this->dates($filters);

        $rows = $this->baseTicket($filters)
            ->leftJoin('users', 'users.id', '=', 'tickets.resolved_by')
            ->where('tickets.status', 'resolved')
            ->whereNotNull('tickets.resolved_by')
            ->whereBetween('tickets.resolved_at', [$start, $end])
            ->selectRaw("COALESCE(users.name, 'Sin asignar') as name, COUNT(*) as total")
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total')
            ->limit($limit)
            ->get();


        return [
            'resolvers' => $rows->map(fn($r) => [
                'name' => $r->name,
                'count' => (int) $r->total,
            ])->values()->toArray(),
        ];
    }

    public function averageResolutionTime(array $filters): array
    {
        [$start, $end] = $this->dates($filters);
        $fmt = $this->sqlDateFormat($filters);
        $keys = $this->seriesKeys($filters);

        $byMonth = $this->baseTicket($filters)
            ->where('status', 'resolved')
            ->whereBetween('resolved_at', [$start, $end])
            ->selectRaw("DATE_FORMAT(resolved_at, '{$fmt}') as period, COALESCE(AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)), 0) as hours")
            ->groupBy('period')
            ->pluck('hours', 'period');

        $byPriority = $this->baseTicket($filters)
            ->where('status', 'resolved')
            ->whereBetween('resolved_at', [$start, $end])
            ->selectRaw("priority, COALESCE(AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)), 0) as hours")
            ->groupBy('priority')
            ->pluck('hours', 'priority');

        return [
            'labels' => array_values($keys),
            'hours' => array_map(fn($v) => round($v, 1), $this->fillSeries($byMonth, $keys)),
            'byPriority' => [
                'labels' => ['P1', 'P2', 'P3', 'P4'],
                'hours' => array_map(fn($p) => round((float) ($byPriority[$p] ?? 0), 1), ['P1', 'P2', 'P3', 'P4']),
            ],
        ];
    }

    public function slaComplianceReport(array $filters): array
    {
        [$start, $end] = $this->dates($filters);

        $byPriority = $this->baseTicket($filters)
            ->whereNotNull('sla_evaluated_at')
            ->whereBetween('resolved_at', [$start, $end])
            ->selectRaw('priority, COUNT(*) as total, COALESCE(SUM(CASE WHEN sla_met = 1 THEN 1 ELSE 0 END), 0) as met')
            ->groupBy('priority')
            ->get();

        $total = (int) $byPriority->sum('total');
        $met = (int) $byPriority->sum('met');

        $pending = (int) $this->baseTicket($filters)
            ->whereNotNull('sla_goal_id')
            ->whereNull('sla_evaluated_at')
            ->whereBetween('created_at', [$start, $end])
            ->count();

        return [
            'overall' => $total > 0 ? round(($met / $total) * 100, 1) : 0,
            'byPriority' => [
                'labels' => ['P1', 'P2', 'P3', 'P4'],
                'percentages' => array_map(function ($p) use ($byPriority) {
                    $row = $byPriority->firstWhere('priority', $p);
                    $t = (int) ($row->total ?? 0);
                    return $t > 0 ? round((($row->met ?? 0) / $t) * 100, 1) : 0;
                }, ['P1', 'P2', 'P3', 'P4']),
            ],
            'donut' => [
                'labels' => ['Cumplidas', 'No cumplidas', 'Pendientes'],
                'series' => [
                    $met,
                    max(0, $total - $met),
                    $pending,
                ],
            ],
        ];
    }

    public function failuresByServiceType(array $filters): array
    {
        [$start, $end] = $this->dates($filters);

        $rows = $this->baseTicket($filters)
            ->where('status', 'resolved')
            ->whereBetween('resolved_at', [$start, $end])
            ->selectRaw("COALESCE(NULLIF(service_type, ''), 'Sin tipo') as label, COUNT(*) as total")
            ->groupBy('label')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        return [
            'labels' => $rows->pluck('label')->map(fn($l) => ucwords(str_replace('_', ' ', $l)))->values()->toArray(),
            'series' => $rows->pluck('total')->map(fn($v) => (int) $v)->values()->toArray(),
        ];
    }

    public function escalations(array $filters): array
    {
        [$start, $end] = $this->dates($filters);

        $rows = $this->baseTicket($filters)
            ->where('status', 'resolved')
            ->whereBetween('resolved_at', [$start, $end])
            ->selectRaw('COUNT(*) as total, COALESCE(SUM(CASE WHEN requires_noc = 1 THEN 1 ELSE 0 END), 0) as noc')
            ->first();

        $total = (int) $rows->total;
        $noc = (int) $rows->noc;

        return [
            'labels' => ['L1 (SAC)', 'NOC'],
            'series' => [max(0, $total - $noc), $noc],
        ];
    }

    // ══════════════════════ 2.6 FUNNEL COMERCIAL ══════════════════════

    public function commercialFunnel(array $filters): array
    {
        [$start, $end] = $this->dates($filters);

        $clients = (int) Client::query()
            ->when(!empty($filters['departamento']), fn($q) => $q->where('departamento', $filters['departamento']))
            ->when(!empty($filters['branchId']), fn($q) => $q->where('branch_id', $filters['branchId']))
            ->when(!empty($filters['planId']), fn($q) => $q->where('plan_id', $filters['planId']))
            ->whereBetween('created_at', [$start, $end])
            ->count();

        [$contracts, $revenue] = $this->salesTotals($start, $end, $filters);
        $installations = $this->installationTotals($start, $end, $filters);

        return [
            'clients' => $clients,
            'contracts' => $contracts,
            'installations' => $installations,
            'revenue' => $revenue,
        ];
    }

    public function conversionRates(array $filters): array
    {
        [$start, $end] = $this->dates($filters);

        // Ticket → Contrato: contratos vinculados a ticket / tickets creados en el período.
        $tickets = (int) $this->baseTicket($filters)
            ->whereBetween('created_at', [$start, $end])
            ->count();

        $contractsFromTickets = (int) Contract::query()
            ->whereNotNull('ticket_id')
            ->whereBetween('contract_date', [$start, $end])
            ->when(!empty($filters['planId']), fn($q) => $q->where('plan_id', $filters['planId']))
            ->when(!empty($filters['departamento']) || !empty($filters['branchId']), fn($q) => $q->whereHas('client', function ($c) use ($filters) {
                if (!empty($filters['departamento']))
                    $c->where('departamento', $filters['departamento']);
                if (!empty($filters['branchId']))
                    $c->where('branch_id', $filters['branchId']);
            }))
            ->count();

        $contracts = $this->salesTotals($start, $end, $filters)[0];
        $installations = $this->installationTotals($start, $end, $filters);

        return [
            'ticketsToContracts' => $tickets > 0 ? round(($contractsFromTickets / $tickets) * 100, 1) : 0,
            'contractsToInstallations' => $contracts > 0 ? round(($installations / $contracts) * 100, 1) : 0,
        ];
    }

    // ══════════════════════ 2.7 EFICIENCIA TÉCNICA ══════════════════════

    public function technicalEfficiency(array $filters, int $limit = 8): array
    {
        [$start, $end] = $this->dates($filters);

        $reqs = Requisition::query()
            ->where('status', 'approved')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('technician_id, COUNT(*) as total')
            ->groupBy('technician_id')
            ->pluck('total', 'technician_id');

        $returns = TechnicianReturn::query()
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('user_id, type, COUNT(*) as total')
            ->groupBy('user_id', 'type')
            ->get();

        $surplus = $returns->where('type', 'surplus')->pluck('total', 'user_id');
        $damage = $returns->where('type', 'damage')->pluck('total', 'user_id');

        $ids = $reqs->keys()->merge($surplus->keys())->merge($damage->keys())->unique();
        $names = User::whereIn('id', $ids)->pluck('name', 'id');

        $rows = $ids->map(fn($id) => [
            'name' => $names[$id] ?? 'Técnico #' . $id,
            'approved' => (int) ($reqs[$id] ?? 0),
            'surplus' => (int) ($surplus[$id] ?? 0),
            'damage' => (int) ($damage[$id] ?? 0),
        ])->sortByDesc('approved')->values()->take($limit);

        return ['technicians' => $rows->toArray()];
    }

    public function inventorySnapshot(array $filters): array
    {
        [$start, $end] = $this->dates($filters);
        $fmt = $this->sqlDateFormat($filters);
        $keys = $this->seriesKeys($filters);

        $movements = Movement::query()
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw("DATE_FORMAT(created_at, '{$fmt}') as period, COUNT(*) as total")
            ->groupBy('period')
            ->pluck('total', 'period');

        return [
            'value' => (float) Product::sum('total_value'),
            'lowStock' => (int) Product::whereColumn('current_stock', '<=', 'stock_min')->count(),
            'movements' => [
                'labels' => array_values($keys),
                'series' => $this->fillSeries($movements, $keys),
            ],
        ];
    }

    public function shipmentsByStatus(): array
    {
        $rows = DistributionShipment::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $order = ['pending', 'in_transit', 'delivered', 'confirmed'];
        $labels = ['Pendiente', 'En tránsito', 'Entregado', 'Confirmado'];

        return [
            'labels' => $labels,
            'series' => array_map(fn($s) => (int) ($rows[$s] ?? 0), $order),
        ];
    }

    // ══════════════════════ CATÁLOGOS PARA FILTROS ══════════════════════

    public function departamentos(): array
    {
        return Client::query()
            ->whereNotNull('departamento')
            ->where('departamento', '!=', '')
            ->select('departamento')
            ->distinct()
            ->orderBy('departamento')
            ->pluck('departamento')
            ->toArray();
    }
}
