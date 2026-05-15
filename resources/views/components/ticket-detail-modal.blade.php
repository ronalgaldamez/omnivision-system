{{-- Modal unificado de detalle de ticket --}}
@if($ticket)
    <div x-data="{ open: true }" x-show="open" x-cloak
        class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-xl bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Detalle del Ticket #{{ $ticket->id }}</h3>
                <button wire:click="closeModal" class="text-gray-500 hover:text-gray-700">&times;</button>
            </div>

            <div class="space-y-3">
                {{-- Creado por (con código y área) --}}
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

                {{-- Código de asistencia --}}
                <div class="bg-gray-50 p-3 rounded-lg">
                    <p class="text-xs text-gray-500">Código de asistencia</p>
                    <p class="font-mono text-sm font-bold">{{ $ticket->ticket_code ?? 'N/A' }}</p>
                </div>

                {{-- Cliente --}}
                <div class="bg-gray-50 p-3 rounded-lg">
                    <p class="text-xs text-gray-500">Cliente</p>
                    <p class="font-medium">{{ $ticket->client->name ?? 'N/A' }}</p>
                    <p class="text-sm">{{ $ticket->client->phone ?? 'Sin teléfono' }}</p>
                    <p class="text-sm">{{ $ticket->client->address ?? 'Sin dirección' }}</p>
                </div>

                {{-- Tipo de servicio --}}
                <div class="bg-gray-50 p-3 rounded-lg">
                    <p class="text-xs text-gray-500">Tipo de servicio</p>
                    <p class="font-medium">{{ ucfirst(str_replace('_', ' ', $ticket->service_type)) }}</p>
                </div>

                {{-- Prioridad --}}
                <div class="bg-gray-50 p-3 rounded-lg">
                    <p class="text-xs text-gray-500">Prioridad</p>
                    @if($ticket->priority)
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium
                            @switch($ticket->priority)
                                @case('P1') bg-red-100 text-red-700 @break
                                @case('P2') bg-orange-100 text-orange-700 @break
                                @case('P3') bg-blue-100 text-blue-700 @break
                                @case('P4') bg-gray-100 text-gray-600 @break
                            @endswitch">
                            {{ $ticket->priority }} -
                            @php
                                $priorityLabels = ['P1' => 'Crítico', 'P2' => 'Alta', 'P3' => 'Media', 'P4' => 'Baja'];
                            @endphp
                            {{ $priorityLabels[$ticket->priority] ?? $ticket->priority }}
                        </span>
                    @else
                        <span class="text-gray-400">—</span>
                    @endif
                </div>

                {{-- Origen --}}
                <div class="bg-gray-50 p-3 rounded-lg">
                    <p class="text-xs text-gray-500">Origen</p>
                    <p class="font-medium">{{ $ticket->origin ?? 'N/A' }}</p>
                </div>

                {{-- Requiere NOC --}}
                <div class="bg-gray-50 p-3 rounded-lg">
                    <p class="text-xs text-gray-500">Requiere NOC</p>
                    @if($ticket->requires_noc)
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">
                            Sí
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-gray-100 text-gray-600 rounded-full text-xs font-medium">
                            No
                        </span>
                    @endif
                </div>

                {{-- Descripción --}}
                <div class="bg-gray-50 p-3 rounded-lg">
                    <p class="text-xs text-gray-500">Descripción del problema</p>
                    <p class="text-sm whitespace-pre-wrap">{{ $ticket->description }}</p>
                </div>

                {{-- Notas adicionales --}}
                @if($ticket->notes)
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <p class="text-xs text-gray-500">Notas adicionales</p>
                        <p class="text-sm">{{ $ticket->notes }}</p>
                    </div>
                @endif

                {{-- Acciones (variables según el contexto) --}}
                <div class="flex justify-end gap-2 pt-3">
                    @if($showNocButton ?? false)
                        <button wire:click="goToNocPanel"
                            class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700">
                            Ir al panel del NOC
                        </button>
                    @endif
                    @if(($showCreateOtButton ?? true) && auth()->user()->can('create work_orders'))
                        <button wire:click="promptCreateWorkOrder({{ $ticket->id }})"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">
                            Crear OT
                        </button>
                    @endif
                    <button wire:click="closeModal"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-300">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
@endif