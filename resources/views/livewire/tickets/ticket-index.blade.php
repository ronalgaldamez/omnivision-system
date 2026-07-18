<div class="max-w-7xl mx-auto" wire:poll.15s="$refresh">
    <x-ui.card icon="confirmation_number" title="Tickets" subtitle="Listado de solicitudes de servicio">
        <x-slot:headerActions>
            @can('create tickets')
                <x-ui.button variant="primary" icon="add_circle" href="{{ route('tickets.create') }}">
                    Nuevo Ticket
                </x-ui.button>
            @endcan
        </x-slot:headerActions>

        {{-- Tabs --}}
        <div class="border-b border-gray-200 -mx-6 px-6 mb-5">
            <nav class="flex gap-1" role="tablist">
                @php
                    $tabLabels = ['all' => 'Todos', 'contracts' => 'Contratos', 'ot' => 'OT', 'noc' => 'NOC'];
                    $tabIcons = ['all' => 'confirmation_number', 'contracts' => 'description', 'ot' => 'build', 'noc' => 'dns'];
                    $baseQuery = \App\Models\Ticket::query();
                    $tabCounts = [
                        'all' => (clone $baseQuery)->count(),
                        'contracts' => (clone $baseQuery)->where('requires_contract', true)->count(),
                        'ot' => (clone $baseQuery)->where('create_ot', true)->count(),
                        'noc' => (clone $baseQuery)->where('requires_noc', true)->count(),
                    ];
                @endphp
                @foreach (['all', 'contracts', 'ot', 'noc'] as $tab)
                    <button wire:click="setActiveTab('{{ $tab }}')" role="tab"
                        class="flex items-center gap-2 px-4 py-3 text-sm font-medium border-b-2 transition -mb-px
                        {{ $activeTab === $tab ? 'border-indigo-600 text-indigo-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        <span class="material-symbols-outlined text-base">{{ $tabIcons[$tab] }}</span>
                        {{ $tabLabels[$tab] }}
                        <span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full text-xs font-medium
                            {{ $activeTab === $tab ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $tabCounts[$tab] }}
                        </span>
                    </button>
                @endforeach
            </nav>
        </div>

        {{-- Filtros --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-5">
            <x-ui.input type="text" wire:model.live="search"
                placeholder="Buscar por cliente, descripción o código de asistencia..." icon="search" />
            <x-ui.select wire:model.live="statusFilter" icon="filter_alt" placeholder="Todos los estados">
                <option value="">Todos los estados</option>
                <option value="pending">Pendiente</option>
                <option value="in_progress">En progreso</option>
                <option value="resolved">Resuelto</option>
                <option value="closed">Cerrado</option>
                <option value="cancelled">Cancelado</option>
            </x-ui.select>
        </div>

        {{-- Tabla --}}
        <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="px-4 py-3 text-left text-gray-600 font-medium">Código</th>
                        <th class="px-4 py-3 text-left text-gray-600 font-medium">ID</th>
                        <th class="px-4 py-3 text-left text-gray-600 font-medium">Cliente</th>
                        <th class="px-4 py-3 text-left text-gray-600 font-medium">Tipo</th>
                        <th class="px-4 py-3 text-left text-gray-600 font-medium">Prioridad</th>
                        <th class="px-4 py-3 text-left text-gray-600 font-medium">Origen</th>
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
                        @php
                            $canViewWorkOrders = auth()->user()->can('view work_orders');
                            $canCreateWorkOrders = auth()->user()->can('create work_orders');
                        @endphp
                        <tr class="hover:bg-gray-50/80 transition">
                            <td class="px-4 py-3 font-mono text-xs text-gray-700">
                                {{ $ticket->ticket_code ?? '—' }}
                            </td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-700">#{{ $ticket->id }}</td>
                            <td class="px-4 py-3 text-gray-800">{{ $ticket->client?->name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <x-ui.badge variant="neutral">{{ $ticket->service_type }}</x-ui.badge>
                            </td>
                            <td class="px-4 py-3">
                                @if($ticket->priority)
                                    <x-ui.badge :variant="match($ticket->priority) { 'P1' => 'danger', 'P2' => 'warning', 'P3' => 'info', default => 'neutral' }">
                                        {{ $ticket->priority }}
                                    </x-ui.badge>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ $ticket->origin ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-600 max-w-[200px] truncate" title="{{ $ticket->description }}">
                                {{ Str::limit($ticket->description, 50) }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($ticket->requires_noc)
                                    <x-ui.badge variant="info" icon="dns">Sí</x-ui.badge>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $statusBadge = match($ticket->status) {
                                        'pending' => ['Pendiente', 'warning'],
                                        'in_progress' => ['En progreso', 'info'],
                                        'resolved' => ['Resuelto', 'success'],
                                        'closed' => ['Cerrado', 'neutral'],
                                        'cancelled' => ['Cancelado', 'danger'],
                                        default => [$ticket->status, 'neutral'],
                                    };
                                @endphp
                                <x-ui.badge :variant="$statusBadge[1]">{{ $statusBadge[0] }}</x-ui.badge>
                            </td>
                            <td class="px-4 py-3 text-gray-700 font-mono text-xs">
                                {{ $ticket->created_at->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($ticket->workOrder && $canViewWorkOrders)
                                    <a href="{{ route('work-orders.show', $ticket->workOrder->id) }}"
                                        class="text-blue-600 hover:text-blue-800 font-medium text-sm">Ver OT</a>
                                @elseif(!$ticket->workOrder)
                                    <button wire:click="viewDetail({{ $ticket->id }})"
                                        class="text-purple-600 hover:text-purple-800 font-medium text-sm">Ver Ticket</button>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('sla.ticket-timeline', $ticket->id) }}"
                                    class="inline-flex items-center gap-1 text-xs text-gray-500 hover:text-blue-600 transition">
                                    <span class="material-symbols-outlined text-sm">account_tree</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="px-4 py-12 text-center bg-gray-50/50">
                                <span class="material-symbols-outlined text-gray-300 text-4xl mb-2">inbox</span>
                                <p class="text-gray-500">No hay tickets registrados</p>
                                <p class="text-sm text-gray-400 mt-1">Haz clic en "Nuevo Ticket" para crear uno</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

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
                        <x-ui.button variant="primary" wire:click="executeConfirmedAction">Sí, continuar</x-ui.button>
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