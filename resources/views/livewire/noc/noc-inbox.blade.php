<div class="max-w-7xl mx-auto" wire:poll.15s="loadTickets">
    <x-ui.card icon="dns" title="Bandeja NOC" subtitle="Gestión de tickets que requieren intervención del NOC">
        {{-- Tabs --}}
        <div class="border-b border-gray-200 -mx-6 px-6">
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

        <div class="mt-5">
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
                                    {{ Str::limit($ticket->description, 60) }}
                                </td>
                                @if($activeTab === 'pending')
                                    <td class="px-4 py-3 text-center font-mono text-sm text-amber-600 font-medium">
                                        @if($ticket->escalated_at)
                                            @php $wait = $ticket->escalated_at->diffInSeconds(now()); @endphp
                                            {{ \App\Services\TimelineService::formatDuration($wait) }}
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                @elseif($activeTab === 'in_progress')
                                    <td class="px-4 py-3 text-center font-mono text-sm text-blue-600 font-medium">
                                        @if($ticket->l2_started_at)
                                            @php $active = $ticket->l2_started_at->diffInSeconds(now()); @endphp
                                            {{ \App\Services\TimelineService::formatDuration($active) }}
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                @endif
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <x-ui.button variant="secondary" icon="visibility" size="sm" wire:click="viewDetail({{ $ticket->id }})">
                                            Ver
                                        </x-ui.button>
                                        @if($activeTab === 'pending')
                                            <x-ui.button variant="primary" icon="play_arrow" size="sm" wire:click="acceptTicket({{ $ticket->id }})">
                                                Aceptar
                                            </x-ui.button>
                                        @elseif($activeTab === 'in_progress')
                                            <x-ui.button variant="success" icon="check_circle" size="sm" wire:click="promptResolveRemote({{ $ticket->id }})">
                                                Resolver
                                            </x-ui.button>
                                            <x-ui.button variant="primary" icon="engineering" size="sm" wire:click="promptCreateWorkOrder({{ $ticket->id }})">
                                                Crear OT
                                            </x-ui.button>
                                        @elseif($activeTab === 'completed')
                                            <x-ui.button variant="secondary" icon="account_tree" size="sm" href="{{ route('sla.ticket-timeline', $ticket->id) }}">
                                                Timeline
                                            </x-ui.button>
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
                                <x-ui.button variant="primary" icon="play_arrow" wire:click="acceptTicket({{ $selectedTicket->id }})">
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
                            @else
                                ¿Crear una orden de trabajo a partir del ticket #{{ $confirmingTicketId }}?
                            @endif
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
</div>