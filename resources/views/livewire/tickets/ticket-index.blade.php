<div class="max-w-7xl mx-auto">
    <!-- Tarjeta principal -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <!-- Encabezado -->
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                    <span class="material-symbols-outlined text-gray-500">confirmation_number</span>
                    Tickets
                </h1>
                <p class="text-sm text-gray-500 mt-1">Listado de solicitudes de servicio</p>
            </div>
            @can('create tickets')
                <a href="{{ route('tickets.create') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-blue-700 transition">
                    <span class="material-symbols-outlined text-base">add_circle</span>
                    Nuevo Ticket
                </a>
            @endcan
        </div>

        <!-- Contenido -->
        <div class="p-6 space-y-5">
            <!-- Filtros -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="relative">
                    <span
                        class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                    <input type="text" wire:model.live="search"
                        placeholder="Buscar por cliente, descripción o código de asistencia..."
                        class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                </div>
                <div class="relative">
                    <span
                        class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">filter_alt</span>
                    <select wire:model.live="statusFilter"
                        class="w-full pl-9 pr-8 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm appearance-none">
                        <option value="">Todos los estados</option>
                        <option value="pending">Pendiente</option>
                        <option value="in_progress">En progreso</option>
                        <option value="resolved">Resuelto</option>
                        <option value="closed">Cerrado</option>
                    </select>
                    <span
                        class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
                </div>
            </div>

            <!-- Tabla -->
            <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-4 py-3 text-left">Código</th>
                            <th class="px-4 py-3 text-left">ID</th>
                            <th class="px-4 py-3 text-left">Cliente</th>
                            <th class="px-4 py-3 text-left">Tipo</th>
                            <th class="px-4 py-3 text-left">Descripción</th>
                            <th class="px-4 py-3 text-center">NOC</th>
                            <th class="px-4 py-3 text-left">Estado</th>
                            <th class="px-4 py-3 text-left">Creado</th>
                            <th class="px-4 py-3 text-center">OT</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($tickets as $ticket)
                            @php
                                $canViewWorkOrders = auth()->user()->can('view work_orders');
                                $canCreateWorkOrders = auth()->user()->can('create work_orders');
                            @endphp
                            <tr class="hover:bg-gray-50/80 transition">
                                <td class="px-4 py-3 font-mono text-xs">{{ $ticket->ticket_code ?? '—' }}</td>
                                <td class="px-4 py-3">#{{ $ticket->id }}</td>
                                <td class="px-4 py-3">{{ $ticket->client->name }}</td>
                                <td class="px-4 py-3">{{ $ticket->service_type }}</td>
                                <td class="px-4 py-3 max-w-[200px] truncate" title="{{ $ticket->description }}">
                                    {{ Str::limit($ticket->description, 50) }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($ticket->requires_noc)
                                        <span class="bg-blue-50 text-blue-700 px-2 py-0.5 rounded-full text-xs">Sí</span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if($ticket->status == 'pending')
                                        <span
                                            class="bg-yellow-50 text-yellow-700 px-2 py-0.5 rounded-full text-xs">Pendiente</span>
                                    @elseif($ticket->status == 'in_progress')
                                        <span class="bg-blue-50 text-blue-700 px-2 py-0.5 rounded-full text-xs">En
                                            progreso</span>
                                    @elseif($ticket->status == 'resolved')
                                        <span
                                            class="bg-green-50 text-green-700 px-2 py-0.5 rounded-full text-xs">Resuelto</span>
                                    @elseif($ticket->status == 'closed')
                                        <span class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded-full text-xs">Cerrado</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">{{ $ticket->created_at->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-center">
                                    @if($ticket->workOrder && $canViewWorkOrders)
                                        <a href="{{ route('work-orders.show', $ticket->workOrder->id) }}"
                                            class="text-blue-600 hover:underline">Ver OT</a>
                                    @elseif(!$ticket->workOrder && $ticket->requires_noc && $canCreateWorkOrders)
                                        <button wire:click="viewDetail({{ $ticket->id }})"
                                            class="text-purple-600 hover:text-purple-800 font-medium text-sm">
                                            Ver Ticket
                                        </button>
                                    @elseif(!$ticket->workOrder && $ticket->requires_noc && !$canCreateWorkOrders)
                                        <button wire:click="viewDetail({{ $ticket->id }})"
                                            class="text-purple-600 hover:text-purple-800 font-medium text-sm">
                                            Ver Ticket
                                        </button>
                                    @elseif(!$ticket->workOrder && !$ticket->requires_noc)
                                        <button wire:click="viewDetail({{ $ticket->id }})"
                                            class="text-purple-600 hover:text-purple-800 font-medium text-sm">
                                            Ver Ticket
                                        </button>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-12 text-center bg-gray-50/50">
                                    <span class="material-symbols-outlined text-gray-300 text-4xl mb-2">inbox</span>
                                    <p class="text-gray-500">No hay tickets registrados</p>
                                    <p class="text-sm text-gray-400 mt-1">Haz clic en "Nuevo Ticket" para crear uno</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            @if($tickets->hasPages())
                <div class="mt-5">{{ $tickets->links() }}</div>
            @endif

            <!-- Mensajes -->
            @if(session('message'))
                <div
                    class="flex items-center gap-2 text-green-700 bg-green-50 px-4 py-3 rounded-lg border border-green-200">
                    <span class="material-symbols-outlined">check_circle</span>
                    {{ session('message') }}
                </div>
            @endif
            @if(session('error'))
                <div class="flex items-center gap-2 text-red-700 bg-red-50 px-4 py-3 rounded-lg border border-red-200">
                    <span class="material-symbols-outlined">error</span>
                    {{ session('error') }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modal de detalle -->
    @if($showDetailModal && $selectedTicket)
        <div x-data="{ open: true }" x-show="open" x-cloak
            class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-xl bg-white">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Detalle del Ticket #{{ $selectedTicket->id }}</h3>
                    <button wire:click="closeModal" class="text-gray-500 hover:text-gray-700">&times;</button>
                </div>

                <div class="space-y-3">
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
                        <p class="font-medium">{{ ucfirst($selectedTicket->service_type) }}</p>
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

                    <div class="flex justify-end gap-2 pt-3">
                        @if(auth()->user()->can('access noc panel') || auth()->user()->can('update tickets'))
                            <button wire:click="resolveRemote({{ $selectedTicket->id }})"
                                onclick="return confirm('¿Confirmas resolución remota?')"
                                class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700">
                                Resolver remotamente
                            </button>
                        @endif
                        @if(auth()->user()->can('create work_orders'))
                            <button wire:click="createWorkOrder({{ $selectedTicket->id }})"
                                onclick="return confirm('¿Crear orden de trabajo?')"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">
                                Crear OT
                            </button>
                        @endif
                        <button wire:click="closeModal"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-300">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Toast -->
    <div x-data="{ toast: null, toastType: null, toastMessage: '' }"
        x-on:show-toast.window="toast = true; toastType = $event.detail.type; toastMessage = $event.detail.message; setTimeout(() => toast = false, 3500)"
        x-show="toast" x-cloak class="fixed bottom-5 right-5 z-50 transition-all duration-300"
        x-transition:enter="transform ease-out duration-300" x-transition:enter-start="translate-y-2 opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100" x-transition:leave="transform ease-in duration-200"
        x-transition:leave-start="translate-y-0 opacity-100" x-transition:leave-end="translate-y-2 opacity-0"
        style="display: none;">
        <div x-show="toastType === 'success'"
            class="bg-green-600 text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-3">
            <span class="material-symbols-outlined">check_circle</span>
            <span x-text="toastMessage" class="text-sm font-medium"></span>
        </div>
        <div x-show="toastType === 'error'"
            class="bg-red-600 text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-3">
            <span class="material-symbols-outlined">error</span>
            <span x-text="toastMessage" class="text-sm font-medium"></span>
        </div>
    </div>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</div>