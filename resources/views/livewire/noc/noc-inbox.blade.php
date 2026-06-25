<div class="max-w-7xl mx-auto" wire:poll.15s="loadTickets">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        {{-- Header --}}
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50">
            <div>
                <h1 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                    <span class="material-symbols-outlined text-gray-500">dns</span>
                    Bandeja NOC
                </h1>
                <p class="text-sm text-gray-500 mt-1">Gestión de tickets que requieren intervención del NOC</p>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="border-b border-gray-200">
            <nav class="flex gap-1 px-6" role="tablist">
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

        {{-- Content --}}
        <div class="p-6">
            <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">Código</th>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">ID</th>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">Cliente</th>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">Servicio</th>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">Prioridad</th>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">Origen</th>
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
                                <td class="px-4 py-3 font-mono text-xs text-gray-700">#{{ $ticket->id }}</td>
                                <td class="px-4 py-3 text-gray-800 max-w-[150px] truncate" title="{{ $ticket->client?->name }}">{{ $ticket->client?->name ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-gray-100 text-gray-700 rounded-full text-xs font-medium">
                                        {{ $ticket->service_type }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    @if($ticket->priority)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @switch($ticket->priority)
                                                @case('P1') bg-red-50 text-red-700 @break
                                                @case('P2') bg-orange-50 text-orange-700 @break
                                                @case('P3') bg-blue-50 text-blue-700 @break
                                                @default bg-gray-100 text-gray-600 @endswitch">
                                            {{ $ticket->priority }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-700">{{ $ticket->origin ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-600 max-w-[200px] truncate" title="{{ $ticket->description }}">
                                    {{ Str::limit($ticket->description, 60) }}
                                </td>
                                @if($activeTab === 'pending')
                                    <td class="px-4 py-3 text-center font-mono text-sm text-amber-600 font-medium">
                                        @if($ticket->escalated_at)
                                            @php
                                                $wait = $ticket->escalated_at->diffInSeconds(now());
                                            @endphp
                                            {{ \App\Services\TimelineService::formatDuration($wait) }}
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                @elseif($activeTab === 'in_progress')
                                    <td class="px-4 py-3 text-center font-mono text-sm text-blue-600 font-medium">
                                        @if($ticket->l2_started_at)
                                            @php
                                                $active = $ticket->l2_started_at->diffInSeconds(now());
                                            @endphp
                                            {{ \App\Services\TimelineService::formatDuration($active) }}
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                @endif
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button wire:click="viewDetail({{ $ticket->id }})"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-600 text-white text-xs font-medium rounded-lg shadow-sm hover:bg-gray-700 transition"
                                            title="Ver detalle completo">
                                            <span class="material-symbols-outlined text-base">visibility</span>
                                            Ver
                                        </button>
                                        @if($activeTab === 'pending')
                                            <button wire:click="acceptTicket({{ $ticket->id }})"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-lg shadow-sm hover:bg-blue-700 transition">
                                                <span class="material-symbols-outlined text-base">play_arrow</span>
                                                Aceptar
                                            </button>
                                        @elseif($activeTab === 'in_progress')
                                            <button wire:click="promptResolveRemote({{ $ticket->id }})"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-green-600 text-white text-xs font-medium rounded-lg shadow-sm hover:bg-green-700 transition">
                                                <span class="material-symbols-outlined text-base">check_circle</span>
                                                Resolver
                                            </button>
                                            <button wire:click="promptCreateWorkOrder({{ $ticket->id }})"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-lg shadow-sm hover:bg-blue-700 transition">
                                                <span class="material-symbols-outlined text-base">engineering</span>
                                                Crear OT
                                            </button>
                                        @elseif($activeTab === 'completed')
                                            <a href="{{ route('sla.ticket-timeline', $ticket->id) }}"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-500 text-white text-xs font-medium rounded-lg shadow-sm hover:bg-gray-600 transition">
                                                <span class="material-symbols-outlined text-base">account_tree</span>
                                                Timeline
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $activeTab === 'completed' ? 9 : 10 }}" class="px-4 py-12 text-center bg-gray-50/50">
                                    <span class="material-symbols-outlined text-gray-300 text-4xl mb-2">inbox</span>
                                    <p class="text-gray-500">
                                        @if($activeTab === 'pending')
                                            No hay tickets pendientes
                                        @elseif($activeTab === 'in_progress')
                                            No hay tickets en curso
                                        @else
                                            No hay tickets completados
                                        @endif
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Detail Modal --}}
    @if($showDetailModal && $selectedTicket)
        <div x-data="{ open: true }" x-show="open" x-cloak
            class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-xl bg-white">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Detalle del Ticket #{{ $selectedTicket->id }}</h3>
                    <button wire:click="closeModal" class="text-gray-500 hover:text-gray-700 text-2xl leading-none">&times;</button>
                </div>

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
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium
                                @switch($selectedTicket->priority)
                                    @case('P1') bg-red-100 text-red-700 @break
                                    @case('P2') bg-orange-100 text-orange-700 @break
                                    @case('P3') bg-blue-100 text-blue-700 @break
                                    @case('P4') bg-gray-100 text-gray-600 @break
                                @endswitch">
                                {{ $selectedTicket->priority }}
                                @php
                                    $priorityLabels = ['P1' => 'Crítico', 'P2' => 'Alta', 'P3' => 'Media', 'P4' => 'Baja'];
                                @endphp
                                - {{ $priorityLabels[$selectedTicket->priority] ?? $selectedTicket->priority }}
                            </span>
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

                    <div class="flex justify-end gap-2 pt-3 border-t border-gray-100">
                        @if($activeTab === 'pending' && is_null($selectedTicket->l2_started_at))
                            <button wire:click="acceptTicket({{ $selectedTicket->id }})"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition">
                                <span class="material-symbols-outlined text-base align-middle">play_arrow</span>
                                Aceptar ticket
                            </button>
                        @endif
                        @if($activeTab === 'in_progress')
                            <button wire:click="promptResolveRemote({{ $selectedTicket->id }})"
                                class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition">
                                <span class="material-symbols-outlined text-base align-middle">check_circle</span>
                                Resolver remotamente
                            </button>
                            <button wire:click="promptCreateWorkOrder({{ $selectedTicket->id }})"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition">
                                <span class="material-symbols-outlined text-base align-middle">engineering</span>
                                Crear OT
                            </button>
                        @endif
                        <button wire:click="closeModal"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-300 transition">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Confirmation Modal --}}
    @if($confirmingAction)
        <div x-data="{ open: true }" x-show="open" x-cloak
            class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center"
            style="display: none;">
            <div class="relative mx-auto p-5 w-full max-w-md">
                <div class="bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden">
                    <div class="p-6 text-center">
                        <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-blue-100 mb-4">
                            <span class="material-symbols-outlined text-blue-600 text-2xl">help</span>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Confirmar acción</h3>
                        <p class="text-sm text-gray-600 mt-2">
                            @if($confirmingAction === 'resolve')
                                ¿Confirmas la resolución remota del ticket #{{ $confirmingTicketId }}?
                            @else
                                ¿Crear una orden de trabajo a partir del ticket #{{ $confirmingTicketId }}?
                            @endif
                        </p>
                    </div>
                    <div class="bg-gray-50 px-6 py-4 flex flex-col gap-3 sm:flex-row-reverse">
                        <button wire:click="executeConfirmedAction"
                            class="w-full sm:w-auto px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-blue-700 transition">
                            Sí, continuar
                        </button>
                        <button @click="open = false" wire:click="cancelConfirmation"
                            class="w-full sm:w-auto px-5 py-2.5 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>[x-cloak] { display: none !important; }</style>
</div>
