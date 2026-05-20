<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\Product;
use App\Models\Requisition;
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

        // ========== TÉCNICO (vista separada) ==========
        if ($user->hasRole('technician')) {
            $pendingRequisitionsCount = Requisition::where('technician_id', $user->id)
                ->where('status', 'open')
                ->count();
            $activeWorkOrdersCount = WorkOrder::where('technician_id', $user->id)
                ->whereIn('status', ['pending', 'in_progress'])
                ->count();
            $recentRequisitions = Requisition::with('items.product')
                ->where('technician_id', $user->id)
                ->latest()
                ->limit(5)
                ->get();

            return view('livewire.reports.dashboard-technician', compact(
                'pendingRequisitionsCount',
                'activeWorkOrdersCount',
                'recentRequisitions'
            ))->layout('components.layouts.app');
        }

        // ========== DATOS GLOBALES ==========
        $lowStockCount = null;
        if ($user->can('view low stock')) {
            $lowStockCount = Product::whereColumn('current_stock', '<=', 'stock_min')->count();
        }

        // Requisiciones pendientes (visible para quien tenga view requisitions)
        $pendingRequisitionsCount = null;
        if ($user->can('view requisitions')) {
            $pendingRequisitionsCount = Requisition::where('status', 'open')->count();
        }

        // Órdenes activas
        $activeWorkOrdersCount = null;
        if ($user->can('view work_orders')) {
            $activeWorkOrdersCount = WorkOrder::whereIn('status', ['pending', 'in_progress'])->count();
        }

        // Movimientos
        $todayMovementsCount = null;
        $recentMovements = null;
        if ($user->can('view movements')) {
            $todayMovementsCount = Movement::whereDate('created_at', today())->count();
            $recentMovements = Movement::with('product', 'user')->latest()->limit(5)->get();
        }

        // Tickets del usuario (secretaria/NOC)
        $myTickets = null;
        $totalMyTickets = null;
        $pendingMyTickets = null;
        $resolvedMyTickets = null;
        if ($user->can('view own tickets') && ($user->hasRole('atencion_al_cliente') || $user->hasRole('noc'))) {
            $myTickets = Ticket::where('created_by', $user->id)->latest()->limit(5)->get();
            $totalMyTickets = Ticket::where('created_by', $user->id)->count();
            $pendingMyTickets = Ticket::where('created_by', $user->id)->where('status', 'pending')->count();
            $resolvedMyTickets = Ticket::where('created_by', $user->id)->where('status', 'resolved')->count();
        }

        // Tickets pendientes NOC
        $pendingNocTickets = null;
        $pendingNocCount = null;
        if ($user->can('view pending noc tickets')) {
            $pendingNocTickets = Ticket::where('requires_noc', true)
                ->where('status', 'pending')
                ->latest()
                ->get();
            $pendingNocCount = $pendingNocTickets->count();
        }

        // Clientes recientes
        $recentClients = null;
        if ($user->can('view clients')) {
            $recentClients = Client::latest()->limit(5)->get();
        }

        // Resueltos por NOC hoy
        $resolvedToday = null;
        if ($user->can('view resolutions')) {
            $resolvedToday = Ticket::where('resolved_by', $user->id)
                ->whereDate('resolved_at', today())
                ->count();
        }

        // Órdenes relacionadas con tickets del usuario
        $relatedWorkOrders = null;
        if ($user->can('view own work_orders') && ($user->hasRole('atencion_al_cliente') || $user->hasRole('noc'))) {
            $relatedWorkOrders = WorkOrder::whereHas('ticket', function ($q) use ($user) {
                $q->where('created_by', $user->id);
            })->latest()->limit(5)->get();
        }

        return view('livewire.reports.dashboard', compact(
            'lowStockCount',
            'pendingRequisitionsCount',
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

    // Métodos auxiliares que se mantienen
    public function resolveRemote($ticketId)
    {
        $ticket = Ticket::find($ticketId);
        if ($ticket && $ticket->requires_noc && auth()->user()->can('edit tickets')) {
            $ticket->status = 'resolved';
            $ticket->resolved_by = auth()->id();
            $ticket->resolved_at = now();
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