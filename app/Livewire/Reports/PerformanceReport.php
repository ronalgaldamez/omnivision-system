<?php

namespace App\Livewire\Reports;

use App\Models\Branch;
use App\Models\Plan;
use App\Services\PerformanceReportService;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class PerformanceReport extends Component
{
    // ─── Filtros ───
    public $fechaInicio;
    public $fechaFin;
    public $departamento = '';
    public $branchId = '';
    public $planId = '';

    // ─── Preset activo ───
    public $preset = 'month';

    public function mount()
    {
        // Default: mes actual. Los KPIs comparan vs el período anterior de igual longitud.
        $this->fechaInicio = now()->startOfMonth()->toDateString();
        $this->fechaFin = now()->endOfMonth()->toDateString();
    }

    public function applyPreset(string $preset)
    {
        $this->preset = $preset;

        match ($preset) {
            '7d' => $this->setRange(now()->subDays(6), now()),
            '30d' => $this->setRange(now()->subDays(29), now()),
            '90d' => $this->setRange(now()->subDays(89), now()),
            '12m' => $this->setRange(now()->subMonths(11)->startOfMonth(), now()),
            default => $this->setRange(now()->startOfMonth(), now()->endOfMonth()),
        };
    }

    public function clearFilters()
    {
        $this->departamento = '';
        $this->branchId = '';
        $this->planId = '';
    }

    private function setRange(\Carbon\CarbonInterface $start, \Carbon\CarbonInterface $end)
    {
        $this->fechaInicio = $start->toDateString();
        $this->fechaFin = $end->toDateString();
    }

    private function filters(): array
    {
        return [
            'fechaInicio' => $this->fechaInicio,
            'fechaFin' => $this->fechaFin,
            'departamento' => $this->departamento ?: null,
            'branchId' => $this->branchId ?: null,
            'planId' => $this->planId ?: null,
        ];
    }

    public function render()
    {
        $filters = $this->filters();
        $cacheKey = 'performance_report:' . md5(serialize($filters));

        $data = Cache::remember($cacheKey, 300, function () use ($filters) {
            $service = app(PerformanceReportService::class);

            $installationsMonthly = $service->installationsMonthly($filters);
            $completedTotal = array_sum($installationsMonthly['completed']);
            $assignedTotal = array_sum($installationsMonthly['assigned']);

            return [
                'kpis' => $service->heroKpis($filters),
                'salesMonthly' => $service->salesMonthly($filters),
                'salesByAgent' => $service->salesByAgent($filters),
                'salesByPlan' => $service->salesByPlan($filters),
                'salesByZone' => $service->salesByZone($filters),
                'salesByServiceType' => $service->salesByServiceType($filters),
                'salesByStatus' => $service->salesByStatus($filters),
                'installationsMonthly' => $installationsMonthly,
                'installationSuccessRate' => $assignedTotal > 0 ? round(($completedTotal / $assignedTotal) * 100, 1) : 0,
                'installationsByTechnician' => $service->installationsByTechnician($filters),
                'averageInstallationTime' => $service->averageInstallationTime($filters),
                'installationsByZone' => $service->installationsByZone($filters),
                'installationsPending' => $service->installationsPending($filters),
                'failuresMonthly' => $service->failuresMonthly($filters),
                'failuresByPriority' => $service->failuresByPriority($filters),
                'failuresByResolver' => $service->failuresByResolver($filters),
                'averageResolutionTime' => $service->averageResolutionTime($filters),
                'slaCompliance' => $service->slaComplianceReport($filters),
                'failuresByServiceType' => $service->failuresByServiceType($filters),
                'escalations' => $service->escalations($filters),
                'funnel' => $service->commercialFunnel($filters),
                'conversion' => $service->conversionRates($filters),
                'technicalEfficiency' => $service->technicalEfficiency($filters),
                'inventory' => $service->inventorySnapshot($filters),
                'shipments' => $service->shipmentsByStatus(),
            ];
        });

        $data['departamentos'] = app(PerformanceReportService::class)->departamentos();
        $data['branches'] = Branch::where('is_active', true)->orderBy('name')->get();
        $data['plans'] = Plan::where('is_active', true)->orderBy('name')->get();

        return view('livewire.reports.performance-report', $data)->layout('components.layouts.app');
    }
}
