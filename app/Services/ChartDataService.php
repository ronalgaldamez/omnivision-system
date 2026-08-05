<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Device;
use App\Models\Movement;
use App\Models\Purchase;
use App\Models\Requisition;
use App\Models\TechnicianReturn;
use App\Models\Ticket;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\DB;



class ChartDataService
{
    private function monthLabels(int $months): array
    {
        $labels = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $labels[] = now()->subMonths($i)->translatedFormat('M');
        }
        return $labels;
    }

    public function monthlyMovements(int $months = 12): array
    {
        $since = now()->subMonths($months - 1)->startOfMonth();

        $rows = Movement::where('created_at', '>=', $since)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month,
                SUM(CASE WHEN type IN ('entry','technician_return') THEN 1 ELSE 0 END) as entries,
                SUM(CASE WHEN type IN ('exit','technician_out','damage','return_to_supplier','requisition_out','branch_allocation') THEN 1 ELSE 0 END) as exits")
            ->groupBy('month')
            ->get();

        $entries = [];
        $exits = [];
        for ($i = 0; $i < $months; $i++) {
            $key = now()->subMonths($months - 1 - $i)->format('Y-m');
            $row = $rows->firstWhere('month', $key);
            $entries[] = $row ? (int) $row->entries : 0;
            $exits[] = $row ? (int) $row->exits : 0;
        }

        return [
            'labels' => $this->monthLabels($months),
            'entries' => $entries,
            'exits' => $exits,
        ];
    }

    public function monthlyWorkOrders(int $months = 6): array
    {
        $since = now()->subMonths($months - 1)->startOfMonth();

        $rows = WorkOrder::where('created_at', '>=', $since)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month,
                COUNT(*) as created,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed")
            ->groupBy('month')
            ->get();

        $created = [];
        $completed = [];
        for ($i = 0; $i < $months; $i++) {
            $key = now()->subMonths($months - 1 - $i)->format('Y-m');
            $row = $rows->firstWhere('month', $key);
            $created[] = $row ? (int) $row->created : 0;
            $completed[] = $row ? (int) $row->completed : 0;
        }

        return [
            'labels' => $this->monthLabels($months),
            'created' => $created,
            'completed' => $completed,
        ];
    }

    public function ticketsByStatus(?int $userId = null): array
    {
        $base = Ticket::query();
        if ($userId) {
            $base->where('created_by', $userId);
        }

        return [
            'labels' => ['Pendientes', 'En proceso', 'Resueltos', 'Cerrados'],
            'series' => [
                (clone $base)->where('status', 'pending')->count(),
                (clone $base)->where('status', 'in_progress')->count(),
                (clone $base)->where('status', 'resolved')->count(),
                (clone $base)->where('status', 'closed')->count(),
            ],
        ];
    }

    public function ticketsByPriority(?int $userId = null): array
    {
        $base = Ticket::query();
        if ($userId) {
            $base->where('created_by', $userId);
        }

        return [
            'labels' => ['P1', 'P2', 'P3', 'P4'],
            'series' => [
                (clone $base)->where('priority', 'P1')->count(),
                (clone $base)->where('priority', 'P2')->count(),
                (clone $base)->where('priority', 'P3')->count(),
                (clone $base)->where('priority', 'P4')->count(),
            ],
        ];
    }

    public function devicesByStatus(): array
    {
        $rows = Device::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $order = ['in_stock', 'assigned', 'installed', 'damaged'];
        $labels = ['En stock', 'Asignados', 'Instalados', 'Dañados'];

        return [
            'labels' => $labels,
            'series' => array_map(fn($s) => (int) ($rows[$s] ?? 0), $order),
        ];
    }

    public function techniciansCompleted(int $limit = 8): array
    {
        $rows = User::role('technician')
            ->withCount(['workOrders as completed' => fn($q) => $q->where('status', 'completed')])
            ->orderByDesc('completed')
            ->limit($limit)
            ->get();

        return [
            'labels' => $rows->pluck('name')
                ->map(function ($full) {
                    $parts = explode(' ', $full);
                    return count($parts) > 1 ? $parts[0] . ' ' . end($parts) : $full;
                })
                ->values()
                ->toArray(),
            'series' => $rows->pluck('completed')->map(fn($v) => (int) $v)->values()->toArray(),
        ];

    }

    public function monthlyComparison(string $model, array $conditions = [], string $dateColumn = 'created_at'): array
    {
        $thisMonth = $model::whereMonth($dateColumn, now()->month)->whereYear($dateColumn, now()->year);
        $lastMonth = $model::whereMonth($dateColumn, now()->subMonth()->month)->whereYear($dateColumn, now()->subMonth()->year);

        foreach ($conditions as $col => $val) {
            $thisMonth->where($col, $val);
            $lastMonth->where($col, $val);
        }

        $current = $thisMonth->count();
        $previous = $lastMonth->count();
        $pct = $previous > 0 ? round((($current - $previous) / $previous) * 100, 1) : ($current > 0 ? 100 : 0);

        return ['current' => $current, 'previous' => $previous, 'pct' => $pct];
    }

    public function slaCompliance(): array
    {
        return [
            'labels' => ['Cumplidas', 'No cumplidas', 'Pendientes'],
            'series' => [
                Ticket::whereNotNull('sla_evaluated_at')->where('sla_met', true)->count(),
                Ticket::whereNotNull('sla_evaluated_at')->where('sla_met', false)->count(),
                Ticket::whereNull('sla_evaluated_at')->whereNotNull('sla_goal_id')->count(),
            ],
        ];
    }

    public function monthlySlaTickets(int $months = 6): array
    {
        $since = now()->subMonths($months - 1)->startOfMonth();

        $rows = Ticket::whereNotNull('sla_goal_id')
            ->where('created_at', '>=', $since)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as total")
            ->groupBy('month')
            ->pluck('total', 'month');

        $result = [];
        for ($i = 0; $i < $months; $i++) {
            $key = now()->subMonths($months - 1 - $i)->format('Y-m');
            $result[] = (int) ($rows[$key] ?? 0);
        }

        return ['labels' => $this->monthLabels($months), 'series' => $result];
    }

    /**
     * Clientes nuevos por mes (últimos N meses).
     */
    public function newClientsMonthly(int $months = 6): array
    {
        $since = now()->subMonths($months - 1)->startOfMonth();

        $rows = Client::where('created_at', '>=', $since)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as total")
            ->groupBy('month')
            ->pluck('total', 'month');

        $result = [];
        for ($i = 0; $i < $months; $i++) {
            $key = now()->subMonths($months - 1 - $i)->format('Y-m');
            $result[] = (int) ($rows[$key] ?? 0);
        }

        return ['labels' => $this->monthLabels($months), 'series' => $result];
    }

    /**
     * Compras por mes (últimos N meses) — total en dólares.
     */
    public function purchasesMonthly(int $months = 6): array
    {
        $since = now()->subMonths($months - 1)->startOfMonth();

        $rows = Purchase::where('purchase_date', '>=', $since)
            ->selectRaw("DATE_FORMAT(purchase_date, '%Y-%m') as month, SUM(total) as total")
            ->groupBy('month')
            ->pluck('total', 'month');

        $result = [];
        for ($i = 0; $i < $months; $i++) {
            $key = now()->subMonths($months - 1 - $i)->format('Y-m');
            $result[] = round((float) ($rows[$key] ?? 0), 2);
        }

        return ['labels' => $this->monthLabels($months), 'series' => $result];
    }

    /**
     * OTs por estado (donut).
     */
    public function workOrdersByStatus(): array
    {
        $rows = WorkOrder::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $order = ['pending', 'in_progress', 'completed', 'cancelled'];
        $labels = ['Pendientes', 'En proceso', 'Completadas', 'Canceladas'];

        return [
            'labels' => $labels,
            'series' => array_map(fn($s) => (int) ($rows[$s] ?? 0), $order),
        ];
    }

    /**
     * Rendimiento mensual de un técnico específico.
     * Devuelve por mes: OTs asignadas, OTs completadas, requisiciones aprobadas, devoluciones.
     */
    public function technicianMonthlyPerformance(int $technicianId, int $months = 6): array
    {
        $since = now()->subMonths($months - 1)->startOfMonth();

        // OTs asignadas (creadas) por mes
        $assigned = WorkOrder::where('technician_id', $technicianId)
            ->where('created_at', '>=', $since)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as total")
            ->groupBy('month')
            ->pluck('total', 'month');

        // OTs completadas por mes (fecha real de completado)
        $completed = WorkOrder::where('technician_id', $technicianId)
            ->where('status', 'completed')
            ->whereNotNull('completed_date')
            ->where('completed_date', '>=', $since)
            ->selectRaw("DATE_FORMAT(completed_date, '%Y-%m') as month, COUNT(*) as total")
            ->groupBy('month')
            ->pluck('total', 'month');

        // Requisiciones aprobadas por mes
        $requisitions = Requisition::where('technician_id', $technicianId)
            ->where('status', 'approved')
            ->where('created_at', '>=', $since)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as total")
            ->groupBy('month')
            ->pluck('total', 'month');

        // Devoluciones (sobrantes + dañados) por mes
        $returns = TechnicianReturn::where('user_id', $technicianId)
            ->where('created_at', '>=', $since)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as total")
            ->groupBy('month')
            ->pluck('total', 'month');

        $assignedData = [];
        $completedData = [];
        $approvedReqs = [];
        $returnsData = [];

        for ($i = 0; $i < $months; $i++) {
            $key = now()->subMonths($months - 1 - $i)->format('Y-m');
            $assignedData[] = (int) ($assigned[$key] ?? 0);
            $completedData[] = (int) ($completed[$key] ?? 0);
            $approvedReqs[] = (int) ($requisitions[$key] ?? 0);
            $returnsData[] = (int) ($returns[$key] ?? 0);
        }

        return [
            'labels' => $this->monthLabels($months),
            'assigned' => $assignedData,
            'completed' => $completedData,
            'approved_requisitions' => $approvedReqs,
            'returns' => $returnsData,
        ];
    }

    /**
     * Comparación de rendimiento de un técnico entre dos períodos.
     * Devuelve métricas agregadas para cada período.
     */
    public function technicianComparison(int $technicianId, string $startA, string $endA, string $startB, string $endB): array
    {
        $periodA = $this->aggregateTechnicianMetrics($technicianId, $startA, $endA);
        $periodB = $this->aggregateTechnicianMetrics($technicianId, $startB, $endB);

        return [
            'periodA' => $periodA,
            'periodB' => $periodB,
            'labels' => ['Período A', 'Período B'],
        ];
    }

    /**
     * Agrega métricas de un técnico en un rango de fechas.
     */
    private function aggregateTechnicianMetrics(int $technicianId, string $start, string $end): array
    {
        $startDate = \Carbon\Carbon::parse($start)->startOfDay();
        $endDate = \Carbon\Carbon::parse($end)->endOfDay();

        $assigned = WorkOrder::where('technician_id', $technicianId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $completed = WorkOrder::where('technician_id', $technicianId)
            ->where('status', 'completed')
            ->whereNotNull('completed_date')
            ->whereBetween('completed_date', [$startDate, $endDate])
            ->count();

        $approvedReqs = Requisition::where('technician_id', $technicianId)
            ->where('status', 'approved')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $returns = TechnicianReturn::where('user_id', $technicianId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        return [
            'assigned' => $assigned,
            'completed' => $completed,
            'approved_requisitions' => $approvedReqs,
            'returns' => $returns,
        ];
    }
}


