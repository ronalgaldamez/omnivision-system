<div class="max-w-7xl mx-auto" wire:poll.15s="loadTickets">
    <x-ui.card icon="dns" title="Bandeja NOC" subtitle="Gestión de tickets que requieren intervención del NOC">
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
            </div>
        </x-slot:headerActions>

        <div class="p-6 space-y-5">
            {{-- KPIs --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <button wire:click="$set('activeTab', 'pending')"
                    class="flex items-center gap-3 w-full rounded-xl border border-gray-200 bg-white p-4 text-left hover:shadow-sm transition-all
                    {{ $activeTab === 'pending' ? 'ring-2 ring-blue-200 border-blue-300' : 'hover:border-blue-200' }}">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-xl">hourglass_empty</span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xl font-bold text-gray-900 leading-none">{{ $kpis['pending'] }}</p>
                        <p class="text-xs font-medium text-gray-500 mt-1">Pendientes</p>
                    </div>
                </button>

                <button wire:click="$set('activeTab', 'in_progress')"
                    class="flex items-center gap-3 w-full rounded-xl border border-gray-200 bg-white p-4 text-left hover:shadow-sm transition-all
                    {{ $activeTab === 'in_progress' ? 'ring-2 ring-sky-200 border-sky-300' : 'hover:border-sky-200' }}">
                    <div class="w-10 h-10 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-xl">engineering</span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xl font-bold text-gray-900 leading-none">{{ $kpis['in_progress'] }}</p>
                        <p class="text-xs font-medium text-gray-500 mt-1">En curso</p>
                    </div>
                </button>

                <button wire:click="$set('activeTab', 'completed')"
                    class="flex items-center gap-3 w-full rounded-xl border border-gray-200 bg-white p-4 text-left hover:shadow-sm transition-all
                    {{ $activeTab === 'completed' ? 'ring-2 ring-green-200 border-green-300' : 'hover:border-green-200' }}">
                    <div class="w-10 h-10 rounded-lg bg-green-50 text-green-600 flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-xl">check_circle</span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xl font-bold text-gray-900 leading-none">{{ $kpis['completed'] }}</p>
                        <p class="text-xs font-medium text-gray-500 mt-1">Completados</p>
                    </div>
                </button>

                <div class="flex items-center gap-3 w-full rounded-xl border border-gray-200 bg-white p-4 text-left">
                    <div class="w-10 h-10 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-xl">engineering</span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xl font-bold text-gray-900 leading-none">{{ $kpis['with_ot'] }}</p>
                        <p class="text-xs font-medium text-gray-500 mt-1">Con OT</p>
                    </div>
                </div>
            </div>

            {{-- Filtros --}}
            <div class="flex flex-wrap items-end gap-3">
                <x-ui.input type="text" wire:model.live="search" icon="search" label="Buscar" placeholder="Cliente o código de ticket..." class="flex-1 min-w-[200px]" />
                <x-ui.select wire:model.live="priorityFilter" label="Prioridad" icon="flag" class="w-32">
                    <option value="">Todas</option>
                    <option value="P1">P1 · Crítica</option>
                    <option value="P2">P2 · Alta</option>
                    <option value="P3">P3 · Media</option>
                    <option value="P4">P4 · Baja</option>
                </x-ui.select>
                @if($search || $priorityFilter)
                    <button wire:click="$set('search', ''); $set('priorityFilter', '')"
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
                        $tabLabels = ['pending' => 'Pendientes', 'in_progress' => 'En curso', 'completed' => 'Completados'];
                        $tabIcons = ['pending' => 'hourglass_empty', 'in_progress' => 'engineering', 'completed' => 'check_circle'];
                        $baseQuery = \App\Models\Ticket::where('requires_noc', true);
                        $tabCounts = [
                            'pending' => (clone $baseQuery)->whereNull('l2_started_at')->whereNotIn('status', ['resolved', 'cancelled'])->count(),
                            'in_progress' => (clone $baseQuery)->whereNotNull('l2_started_at')->whereNull('l2_ended_at')->count(),
                            'completed' => (clone $baseQuery)->whereNotNull('l2_ended_at')->count(),
                        ];
                    @endphp
                    @foreach (['pending', 'in_progress', 'completed'] as $tab)
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
                @php $ticketPrio = $ticket->priority; @endphp
                <div class="rounded-xl border overflow-hidden hover:shadow-md transition-all
                    {{ $ticketPrio === 'P1' ? 'border-red-400 ring-2 ring-red-100 bg-red-50/30 hover:border-red-500' : 'border-gray-200 bg-white hover:border-blue-300' }}">
                    <button wire:click="viewDetail({{ $ticket->id }})" class="block w-full p-4 text-left group">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-1.5 min-w-0">
                                <span class="font-mono font-bold text-xs text-gray-700">{{ $ticket->ticket_code ?? '—' }}</span>
                                @php $pVariant = match($ticketPrio) { 'P1' => 'danger', 'P2' => 'warning', 'P3' => 'info', default => 'neutral' }; @endphp
                                @if($ticketPrio)
                                    <x-ui.badge variant="{{ $pVariant }}">{{ $ticketPrio }}</x-ui.badge>
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
                            @if($activeTab === 'pending')
                                <span class="text-[10px] text-amber-600 font-medium font-mono">
                                    @if($ticket->escalated_at){{ \App\Services\TimelineService::formatDuration($ticket->escalated_at->diffInSeconds(now())) }}@endif
                                </span>
                            @elseif($activeTab === 'in_progress')
                                <span class="text-[10px] text-blue-600 font-medium font-mono">
                                    @if($ticket->l2_started_at){{ \App\Services\TimelineService::formatDuration($ticket->l2_started_at->diffInSeconds(now())) }}@endif
                                </span>
                            @endif
                        </div>
                    </button>
                    @if($activeTab === 'pending')
                    <div class="px-4 py-2.5 bg-gray-50/50 border-t border-gray-100">
                        <x-ui.button variant="primary" size="sm" icon="play_arrow" class="w-full" wire:click="promptAccept({{ $ticket->id }})">Aceptar</x-ui.button>
                    </div>
                    @elseif($activeTab === 'in_progress')
                    <div class="px-4 py-2.5 bg-gray-50/50 border-t border-gray-100 flex items-center gap-2">
                        <x-ui.button variant="success" size="sm" icon="check_circle" class="flex-1" wire:click="promptResolveRemote({{ $ticket->id }})">Resolver</x-ui.button>
                        <x-ui.button variant="primary" size="sm" icon="engineering" class="flex-1" wire:click="promptCreateWorkOrder({{ $ticket->id }})">Crear OT</x-ui.button>
                    </div>
                    @elseif($activeTab === 'completed')
                    <div class="px-4 py-2.5 bg-gray-50/50 border-t border-gray-100">
                        <x-ui.button variant="secondary" size="sm" icon="account_tree" class="w-full" href="{{ route('sla.ticket-timeline', $ticket->id) }}">Ver Timeline</x-ui.button>
                    </div>
                    @endif
                </div>
                @empty
                <div class="col-span-full rounded-xl border border-dashed border-gray-200 py-12 text-center bg-gray-50/50">
                    <span class="material-symbols-outlined text-gray-300 text-4xl mb-2 block">inbox</span>
                    <p class="text-gray-500">
                        @if($activeTab === 'pending') No hay tickets pendientes
                        @elseif($activeTab === 'in_progress') No hay tickets en curso
                        @else No hay tickets completados @endif
                    </p>
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
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">Servicio</th>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">Prioridad</th>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">Descripción</th>
                            @if($activeTab === 'pending')
                                <th class="px-4 py-3 text-center text-gray-600 font-medium">Espera</th>
                            @elseif($activeTab === 'in_progress')
                                <th class="px-4 py-3 text-center text-gray-600 font-medium">Tiempo activo</th>
                            @endif
                            <th class="px-4 py-3 text-center text-gray-600 font-medium">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($tickets as $ticket)
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
                                <td class="px-4 py-3 text-gray-600 max-w-[200px] truncate" title="{{ $ticket->description }}">{{ Str::limit($ticket->description, 60) }}</td>
                                @if($activeTab === 'pending')
                                    <td class="px-4 py-3 text-center font-mono text-sm text-amber-600 font-medium">
                                        @if($ticket->escalated_at){{ \App\Services\TimelineService::formatDuration($ticket->escalated_at->diffInSeconds(now())) }}@else<span class="text-gray-400">—</span>@endif
                                    </td>
                                @elseif($activeTab === 'in_progress')
                                    <td class="px-4 py-3 text-center font-mono text-sm text-blue-600 font-medium">
                                        @if($ticket->l2_started_at){{ \App\Services\TimelineService::formatDuration($ticket->l2_started_at->diffInSeconds(now())) }}@else<span class="text-gray-400">—</span>@endif
                                    </td>
                                @endif
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <x-ui.button variant="secondary" icon="visibility" size="sm" wire:click="viewDetail({{ $ticket->id }})">Ver</x-ui.button>
                                        @if($activeTab === 'pending')
                                            <x-ui.button variant="primary" icon="play_arrow" size="sm" wire:click="promptAccept({{ $ticket->id }})">Aceptar</x-ui.button>
                                        @elseif($activeTab === 'in_progress')
                                            <x-ui.button variant="success" icon="check_circle" size="sm" wire:click="promptResolveRemote({{ $ticket->id }})">Resolver</x-ui.button>
                                            <x-ui.button variant="primary" icon="engineering" size="sm" wire:click="promptCreateWorkOrder({{ $ticket->id }})">Crear OT</x-ui.button>
                                        @elseif($activeTab === 'completed')
                                            <x-ui.button variant="secondary" icon="account_tree" size="sm" href="{{ route('sla.ticket-timeline', $ticket->id) }}">Timeline</x-ui.button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $activeTab === 'completed' ? 7 : 8 }}" class="px-4 py-12 text-center bg-gray-50/50">
                                    <span class="material-symbols-outlined text-gray-300 text-4xl mb-2 block">inbox</span>
                                    <p class="text-gray-500">
                                        @if($activeTab === 'pending') No hay tickets pendientes
                                        @elseif($activeTab === 'in_progress') No hay tickets en curso
                                        @else No hay tickets completados @endif
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </x-ui.card>

    {{-- Detail Modal --}}
    @if($showDetailModal && $selectedTicket)
        <div x-data="{ open: true }" x-show="open" x-cloak
            class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-start justify-center pt-20"
            style="display: none;">
            <div class="relative mx-auto p-5 w-full max-w-2xl">
                <x-ui.card overflow="visible">
                    <x-slot:headerActions>
                        <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 transition">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </x-slot:headerActions>

                    <div class="space-y-3 max-h-[60vh] overflow-y-auto pr-1">
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-xs text-gray-500">Creado por</p>
                            <p class="font-medium">{{ $selectedTicket->createdBy->name ?? 'N/A' }}</p>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-xs text-gray-500">Código de asistencia</p>
                            <p class="font-mono text-sm font-bold">{{ $selectedTicket->ticket_code ?? 'N/A' }}</p>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-xs text-gray-500">Cliente</p>
                            <p class="font-medium">{{ $selectedTicket->client->name ?? 'N/A' }}</p>
                            <p class="text-sm">{{ $selectedTicket->client->phone ?? 'Sin teléfono' }}</p>
                            <p class="text-sm">{{ $selectedTicket->client->address ?? 'Sin dirección' }}</p>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-xs text-gray-500">Tipo de servicio</p>
                            <p class="font-medium">{{ ucfirst(str_replace('_', ' ', $selectedTicket->service_type)) }}</p>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-xs text-gray-500">Prioridad</p>
                            @if($selectedTicket->priority)
                                <x-ui.badge :variant="match($selectedTicket->priority) { 'P1' => 'danger', 'P2' => 'warning', 'P3' => 'info', default => 'neutral' }">
                                    {{ $selectedTicket->priority }} - @php $priorityLabels = ['P1' => 'Crítico', 'P2' => 'Alta', 'P3' => 'Media', 'P4' => 'Baja']; @endphp
                                    {{ $priorityLabels[$selectedTicket->priority] ?? $selectedTicket->priority }}
                                </x-ui.badge>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-xs text-gray-500">Origen</p>
                            <p class="font-medium">{{ $selectedTicket->origin ?? 'N/A' }}</p>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-xs text-gray-500">Descripción del problema</p>
                            <p class="text-sm whitespace-pre-wrap">{{ $selectedTicket->description }}</p>
                        </div>
                        @if($selectedTicket->notes)
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <p class="text-xs text-gray-500">Notas adicionales</p>
                                <p class="text-sm">{{ $selectedTicket->notes }}</p>
                            </div>
                        @endif
                    </div>

                    <x-slot:footer>
                        <div class="flex justify-end gap-2">
                            @if($activeTab === 'pending' && is_null($selectedTicket->l2_started_at))
                                <x-ui.button variant="primary" icon="play_arrow" wire:click="promptAccept({{ $selectedTicket->id }})">
                                    Aceptar ticket
                                </x-ui.button>
                            @endif
                            @if($activeTab === 'in_progress')
                                <x-ui.button variant="success" icon="check_circle" wire:click="promptResolveRemote({{ $selectedTicket->id }})">
                                    Resolver remotamente
                                </x-ui.button>
                                <x-ui.button variant="primary" icon="engineering" wire:click="promptCreateWorkOrder({{ $selectedTicket->id }})">
                                    Crear OT
                                </x-ui.button>
                            @endif
                            <x-ui.button variant="secondary" wire:click="closeModal">Cerrar</x-ui.button>
                        </div>
                    </x-slot:footer>
                </x-ui.card>
            </div>
        </div>
    @endif

    {{-- Confirmation Modal --}}
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
                            @if($confirmingAction === 'resolve')
                                ¿Confirmas la resolución remota del ticket #{{ $confirmingTicketId }}?
                            @elseif($confirmingAction === 'create_ot')
                                ¿Crear una orden de trabajo a partir del ticket #{{ $confirmingTicketId }}?
                            @elseif($confirmingAction === 'accept')
                                ¿Aceptar el ticket #{{ $confirmingTicketId }} para atenderlo en NOC?
                            @endif
                        </p>

                        @if($confirmingAction === 'create_ot')
                            <div class="mt-4 text-left">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Motivo de la creación de la OT *</label>
                                <textarea wire:model="createReason" rows="3"
                                    class="w-full px-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm resize-none"
                                    placeholder="Ej: El problema no puede resolverse de forma remota y requiere visita en campo..."></textarea>
                                <p class="text-[10px] text-gray-400 mt-1">Esta razón quedará registrada en las notas de la OT.</p>
                            </div>
                        @endif
                    </div>
                    <x-slot:footer>
                        @if($confirmingAction === 'create_ot')
                            <x-ui.button variant="primary" icon="engineering" wire:click="executeConfirmedAction">Sí, crear OT</x-ui.button>
                        @elseif($confirmingAction === 'resolve')
                            <x-ui.button variant="success" icon="check_circle" wire:click="executeConfirmedAction">Sí, resolver</x-ui.button>
                        @else
                            <x-ui.button variant="primary" icon="play_arrow" wire:click="executeConfirmedAction">Sí, aceptar</x-ui.button>
                        @endif
                        <x-ui.button variant="secondary" @click="open = false" wire:click="cancelConfirmation">Cancelar</x-ui.button>
                    </x-slot:footer>
                </x-ui.card>
            </div>
        </div>
    @endif
</div>
