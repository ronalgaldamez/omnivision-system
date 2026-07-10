{{-- Modal unificado de detalle de ticket --}}
@if($ticket)
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

                <div class="space-y-3">
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <p class="text-xs text-gray-500">Creado por</p>
                        <p class="font-medium">
                            {{ $ticket->createdBy->name ?? 'N/A' }}
                            <span class="text-gray-500 text-sm">
                                - {{ str_pad($ticket->createdBy->id ?? 0, 3, '0', STR_PAD_LEFT) }}
                                - {{ $ticket->createdBy->roles->first()->name ?? 'Sin área' }}
                            </span>
                        </p>
                    </div>

                    <div class="bg-gray-50 p-3 rounded-lg">
                        <p class="text-xs text-gray-500">Código de asistencia</p>
                        <p class="font-mono text-sm font-bold">{{ $ticket->ticket_code ?? 'N/A' }}</p>
                    </div>

                    <div class="bg-gray-50 p-3 rounded-lg">
                        <p class="text-xs text-gray-500">Cliente</p>
                        <p class="font-medium">{{ $ticket->client->name ?? 'N/A' }}</p>
                        <p class="text-sm">{{ $ticket->client->phone ?? 'Sin teléfono' }}</p>
                        <p class="text-sm">{{ $ticket->client->address ?? 'Sin dirección' }}</p>
                    </div>

                    <div class="bg-gray-50 p-3 rounded-lg">
                        <p class="text-xs text-gray-500">Tipo de servicio</p>
                        <p class="font-medium">{{ ucfirst(str_replace('_', ' ', $ticket->service_type)) }}</p>
                    </div>

                    <div class="bg-gray-50 p-3 rounded-lg">
                        <p class="text-xs text-gray-500">Prioridad</p>
                        @if($ticket->priority)
                            <x-ui.badge :variant="match($ticket->priority) { 'P1' => 'danger', 'P2' => 'warning', 'P3' => 'info', default => 'neutral' }">
                                {{ $ticket->priority }} - @php $priorityLabels = ['P1' => 'Crítico', 'P2' => 'Alta', 'P3' => 'Media', 'P4' => 'Baja']; @endphp
                                {{ $priorityLabels[$ticket->priority] ?? $ticket->priority }}
                            </x-ui.badge>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </div>

                    <div class="bg-gray-50 p-3 rounded-lg">
                        <p class="text-xs text-gray-500">Origen</p>
                        <p class="font-medium">{{ $ticket->origin ?? 'N/A' }}</p>
                    </div>

                    <div class="bg-gray-50 p-3 rounded-lg">
                        <p class="text-xs text-gray-500">Requiere NOC</p>
                        @if($ticket->requires_noc)
                            <x-ui.badge variant="info">Sí</x-ui.badge>
                        @else
                            <x-ui.badge variant="neutral">No</x-ui.badge>
                        @endif
                    </div>

                    <div class="bg-gray-50 p-3 rounded-lg">
                        <p class="text-xs text-gray-500">Descripción del problema</p>
                        <p class="text-sm whitespace-pre-wrap">{{ $ticket->description }}</p>
                    </div>

                    @if($ticket->notes)
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-xs text-gray-500">Notas adicionales</p>
                            <p class="text-sm">{{ $ticket->notes }}</p>
                        </div>
                    @endif
                </div>

                <x-slot:footer>
                    <div class="flex justify-center gap-2">
                        @if($showNocButton ?? false)
                            <x-ui.button variant="success" wire:click="goToNocPanel">Ir a bandeja NOC</x-ui.button>
                        @endif
                        @if(($showCreateOtButton ?? true) && auth()->user()->can('create work_orders') && (!$ticket->requires_noc || $ticket->l2_started_at))
                            <x-ui.button variant="primary" wire:click="promptCreateWorkOrder({{ $ticket->id }})">Crear OT</x-ui.button>
                        @endif
                        <x-ui.button variant="secondary" wire:click="closeModal">Cerrar</x-ui.button>
                    </div>
                </x-slot:footer>
            </x-ui.card>
        </div>
    </div>
@endif