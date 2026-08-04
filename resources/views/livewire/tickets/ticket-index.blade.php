<div class="max-w-7xl mx-auto" wire:poll.15s="$refresh">
    <x-ui.card icon="confirmation_number" title="Tickets" subtitle="Listado de solicitudes de servicio">
        <x-slot:headerActions>
            <div class="flex items-center gap-2">
                <div class="flex items-center bg-gray-100 rounded-lg p-1">
                    <button wire:click="setViewMode('cards')"
                        class="px-3 py-1.5 text-xs font-medium rounded-md transition flex items-center gap-1.5
                        {{ $viewMode === 'cards' ? 'bg-white shadow-sm text-blue-600' : 'text-gray-500 hover:text-gray-700' }}">
                        <span class="material-symbols-outlined text-sm">grid_view</span>
                        <span class="hidden sm:inline">Cards</span>
                    </button>
                    <button wire:click="setViewMode('table')"
                        class="px-3 py-1.5 text-xs font-medium rounded-md transition flex items-center gap-1.5
                        {{ $viewMode === 'table' ? 'bg-white shadow-sm text-blue-600' : 'text-gray-500 hover:text-gray-700' }}">
                        <span class="material-symbols-outlined text-sm">table_rows</span>
                        <span class="hidden sm:inline">Tabla</span>
                    </button>
                </div>
                @can('create tickets')
                    <x-ui.button variant="primary" icon="add_circle" href="{{ route('tickets.create') }}">Nuevo Ticket</x-ui.button>
                @endcan
            </div>
        </x-slot:headerActions>

        <div class="p-6 space-y-5">
            {{-- KPIs --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <button wire:click="$set('statusFilter', '')"
                    class="flex items-center gap-3 w-full rounded-xl border border-gray-200 bg-white p-4 text-left hover:shadow-sm transition-all
                    {{ $statusFilter === '' && $activeTab === 'all' ? 'ring-2 ring-blue-200 border-blue-300' : 'hover:border-blue-200' }}">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-xl">confirmation_number</span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xl font-bold text-gray-900 leading-none">{{ $kpis['active'] }}</p>
                        <p class="text-xs font-medium text-gray-500 mt-1">Activos</p>
                    </div>
                </button>

                <button wire:click="$set('statusFilter', 'pending')"
                    class="flex items-center gap-3 w-full rounded-xl border border-gray-200 bg-white p-4 text-left hover:shadow-sm transition-all
                    {{ $statusFilter === 'pending' ? 'ring-2 ring-amber-200 border-amber-300' : 'hover:border-amber-200' }}">
                    <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-xl">hourglass_empty</span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xl font-bold text-gray-900 leading-none">{{ $kpis['open'] }}</p>
                        <p class="text-xs font-medium text-gray-500 mt-1">Sin atender</p>
                    </div>
                </button>

                <button wire:click="$set('statusFilter', 'in_progress')"
                    class="flex items-center gap-3 w-full rounded-xl border border-gray-200 bg-white p-4 text-left hover:shadow-sm transition-all
                    {{ $statusFilter === 'in_progress' ? 'ring-2 ring-sky-200 border-sky-300' : 'hover:border-sky-200' }}">
                    <div class="w-10 h-10 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-xl">sync</span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xl font-bold text-gray-900 leading-none">{{ $kpis['in_progress'] }}</p>
                        <p class="text-xs font-medium text-gray-500 mt-1">En proceso</p>
                    </div>
                </button>

                <button wire:click="$set('statusFilter', 'resolved')"
                    class="flex items-center gap-3 w-full rounded-xl border border-gray-200 bg-white p-4 text-left hover:shadow-sm transition-all
                    {{ $statusFilter === 'resolved' ? 'ring-2 ring-green-200 border-green-300' : 'hover:border-green-200' }}">
                    <div class="w-10 h-10 rounded-lg bg-green-50 text-green-600 flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-xl">check_circle</span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xl font-bold text-gray-900 leading-none">{{ $kpis['resolved'] }}</p>
                        <p class="text-xs font-medium text-gray-500 mt-1">Resueltos</p>
                    </div>
                </button>
            </div>
            @if($kpis['resolved'] > 0)
                <div class="flex justify-end">
                    <button wire:click="$set('statusFilter', 'resolved')"
                        class="inline-flex items-center gap-1 text-xs text-gray-500 hover:text-green-700 transition {{ $statusFilter === 'resolved' ? 'text-green-700 font-semibold' : '' }}">
                        <span class="material-symbols-outlined text-sm">check_circle</span>
                        Ver {{ $kpis['resolved'] }} resueltos
                    </button>
                </div>
            @endif

            {{-- Filtros --}}
            <div class="flex flex-wrap items-end gap-3">
                <x-ui.input type="text" wire:model.live="search" icon="search" label="Buscar" placeholder="Cliente, descripción o código..." class="flex-1 min-w-[200px]" />
                <x-ui.select wire:model.live="statusFilter" label="Estado" icon="flag" class="w-40">
                    <option value="">Todos los estados</option>
                    <option value="pending">Pendiente</option>
                    <option value="open">Abierto</option>
                    <option value="in_progress">En progreso</option>
                    <option value="resolved">Resuelto</option>
                    <option value="closed">Cerrado</option>
                    <option value="cancelled">Cancelado</option>
                </x-ui.select>
                <x-ui.select wire:model.live="priorityFilter" label="Prioridad" icon="priority_high" class="w-32">
                    <option value="">Todas</option>
                    <option value="P1">P1 · Crítica</option>
                    <option value="P2">P2 · Alta</option>
                    <option value="P3">P3 · Media</option>
                    <option value="P4">P4 · Baja</option>
                </x-ui.select>
                @if($search || $statusFilter || $priorityFilter)
                    <button wire:click="$set('search', ''); $set('statusFilter', ''); $set('priorityFilter', '')"
                        class="inline-flex items-center gap-1 px-3 py-2 rounded-lg text-xs font-medium bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors">
                        <span class="material-symbols-outlined text-sm">filter_alt_off</span>
                        Limpiar filtros
                    </button>
                @endif
            </div>

            {{-- Tabs --}}
            <div class="border-b border-gray-200">
                <nav class="flex gap-1" role="tablist">
                    @php
                        $tabLabels = ['all' => 'Todos', 'ot' => 'OT', 'noc' => 'NOC'];
                        $tabIcons = ['all' => 'confirmation_number', 'ot' => 'build', 'noc' => 'dns'];
                        $baseQuery = \App\Models\Ticket::query();
                        $tabCounts = [
                            'all' => (clone $baseQuery)->count(),
                            'ot' => (clone $baseQuery)->where('create_ot', true)->count(),
                            'noc' => (clone $baseQuery)->where('requires_noc', true)->count(),
                        ];
                    @endphp
                    @foreach (['all', 'ot', 'noc'] as $tab)
                        <button wire:click="setActiveTab('{{ $tab }}')" role="tab"
                            class="flex items-center gap-2 px-4 py-3 text-sm font-medium border-b-2 transition -mb-px
                            {{ $activeTab === $tab ? 'border-blue-600 text-blue-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            <span class="material-symbols-outlined text-base">{{ $tabIcons[$tab] }}</span>
                            {{ $tabLabels[$tab] }}
                            <span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full text-xs font-medium
                                {{ $activeTab === $tab ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $tabCounts[$tab] }}
                            </span>
                        </button>
                    @endforeach
                </nav>
            </div>

            {{-- VISTA CARDS --}}
            @if($viewMode === 'cards')
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @forelse($tickets as $ticket)
                @php
                    $canViewWorkOrders = auth()->user()->can('view work_orders');
                    $ticketPrio = $ticket->priority;
                    $statusBadge = match($ticket->status) {
                        'pending' => ['Pendiente', 'warning'],
                        'open' => ['Abierto', 'warning'],
                        'in_progress' => ['En progreso', 'info'],
                        'resolved' => ['Resuelto', 'success'],
                        'closed' => ['Cerrado', 'neutral'],
                        'cancelled' => ['Cancelado', 'danger'],
                        default => [$ticket->status, 'neutral'],
                    };
                @endphp
                <div class="rounded-xl border overflow-hidden hover:shadow-md transition-all
                    {{ $ticketPrio === 'P1' ? 'border-red-400 ring-2 ring-red-100 bg-red-50/30 hover:border-red-500' : 'border-gray-200 bg-white hover:border-blue-300' }}">
                    <button wire:click="viewDetail({{ $ticket->id }})" class="block w-full p-4 text-left group">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-1.5 min-w-0">
                                <span class="font-mono font-bold text-xs text-gray-700">{{ $ticket->ticket_code ?? '—' }}</span>
                                @if($ticketPrio)
                                    <x-ui.badge :variant="match($ticketPrio) { 'P1' => 'danger', 'P2' => 'warning', 'P3' => 'info', default => 'neutral' }">{{ $ticketPrio }}</x-ui.badge>
                                @endif
                            </div>
                            <span class="inline-flex items-center gap-0.5 text-[10px] text-gray-400 group-hover:text-blue-600 transition">Ver
                                <span class="material-symbols-outlined text-xs">arrow_forward</span>
                            </span>
                        </div>
                        <p class="text-sm font-medium text-gray-800 truncate">{{ $ticket->client?->name ?? '—' }}</p>
                        <p class="text-xs text-gray-500 mt-1 line-clamp-2">{{ Str::limit($ticket->description, 80) }}</p>
                        <div class="flex items-center justify-between mt-3 pt-2 border-t border-gray-100">
                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-gray-100 text-gray-700 font-medium">{{ $ticket->service_type }}</span>
                            <x-ui.badge :variant="$statusBadge[1]">{{ $statusBadge[0] }}</x-ui.badge>
                        </div>
                    </button>
                    <div class="px-4 py-2.5 bg-gray-50/50 border-t border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-1">
                            @if($ticket->workOrder && $canViewWorkOrders)
                                <a href="{{ route('work-orders.show', $ticket->workOrder->id) }}" class="text-xs font-medium text-blue-600 hover:text-blue-800">Ver OT</a>
                            @endif
                            <a href="{{ route('sla.ticket-timeline', $ticket->id) }}" class="p-1.5 text-purple-600 hover:bg-purple-100 rounded-lg transition" title="Timeline SLA">
                                <span class="material-symbols-outlined text-sm">account_tree</span>
                            </a>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-span-full rounded-xl border border-dashed border-gray-200 py-12 text-center bg-gray-50/50">
                    <span class="material-symbols-outlined text-gray-300 text-4xl mb-2 block">inbox</span>
                    <p class="text-gray-500">No hay tickets registrados</p>
                    <p class="text-sm text-gray-400 mt-1">Haz clic en "Nuevo Ticket" para crear uno</p>
                </div>
                @endforelse
            </div>
            @else
            {{-- VISTA TABLA --}}
            <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">Código</th>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">Cliente</th>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">Tipo</th>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">Prioridad</th>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">Descripción</th>
                            <th class="px-4 py-3 text-center text-gray-600 font-medium">NOC</th>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">Estado</th>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">Creado</th>
                            <th class="px-4 py-3 text-center text-gray-600 font-medium">OT</th>
                            <th class="px-4 py-3 text-center text-gray-600 font-medium">Timeline</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($tickets as $ticket)
                            @php $canViewWorkOrders = auth()->user()->can('view work_orders'); @endphp
                            <tr class="hover:bg-gray-50/80 transition">
                                <td class="px-4 py-3 font-mono text-xs text-gray-700">{{ $ticket->ticket_code ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-800 max-w-[150px] truncate" title="{{ $ticket->client?->name }}">{{ $ticket->client?->name ?? '—' }}</td>
                                <td class="px-4 py-3"><x-ui.badge variant="neutral">{{ $ticket->service_type }}</x-ui.badge></td>
                                <td class="px-4 py-3">
                                    @if($ticket->priority)
                                        <x-ui.badge :variant="match($ticket->priority) { 'P1' => 'danger', 'P2' => 'warning', 'P3' => 'info', default => 'neutral' }">{{ $ticket->priority }}</x-ui.badge>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-600 max-w-[200px] truncate" title="{{ $ticket->description }}">{{ Str::limit($ticket->description, 50) }}</td>
                                <td class="px-4 py-3 text-center">
                                    @if($ticket->requires_noc)<x-ui.badge variant="info" icon="dns">Sí</x-ui.badge>@else<span class="text-gray-400">—</span>@endif
                                </td>
                                <td class="px-4 py-3">
                                    @php $statusBadge = match($ticket->status) { 'pending' => ['Pendiente', 'warning'], 'open' => ['Abierto', 'warning'], 'in_progress' => ['En progreso', 'info'], 'resolved' => ['Resuelto', 'success'], 'closed' => ['Cerrado', 'neutral'], 'cancelled' => ['Cancelado', 'danger'], default => [$ticket->status, 'neutral'] }; @endphp
                                    <x-ui.badge :variant="$statusBadge[1]">{{ $statusBadge[0] }}</x-ui.badge>
                                </td>
                                <td class="px-4 py-3 text-gray-700 font-mono text-xs">{{ $ticket->created_at->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-center">
                                    @if($ticket->workOrder && $canViewWorkOrders)
                                        <a href="{{ route('work-orders.show', $ticket->workOrder->id) }}" class="text-blue-600 hover:text-blue-800 font-medium text-sm">Ver OT</a>
                                    @elseif(!$ticket->workOrder)
                                        <button wire:click="viewDetail({{ $ticket->id }})" class="text-purple-600 hover:text-purple-800 font-medium text-sm">Ver Ticket</button>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('sla.ticket-timeline', $ticket->id) }}" class="inline-flex items-center gap-1 text-xs text-gray-500 hover:text-blue-600 transition">
                                        <span class="material-symbols-outlined text-sm">account_tree</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-4 py-12 text-center bg-gray-50/50">
                                    <span class="material-symbols-outlined text-gray-300 text-4xl mb-2">inbox</span>
                                    <p class="text-gray-500">No hay tickets registrados</p>
                                    <p class="text-sm text-gray-400 mt-1">Haz clic en "Nuevo Ticket" para crear uno</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @endif

            {{-- Paginación --}}
            @if($tickets->hasPages())
                <div class="mt-5">{{ $tickets->links() }}</div>
            @endif

            {{-- Mensajes de sesión --}}
            @if(session('message'))
                <x-ui.alert variant="success">{{ session('message') }}</x-ui.alert>
            @endif
            @if(session('error'))
                <x-ui.alert variant="danger">{{ session('error') }}</x-ui.alert>
            @endif
        </div>
    </x-ui.card>

    {{-- Modal de detalle (unificado) --}}
    @if($showDetailModal && $selectedTicket)
        @include('components.ticket-detail-modal', [
            'ticket' => $selectedTicket,
            'showNocButton' => auth()->user()->can('access noc panel'),
            'showCreateOtButton' => auth()->user()->can('create work_orders'),
        ])
    @endif

    {{-- Modal de confirmación --}}
    @if($confirmingAction)
        <div x-data="{ open: true }" x-show="open" x-cloak
            class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center"
            style="display: none;">
            <div class="relative mx-auto p-5 w-full max-w-md">
                <x-ui.card overflow="visible">
                    <div class="text-center">
                        <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-blue-100 mb-4">
                            <span class="material-symbols-outlined text-blue-600 text-2xl">help</span>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Confirmar acción</h3>
                        <p class="text-sm text-gray-600 mt-2">
                            ¿Crear una orden de trabajo a partir del ticket #{{ $confirmingTicketId }}?
                        </p>
                    </div>
                    <x-slot:footer>
                        <x-ui.button variant="primary" icon="engineering" wire:click="executeConfirmedAction">Sí, crear OT</x-ui.button>
                        <x-ui.button variant="secondary" @click="open = false" wire:click="cancelConfirmation">Cancelar</x-ui.button>
                    </x-slot:footer>
                </x-ui.card>
            </div>
        </div>
    @endif

    <div x-data="{ toast: null, toastType: null, toastMessage: '' }"
         x-on:show-toast.window="toast = true; toastType = $event.detail.type; toastMessage = $event.detail.message; setTimeout(() => toast = false, 3500)"
         x-show="toast" x-cloak class="fixed bottom-5 right-5 z-50 transition-all duration-300"
         x-transition:enter="transform ease-out duration-300" x-transition:enter-start="translate-y-2 opacity-0"
         x-transition:enter-end="translate-y-0 opacity-100" x-transition:leave="transform ease-in duration-200"
         x-transition:leave-start="translate-y-0 opacity-100" x-transition:leave-end="translate-y-2 opacity-0"
         style="display: none;">
        <div x-show="toastType === 'success'"
             class="bg-green-600 text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-3">
            <span class="material-symbols-outlined">check_circle</span> <span x-text="toastMessage" class="text-sm font-medium"></span>
        </div>
        <div x-show="toastType === 'error'"
             class="bg-red-600 text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-3">
            <span class="material-symbols-outlined">error</span> <span x-text="toastMessage" class="text-sm font-medium"></span>
        </div>
        <div x-show="toastType === 'info'"
             class="bg-blue-600 text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-3">
            <span class="material-symbols-outlined">info</span> <span x-text="toastMessage" class="text-sm font-medium"></span>
        </div>
    </div>

    <style>[x-cloak] { display: none !important; }</style>
</div>
