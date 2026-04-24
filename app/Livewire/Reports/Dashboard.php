<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\Product;
use App\Models\TechnicianRequest;
use App\Models\WorkOrder;
use App\Models\Movement;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Component
{
    public function render()
    {
        $user = Auth::user();

        if ($user->hasRole('technician')) {
            // Vista para técnico: solo datos personales
            $lowStockCount = null; // no mostrar
            $pendingRequestsCount = TechnicianRequest::where('technician_id', $user->id)->where('status', 'pending')->count();
            $activeWorkOrdersCount = WorkOrder::where('technician_id', $user->id)->whereIn('status', ['pending', 'in_progress'])->count();
            $todayMovementsCount = null; // no mostrar
            $recentMovements = null; // no mostrar

            // En lugar de movimientos, podríamos mostrar sus solicitudes recientes
            $recentRequests = TechnicianRequest::with('products.product')
                ->where('technician_id', $user->id)
                ->latest()
                ->limit(5)
                ->get();

            return view('livewire.reports.dashboard-technician', compact(
                'pendingRequestsCount',
                'activeWorkOrdersCount',
                'recentRequests'
            ))->layout('components.layouts.app');
        } else {
            // Vista para admin/warehouse: datos globales
            $lowStockCount = Product::whereColumn('current_stock', '<=', 'stock_min')->count();
            $pendingRequestsCount = TechnicianRequest::where('status', 'pending')->count();
            $activeWorkOrdersCount = WorkOrder::whereIn('status', ['pending', 'in_progress'])->count();
            $todayMovementsCount = Movement::whereDate('created_at', today())->count();
            $recentMovements = Movement::with('product', 'user')->latest()->limit(5)->get();

            return view('livewire.reports.dashboard', compact(
                'lowStockCount',
                'pendingRequestsCount',
                'activeWorkOrdersCount',
                'todayMovementsCount',
                'recentMovements'
            ))->layout('components.layouts.app');
        }
    }
}