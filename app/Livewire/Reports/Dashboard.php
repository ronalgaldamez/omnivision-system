<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\Product;
use App\Models\Requisition;
use App\Models\WorkOrder;
use App\Models\Movement;
use App\Models\Ticket;
use App\Models\Client;
use App\Services\SlaService;
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
            $pendingRequisitionsCount = Requisition::where('status', 'open')->count();
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

        $myTickets = null;
        $totalMyTickets = null;
        $pendingMyTickets = null;
        $resolvedMyTickets = null;
        if ($user->can('view own tickets')) {
            $myTickets = Ticket::where('created_by', $user->id)->latest()->limit(5)->get();
            $totalMyTickets = Ticket::where('created_by', $user->id)->count();
            $pendingMyTickets = Ticket::where('created_by', $user->id)->where('status', 'pending')->count();
            $resolvedMyTickets = Ticket::where('created_by', $user->id)->where('status', 'resolved')->count();
        }

        $pendingNocTickets = null;
        $pendingNocCount = null;
        if ($user->can('view pending noc tickets')) {
            $pendingNocTickets = Ticket::where('requires_noc', true)
                ->where('status', 'pending')
                ->latest()
                ->get();
            $pendingNocCount = $pendingNocTickets->count();
        }

        $recentClients = null;
        if ($user->can('view clients')) {
            $recentClients = Client::latest()->limit(5)->get();
        }

        $resolvedToday = null;
        if ($user->can('view resolutions')) {
            $resolvedToday = Ticket::where('resolved_by', $user->id)
                ->whereDate('resolved_at', today())
                ->count();
        }

        $relatedWorkOrders = null;
        if ($user->can('view own work_orders')) {
            $relatedWorkOrders = WorkOrder::whereHas('ticket', function ($q) use ($user) {
                $q->where('created_by', $user->id);
            })->latest()->limit(5)->get();
        }

        // ========== DATOS ESPECÍFICOS PARA TÉCNICO ==========
        $techPendingRequisitionsCount = null;
        $techActiveWorkOrdersCount = null;
        $techRecentRequisitions = null;
        if ($user->can('view technician dashboard')) {
            $techPendingRequisitionsCount = Requisition::where('technician_id', $user->id)
                ->where('status', 'open')
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
            'myTickets',
            'totalMyTickets',
            'pendingMyTickets',
            'resolvedMyTickets',
            'pendingNocTickets',
            'pendingNocCount',
            'recentClients',
            'resolvedToday',
            'relatedWorkOrders',
            'techPendingRequisitionsCount',
            'techActiveWorkOrdersCount',
            'techRecentRequisitions'
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
            $workOrder = WorkOrder::create([
                'ticket_id' => $ticket->id,
                'client_id' => $ticket->client_id,
                'status' => 'pending',
                'notes' => $ticket->description,
            ]);
            $ticket->status = 'in_progress';
            $ticket->save();
            app(SlaService::class)->evaluateSla($ticket);
            session()->flash('message', 'OT creada a partir del ticket.');
        }
        return redirect()->route('work-orders.index');
    }
}