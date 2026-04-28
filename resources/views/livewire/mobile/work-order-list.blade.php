<div class="max-w-4xl mx-auto">
    <!-- Tarjeta principal -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <!-- Encabezado con fondo sutil -->
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                    <span class="material-symbols-outlined text-gray-500">work</span>
                    Mis Órdenes de Trabajo
                </h1>
                <p class="text-sm text-gray-500 mt-1">Órdenes asignadas a tu perfil</p>
            </div>
            <a href="{{ route('mobile.work-orders.map') }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-1 transition">
                <span class="material-symbols-outlined text-base">map</span>
                Ver en mapa
            </a>
        </div>

        <!-- Contenido -->
        <div class="p-6 space-y-5">
            <!-- Filtros -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="relative">
                    <span
                        class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                    <input type="text" wire:model.live.debounce.300ms="search"
                        placeholder="Buscar por cliente o dirección..."
                        class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                </div>
                <div class="relative">
                    <span
                        class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">filter_alt</span>
                    <select wire:model.live="statusFilter"
                        class="w-full pl-9 pr-8 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm appearance-none">
                        <option value="pending,in_progress">Pendientes / En progreso</option>
                        <option value="pending">Solo pendientes</option>
                        <option value="in_progress">Solo en progreso</option>
                        <option value="completed">Completadas</option>
                        <option value="cancelled">Canceladas</option>
                    </select>
                    <span
                        class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
                </div>
            </div>

            <!-- Listado de OT (tarjetas) -->
            <div class="space-y-4">
                @forelse($orders as $order)
                    <div
                        class="bg-white rounded-xl border border-gray-200/80 shadow-sm hover:shadow-md transition overflow-hidden">
                        <div class="p-5">
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-gray-100 text-gray-700 rounded-full text-xs font-medium">
                                        <span class="material-symbols-outlined text-sm">tag</span>
                                        #{{ $order->id }}
                                    </span>
                                    <span class="text-xs text-gray-400">
                                        {{ $order->scheduled_date?->format('d/m/Y') ?? 'Sin fecha' }}
                                    </span>
                                </div>
                                <div>
                                    @if($order->status == 'pending')
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-yellow-50 text-yellow-700 rounded-full text-xs font-medium">
                                            <span class="material-symbols-outlined text-sm">schedule</span>
                                            Pendiente
                                        </span>
                                    @elseif($order->status == 'in_progress')
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-blue-50 text-blue-700 rounded-full text-xs font-medium">
                                            <span class="material-symbols-outlined text-sm">autorenew</span>
                                            En progreso
                                        </span>
                                    @elseif($order->status == 'completed')
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-green-50 text-green-700 rounded-full text-xs font-medium">
                                            <span class="material-symbols-outlined text-sm">check_circle</span>
                                            Completada
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-red-50 text-red-700 rounded-full text-xs font-medium">
                                            <span class="material-symbols-outlined text-sm">cancel</span>
                                            Cancelada
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Datos del cliente -->
                            <div class="text-sm font-medium text-gray-800 mb-1 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-sm">person</span>
                                {{ $order->client->name ?? 'Cliente no especificado' }}
                            </div>
                            <div class="text-xs text-gray-500 mb-2 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-sm">location_on</span>
                                {{ $order->client->address ?? 'Sin dirección' }}
                            </div>

                            @if($order->notes)
                                <div class="text-xs text-gray-500 mb-3 flex items-start gap-1.5 italic">
                                    <span class="material-symbols-outlined text-gray-400 text-sm">sticky_note_2</span>
                                    "{{ Str::limit($order->notes, 60) }}"
                                </div>
                            @endif

                            <div class="flex justify-end pt-2 border-t border-gray-100">
                                <a href="{{ route('mobile.work-orders.show', $order->id) }}"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-blue-600 text-xs font-medium rounded-lg hover:bg-blue-50 transition">
                                    <span class="material-symbols-outlined text-base">visibility</span>
                                    Ver detalle
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-gray-50/50 rounded-xl border border-dashed border-gray-300 py-12 text-center">
                        <span class="material-symbols-outlined text-gray-300 text-5xl mb-3">inbox</span>
                        <p class="text-gray-500">No tienes órdenes de trabajo asignadas</p>
                        <p class="text-sm text-gray-400 mt-1">Las órdenes que te asignen aparecerán aquí</p>
                    </div>
                @endforelse
            </div>

            <!-- Paginación -->
            @if($orders->hasPages())
                <div class="mt-5">
                    {{ $orders->links() }}
                </div>
            @endif

            <!-- Mensajes de sesión -->
            @if(session('message'))
                <div
                    class="flex items-center gap-2 text-sm text-green-700 bg-green-50 px-4 py-3 rounded-lg border border-green-200">
                    <span class="material-symbols-outlined text-green-600">check_circle</span>
                    {{ session('message') }}
                </div>
            @endif
            @if(session('error'))
                <div
                    class="flex items-center gap-2 text-sm text-red-700 bg-red-50 px-4 py-3 rounded-lg border border-red-200">
                    <span class="material-symbols-outlined text-red-600">error</span>
                    {{ session('error') }}
                </div>
            @endif
        </div>
    </div>
</div>