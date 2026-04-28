<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\Product;
use App\Models\TechnicianRequest;
use App\Models\WorkOrder;
use App\Models\Movement;
use App\Models\Ticket;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Component
{
    public function render()
    {
        $user = Auth::user();

        // ========== TÉCNICO (mantenemos su vista separada) ==========
        if ($user->hasRole('technician')) {
            $pendingRequestsCount = TechnicianRequest::where('technician_id', $user->id)
                ->where('status', 'pending')
                ->count();
            $activeWorkOrdersCount = WorkOrder::where('technician_id', $user->id)
                ->whereIn('status', ['pending', 'in_progress'])
                ->count();
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
        }

        // ========== DATOS GLOBALES (para admin, warehouse, supervisor, y otros) ==========
        // Solo se cargan si el usuario tiene el permiso correspondiente

        // Stock bajo (permiso 'view low stock')
        $lowStockCount = null;
        if ($user->can('view low stock')) {
            $lowStockCount = Product::whereColumn('current_stock', '<=', 'stock_min')->count();
        }

        // Solicitudes pendientes (permiso 'view technician_requests')
        $pendingRequestsCount = null;
        if ($user->can('view technician_requests')) {
            $pendingRequestsCount = TechnicianRequest::where('status', 'pending')->count();
        }

        // Órdenes activas (permiso 'view work_orders')
        $activeWorkOrdersCount = null;
        if ($user->can('view work_orders')) {
            $activeWorkOrdersCount = WorkOrder::whereIn('status', ['pending', 'in_progress'])->count();
        }

        // Movimientos hoy y últimos movimientos (permiso 'view movements')
        $todayMovementsCount = null;
        $recentMovements = null;
        if ($user->can('view movements')) {
            $todayMovementsCount = Movement::whereDate('created_at', today())->count();
            $recentMovements = Movement::with('product', 'user')->latest()->limit(5)->get();
        }

        // Tickets del usuario (secretaria o NOC): permiso 'view own tickets' + roles específicos
        $myTickets = null;
        $totalMyTickets = null;
        $pendingMyTickets = null;
        $resolvedMyTickets = null;
        if ($user->can('view own tickets') && ($user->hasRole('secretary') || $user->hasRole('noc'))) {
            $myTickets = Ticket::where('created_by', $user->id)->latest()->limit(5)->get();
            $totalMyTickets = Ticket::where('created_by', $user->id)->count();
            $pendingMyTickets = Ticket::where('created_by', $user->id)->where('status', 'pending')->count();
            $resolvedMyTickets = Ticket::where('created_by', $user->id)->where('status', 'resolved')->count();
        }

        // Tickets pendientes NOC (permiso 'view pending noc tickets')
        $pendingNocTickets = null;
        $pendingNocCount = null;
        if ($user->can('view pending noc tickets')) {
            $pendingNocTickets = Ticket::where('requires_noc', true)
                ->where('status', 'pending')
                ->latest()
                ->get();
            $pendingNocCount = $pendingNocTickets->count();
        }

        // Clientes recientes (permiso 'view clients')
        $recentClients = null;
        if ($user->can('view clients')) {
            $recentClients = Client::latest()->limit(5)->get();
        }

        // Resueltos por NOC hoy (permiso 'view resolutions')
        $resolvedToday = null;
        if ($user->can('view resolutions')) {
            $resolvedToday = Ticket::where('resolved_by', $user->id)
                ->whereDate('resolved_at', today())
                ->count();
        }

        // Órdenes de trabajo relacionadas con tickets del usuario (solo si aplica)
        $relatedWorkOrders = null;
        if ($user->can('view own work_orders') && ($user->hasRole('secretary') || $user->hasRole('noc'))) {
            $relatedWorkOrders = WorkOrder::whereHas('ticket', function ($q) use ($user) {
                $q->where('created_by', $user->id);
            })->latest()->limit(5)->get();
        }

        return view('livewire.reports.dashboard', compact(
            'lowStockCount',
            'pendingRequestsCount',
            'activeWorkOrdersCount',
            'todayMovementsCount',
            'recentMovements',
            'myTickets',
            'totalMyTickets',
            'pendingMyTickets',
            'resolvedMyTickets',
            'pendingNocTickets',
            'pendingNocCount',
            'recentClients',
            'resolvedToday',
            'relatedWorkOrders'
        ))->layout('components.layouts.app');
    }

    // Métodos auxiliares para acciones desde el dashboard (resolver remotamente, crear OT)
    public function resolveRemote($ticketId)
    {
        $ticket = Ticket::find($ticketId);
        if ($ticket && $ticket->requires_noc && auth()->user()->can('edit tickets')) {
            $ticket->status = 'resolved';
            $ticket->resolved_by = auth()->id();
            $ticket->resolved_at = now();
            $ticket->save();
            session()->flash('message', 'Ticket resuelto remotamente.');
        }
        return redirect()->route('noc.panel');
    }

    public function createWorkOrder($ticketId)
    {
        $ticket = Ticket::with('client')->find($ticketId);
        if ($ticket && auth()->user()->can('create work_orders')) {
            $workOrder = WorkOrder::create([
                'ticket_id' => $ticket->id,
                'client_id' => $ticket->client_id,
                'status' => 'pending',
                'notes' => $ticket->description,
            ]);
            $ticket->status = 'in_progress';
            $ticket->save();
            session()->flash('message', 'OT creada a partir del ticket.');
        }
        return redirect()->route('work-orders.index');
    }
}