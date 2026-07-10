<div class="max-w-7xl mx-auto">
    <x-ui.card icon="dns" title="Panel NOC - Tickets pendientes" subtitle="Gestión de tickets que requieren intervención del NOC">
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
                        <th class="px-4 py-3 text-center text-gray-600 font-medium">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($tickets as $ticket)
                        <tr class="hover:bg-gray-50/80 transition">
                            <td class="px-4 py-3 font-mono text-xs text-gray-700">{{ $ticket->ticket_code ?? '—' }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-700">#{{ $ticket->id }}</td>
                            <td class="px-4 py-3 text-gray-800">{{ $ticket->client->name ?? 'N/A' }}</td>
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
                            <td class="px-4 py-3 text-gray-600 max-w-xs truncate" title="{{ $ticket->description }}">
                                {{ Str::limit($ticket->description, 100) }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <x-ui.button variant="secondary" icon="visibility" size="sm" wire:click="viewDetail({{ $ticket->id }})">
                                        Detalle
                                    </x-ui.button>
                                    <x-ui.button variant="success" icon="check_circle" size="sm" wire:click="promptResolveRemote({{ $ticket->id }})">
                                        Resolver
                                    </x-ui.button>
                                    <x-ui.button variant="primary" icon="engineering" size="sm" wire:click="promptCreateWorkOrder({{ $ticket->id }})">
                                        Crear OT
                                    </x-ui.button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center bg-gray-50/50">
                                <span class="material-symbols-outlined text-gray-300 text-4xl mb-2">checklist</span>
                                <p class="text-gray-500">No hay tickets pendientes</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>

    @if($showDetailModal && $selectedTicket)
        @include('components.ticket-detail-modal', [
            'ticket' => $selectedTicket,
            'showNocButton' => false,
            'showCreateOtButton' => auth()->user()->can('create work_orders'),
        ])
    @endif

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