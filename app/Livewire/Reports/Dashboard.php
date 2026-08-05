<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\Product;
use App\Models\Requisition;
use App\Models\WorkOrder;
use App\Models\Movement;
use App\Models\Ticket;
use App\Services\SlaService;
use App\Services\DashboardKpiService;
use App\Services\ChartDataService;
use App\Services\WorkOrderService;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Component
{
    public function render()
    {
        $user = Auth::user();

        // ========== DATOS COMUNES PARA TODOS LOS ROLES ==========
        $lowStockCount = null;
        if ($user->can('view low stock')) {
            $lowStockCount = Product::whereColumn('current_stock', '<=', 'stock_min')->count();
        }

        $pendingRequisitionsCount = null;
        if ($user->can('view requisitions')) {
            $pendingRequisitionsCount = Requisition::where('status', 'pending')->count();
        }

        $activeWorkOrdersCount = null;
        if ($user->can('view work_orders')) {
            $activeWorkOrdersCount = WorkOrder::whereIn('status', ['pending', 'in_progress'])->count();
        }

        $todayMovementsCount = null;
        $recentMovements = null;
        if ($user->can('view movements')) {
            $todayMovementsCount = Movement::whereDate('created_at', today())->count();
            $recentMovements = Movement::with('product', 'user')->latest()->limit(5)->get();
        }

        // ========== NUEVOS KPI ==========
        $kpi = app(DashboardKpiService::class);

        $inventoryValue = null;
        if ($user->can('view products')) {
            $inventoryValue = $kpi->getInventoryValue();
        }

        $todayEntries = null;
        $todayExits = null;
        $topProducts = null;
        if ($user->can('view movements')) {
            $todayEntries = $kpi->getTodayEntries();
            $todayExits = $kpi->getTodayExits();
            $topProducts = $kpi->getTopProducts();
        }

        $monthlyPurchasesTotal = null;
        $monthlyPurchasesCount = null;
        $recentPurchases = null;
        if ($user->can('view purchases')) {
            $monthlyPurchasesTotal = $kpi->getMonthlyPurchasesTotal();
            $monthlyPurchasesCount = $kpi->getMonthlyPurchasesCount();
            $recentPurchases = $kpi->getRecentPurchases();
        }

        $devicesByStatus = null;
        if ($user->can('access_inventory')) {
            $devicesByStatus = $kpi->getDevicesByStatus();
        }

        $newClientsToday = null;
        $newClientsThisMonth = null;
        if ($user->can('view clients')) {
            $newClientsToday = $kpi->getNewClientsToday();
            $newClientsThisMonth = $kpi->getNewClientsThisMonth();
        }

        $pendingWorkOrders = null;
        $completedWorkOrders = null;
        if ($user->can('view work_orders')) {
            $pendingWorkOrders = WorkOrder::whereIn('status', ['pending', 'in_progress'])->count();
            $completedWorkOrders = WorkOrder::where('status', 'completed')->count();
        }

        // ========== GRÁFICOS (ApexCharts) ==========
        $charts = app(ChartDataService::class);

        $monthlyMovements = null;
        $monthlyWorkOrders = null;
        $ticketsByStatus = null;
        $ticketsByPriority = null;
        $devicesChart = null;
        $workOrdersChart = null;
        $instalacionesComparison = null;
        $newClientsChart = null;
        $purchasesChart = null;



        if ($user->can('view movements')) {
            $monthlyMovements = $charts->monthlyMovements(12);
        }

        if ($user->can('view work_orders')) {
            $monthlyWorkOrders = $charts->monthlyWorkOrders(6);
            $instalacionesComparison = $charts->monthlyComparison(WorkOrder::class, ['service_type' => 'instalacion']);
            $workOrdersChart = $charts->workOrdersByStatus();
        }



        if ($user->can('view any tickets') || $user->can('view own tickets')) {
            $ticketsScope = $user->can('view any tickets') ? null : $user->id;
            $ticketsByStatus = $charts->ticketsByStatus($ticketsScope);
            $ticketsByPriority = $charts->ticketsByPriority($ticketsScope);
        }

        if ($user->can('access_inventory')) {
            $devicesChart = $charts->devicesByStatus();
        }

        if ($user->can('view clients')) {
            $newClientsChart = $charts->newClientsMonthly(6);
        }

        if ($user->can('view purchases')) {
            $purchasesChart = $charts->purchasesMonthly(6);
        }


        // ========== DATOS ESPECÍFICOS PARA TÉCNICO ==========
        $techPendingRequisitionsCount = null;
        $techActiveWorkOrdersCount = null;
        $techRecentRequisitions = null;
        if ($user->can('view technician dashboard')) {
            $techPendingRequisitionsCount = Requisition::where('technician_id', $user->id)
                ->whereIn('status', ['open', 'approved'])
                ->count();
            $techActiveWorkOrdersCount = WorkOrder::where('technician_id', $user->id)
                ->whereIn('status', ['pending', 'in_progress'])
                ->count();
            $techRecentRequisitions = Requisition::with('items.product')
                ->where('technician_id', $user->id)
                ->latest()
                ->limit(5)
                ->get();
        }

        return view('livewire.reports.dashboard', compact(
            'lowStockCount',
            'pendingRequisitionsCount',
            'activeWorkOrdersCount',
            'todayMovementsCount',
            'recentMovements',
            'techPendingRequisitionsCount',
            'techActiveWorkOrdersCount',
            'techRecentRequisitions',
            'inventoryValue',
            'todayEntries',
            'todayExits',
            'monthlyPurchasesTotal',
            'monthlyPurchasesCount',
            'recentPurchases',
            'topProducts',
            'devicesByStatus',
            'newClientsToday',
            'newClientsThisMonth',
            'pendingWorkOrders',
            'completedWorkOrders',
            'monthlyMovements',
            'monthlyWorkOrders',
            'ticketsByStatus',
            'ticketsByPriority',
            'devicesChart',
            'workOrdersChart',
            'instalacionesComparison',
            'newClientsChart',
            'purchasesChart'
        ))->layout('components.layouts.app');

    }

    // Métodos auxiliares (sin cambios)
    public function resolveRemote($ticketId)
    {
        $ticket = Ticket::find($ticketId);
        if ($ticket && $ticket->requires_noc && auth()->user()->can('edit tickets')) {
            $ticket->status = 'resolved';
            $ticket->resolved_by = auth()->id();
            $ticket->resolved_at = now();
            $ticket->save();
            app(SlaService::class)->evaluateSla($ticket);
            session()->flash('message', 'Ticket resuelto remotamente.');
        }
        return redirect()->route('noc.panel');
    }

    public function createWorkOrder($ticketId)
    {
        $ticket = Ticket::with('client')->find($ticketId);
        if ($ticket && auth()->user()->can('create work_orders')) {
            $workOrder = app(WorkOrderService::class)->createFromTicket($ticket);
            $ticket->status = 'in_progress';
            $ticket->save();
            app(SlaService::class)->evaluateSla($ticket);
            session()->flash('message', 'OT creada a partir del ticket.');
        }
        return redirect()->route('work-orders.index');
    }
}