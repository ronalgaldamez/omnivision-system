<div class="max-w-7xl mx-auto">
    <!-- Tarjeta principal -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <!-- Encabezado con fondo sutil -->
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50">
            <div>
                <h1 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                    <span class="material-symbols-outlined text-gray-500">dns</span>
                    Panel NOC - Tickets pendientes
                </h1>
                <p class="text-sm text-gray-500 mt-1">Gestión de tickets que requieren intervención del NOC</p>
            </div>
        </div>

        <!-- Contenido -->
        <div class="p-6 space-y-5">
            @if(session('message'))
                <div
                    class="flex items-center gap-2 text-sm text-green-700 bg-green-50 px-4 py-3 rounded-lg border border-green-200">
                    <span class="material-symbols-outlined text-green-600">check_circle</span>
                    {{ session('message') }}
                </div>
            @endif

            <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">
                                <div class="flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">tag</span> ID
                                </div>
                            </th>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">
                                <div class="flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">person</span>
                                    Cliente
                                </div>
                            </th>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">
                                <div class="flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">handyman</span>
                                    Servicio
                                </div>
                            </th>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">
                                <div class="flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">description</span>
                                    Descripción
                                </div>
                            </th>
                            <th class="px-4 py-3 text-center text-gray-600 font-medium">
                                <div class="flex items-center justify-center gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">settings</span>
                                    Acciones
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($tickets as $ticket)
                            <tr class="hover:bg-gray-50/80 transition">
                                <td class="px-4 py-3 font-mono text-xs text-gray-700">#{{ $ticket->id }}</td>
                                <td class="px-4 py-3 text-gray-800">{{ $ticket->client->name ?? 'N/A' }}</td>
                                <td class="px-4 py-3">
                                    <span
                                        class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-gray-100 text-gray-700 rounded-full text-xs font-medium">
                                        {{ $ticket->service_type }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-600 max-w-xs truncate" title="{{ $ticket->description }}">
                                    {{ Str::limit($ticket->description, 100) }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button wire:click="viewDetail({{ $ticket->id }})"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-600 text-white text-xs font-medium rounded-lg shadow-sm hover:bg-gray-700 transition"
                                            title="Ver detalle completo">
                                            <span class="material-symbols-outlined text-base">visibility</span>
                                            Detalle
                                        </button>
                                        <button wire:click="resolveRemote({{ $ticket->id }})"
                                            onclick="return confirm('¿Confirmas resolución remota?')"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-600 text-white text-xs font-medium rounded-lg shadow-sm hover:bg-green-700 transition">
                                            <span class="material-symbols-outlined text-base">check_circle</span>
                                            Resolver
                                        </button>
                                        <button wire:click="createWorkOrder({{ $ticket->id }})"
                                            onclick="return confirm('¿Crear orden de trabajo?')"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-lg shadow-sm hover:bg-blue-700 transition">
                                            <span class="material-symbols-outlined text-base">engineering</span>
                                            Crear OT
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-12 text-center bg-gray-50/50">
                                    <span class="material-symbols-outlined text-gray-300 text-4xl mb-2">checklist</span>
                                    <p class="text-gray-500">No hay tickets pendientes</p>
                                    <p class="text-sm text-gray-400 mt-1">Todos los tickets NOC han sido procesados</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal de detalle del ticket -->
    @if($showDetailModal && $selectedTicket)
        <div x-data="{ open: true }" x-show="open" x-cloak
            class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-xl bg-white">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Detalle del Ticket #{{ $selectedTicket->id }}</h3>
                    <button wire:click="closeModal" class="text-gray-500 hover:text-gray-700">&times;</button>
                </div>

                <div class="space-y-3">
                    <!-- Cliente -->
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <p class="text-xs text-gray-500">Cliente</p>
                        <p class="font-medium">{{ $selectedTicket->client->name ?? 'N/A' }}</p>
                        <p class="text-sm">{{ $selectedTicket->client->phone ?? 'Sin teléfono' }}</p>
                        <p class="text-sm">{{ $selectedTicket->client->address ?? 'Sin dirección' }}</p>
                    </div>

                    <!-- Tipo de servicio -->
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <p class="text-xs text-gray-500">Tipo de servicio</p>
                        <p class="font-medium">{{ ucfirst($selectedTicket->service_type) }}</p>
                    </div>

                    <!-- Descripción completa -->
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <p class="text-xs text-gray-500">Descripción del problema</p>
                        <p class="text-sm whitespace-pre-wrap">{{ $selectedTicket->description }}</p>
                    </div>

                    <!-- Notas adicionales (si existen) -->
                    @if($selectedTicket->notes)
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-xs text-gray-500">Notas adicionales</p>
                            <p class="text-sm">{{ $selectedTicket->notes }}</p>
                        </div>
                    @endif

                    <!-- Acciones -->
                    <div class="flex justify-end gap-2 pt-3">
                        <button wire:click="resolveRemote({{ $selectedTicket->id }})"
                            onclick="return confirm('¿Confirmas resolución remota?')"
                            class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700">
                            Resolver remotamente
                        </button>
                        <button wire:click="createWorkOrder({{ $selectedTicket->id }})"
                            onclick="return confirm('¿Crear orden de trabajo?')"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">
                            Crear OT
                        </button>
                        <button wire:click="closeModal"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-300">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>