{{-- resources/views/livewire/mobile/work-order-list.blade.php --}}
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        {{-- Header --}}
        <div class="px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                    <span class="material-symbols-outlined text-blue-600">engineering</span>
                    Mis Órdenes de Trabajo
                </h1>
                <p class="text-sm text-gray-500 mt-1">
                    {{ $orders->total() }} órdenes asignadas
                </p>
            </div>
            <a href="{{ route('mobile.work-orders.map') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-green-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-green-700 transition">
                <span class="material-symbols-outlined text-base">map</span>
                Ver en mapa
            </a>
        </div>

        <div class="p-6 space-y-5">
            {{-- Filtros --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                    <input type="text" wire:model.live.debounce.300ms="search"
                        placeholder="Buscar por cliente o dirección..."
                        class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                </div>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">filter_alt</span>
                    <select wire:model.live="statusFilter"
                        class="w-full pl-9 pr-8 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm appearance-none">
                        <option value="pending,in_progress,paused">Pendientes / En progreso / Pausadas</option>
                        <option value="pending">Solo pendientes</option>
                        <option value="in_progress">Solo en progreso</option>
                        <option value="paused">Solo pausadas</option>
                        <option value="completed">Completadas</option>
                        <option value="cancelled">Canceladas</option>
                    </select>
                    <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
                </div>
            </div>

            {{-- Listado de OT --}}
            <div class="space-y-4">
                @forelse($orders as $order)
                    <a href="{{ route('mobile.work-orders.show', $order->id) }}" 
                       class="block bg-white rounded-xl border border-gray-200/80 shadow-sm hover:shadow-md hover:border-blue-200 transition-all duration-200 group">
                        <div class="p-5">
                            {{-- Línea superior: código, prioridad, estado --}}
                            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                                <div class="flex items-center gap-2 flex-wrap">
                                    {{-- Código OT --}}
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-50 text-blue-700 rounded-full text-xs font-semibold font-mono tracking-wide group-hover:bg-blue-100 transition">
                                        <span class="material-symbols-outlined text-sm">tag</span>
                                        {{ $order->code ?? 'OT-'.$order->id }}
                                    </span>

                                    {{-- Fecha programada --}}
                                    <span class="text-xs text-gray-500 flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm">calendar_today</span>
                                        {{ $order->scheduled_date?->format('d/m/Y') ?? 'Sin fecha' }}
                                    </span>

                                    {{-- Prioridad --}}
                                    @if($order->ticket?->priority)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold
                                            @switch($order->ticket->priority)
                                                @case('P1') bg-red-50 text-red-700 @break
                                                @case('P2') bg-orange-50 text-orange-700 @break
                                                @case('P3') bg-blue-50 text-blue-700 @break
                                                @case('P4') bg-gray-100 text-gray-600 @break
                                            @endswitch">
                                            <span class="material-symbols-outlined text-sm">flag</span>
                                            {{ $order->ticket->priority }}
                                        </span>
                                    @endif
                                </div>

                                {{-- Estado --}}
                                <div>
                                    @if($order->status == 'pending')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-yellow-50 text-yellow-700 rounded-full text-xs font-medium">
                                            <span class="material-symbols-outlined text-sm">schedule</span>
                                            Pendiente
                                        </span>
                                    @elseif($order->status == 'in_progress')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-50 text-blue-700 rounded-full text-xs font-medium">
                                            <span class="material-symbols-outlined text-sm">autorenew</span>
                                            En progreso
                                        </span>
                                    @elseif($order->status == 'paused')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-medium">
                                            <span class="material-symbols-outlined text-sm">pause_circle</span>
                                            Pausada
                                        </span>
                                    @elseif($order->status == 'completed')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-green-50 text-green-700 rounded-full text-xs font-medium">
                                            <span class="material-symbols-outlined text-sm">check_circle</span>
                                            Completada
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-red-50 text-red-700 rounded-full text-xs font-medium">
                                            <span class="material-symbols-outlined text-sm">cancel</span>
                                            Cancelada
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Datos del cliente --}}
                            <div class="space-y-2">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
                                        <span class="material-symbols-outlined text-gray-500 text-sm">person</span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-800">
                                            {{ $order->client->name ?? 'Cliente no especificado' }}
                                        </p>
                                        @if($order->client->phone)
                                            <p class="text-xs text-gray-500">{{ $order->client->phone }}</p>
                                        @endif
                                    </div>
                                </div>

                                {{-- Dirección --}}
                                <div class="flex items-start gap-2 ml-10">
                                    <span class="material-symbols-outlined text-gray-400 text-sm mt-0.5">location_on</span>
                                    <p class="text-xs text-gray-500 leading-relaxed">
                                        {{ $order->client->address ?? 'Sin dirección' }}
                                        @if($order->client->installation_address)
                                            <br>
                                            <span class="text-gray-400">Instalación:</span> {{ $order->client->installation_address }}
                                        @endif
                                    </p>
                                </div>

                                {{-- Notas (solo si existen) --}}
                                @if($order->notes)
                                    <div class="flex items-start gap-2 ml-10">
                                        <span class="material-symbols-outlined text-gray-400 text-sm mt-0.5">sticky_note_2</span>
                                        <p class="text-xs text-gray-500 italic leading-relaxed line-clamp-2">
                                            "{{ $order->notes }}"
                                        </p>
                                    </div>
                                @endif
                            </div>

                            {{-- Pie: indicador de requisición y acción --}}
                            <div class="flex items-center justify-between mt-4 pt-3 border-t border-gray-100">
                                <div class="flex items-center gap-2">
                                    @if($order->requisitions()->where('status', 'open')->exists())
                                        <span class="inline-flex items-center gap-1 text-xs text-green-600">
                                            <span class="material-symbols-outlined text-sm">inventory_2</span>
                                            Material asignado
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-xs text-gray-400">
                                            <span class="material-symbols-outlined text-sm">inventory_2</span>
                                            Sin material
                                        </span>
                                    @endif
                                </div>
                                <span class="inline-flex items-center gap-1 text-xs text-blue-600 font-medium group-hover:text-blue-700 transition">
                                    Ver detalle
                                    <span class="material-symbols-outlined text-sm group-hover:translate-x-0.5 transition-transform">arrow_forward</span>
                                </span>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="bg-gray-50/50 rounded-xl border border-dashed border-gray-300 py-16 text-center">
                        <span class="material-symbols-outlined text-gray-300 text-6xl mb-4">inventory_2</span>
                        <p class="text-gray-500 font-medium">No tienes órdenes de trabajo asignadas</p>
                        <p class="text-sm text-gray-400 mt-1">Las órdenes que te asignen aparecerán aquí</p>
                    </div>
                @endforelse
            </div>

            {{-- Paginación --}}
            @if($orders->hasPages())
                <div class="mt-6">{{ $orders->links() }}</div>
            @endif

            {{-- Mensajes de sesión --}}
            @if(session('message'))
                <div class="flex items-center gap-2 text-sm text-green-700 bg-green-50 px-4 py-3 rounded-lg border border-green-200">
                    <span class="material-symbols-outlined text-green-600">check_circle</span>
                    {{ session('message') }}
                </div>
            @endif
            @if(session('error'))
                <div class="flex items-center gap-2 text-sm text-red-700 bg-red-50 px-4 py-3 rounded-lg border border-red-200">
                    <span class="material-symbols-outlined text-red-600">error</span>
                    {{ session('error') }}
                </div>
            @endif
        </div>
    </div>
</div>