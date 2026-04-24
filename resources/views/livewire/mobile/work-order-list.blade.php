<div class="max-w-lg mx-auto px-3 py-4">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-xl font-semibold text-gray-800">Mis Órdenes de Trabajo</h1>
        <a href="{{ route('mobile.work-orders.map') }}"
            class="bg-green-600 text-white px-3 py-2 rounded-lg text-sm flex items-center gap-1 shadow">
            <span class="material-symbols-outlined text-base">map</span> Ver en mapa
        </a>
    </div>

    <!-- Filtros -->
    <div class="mb-4 flex gap-2">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar por cliente o dirección..."
            class="flex-1 rounded-lg border-gray-300 text-sm">
        <select wire:model.live="statusFilter" class="rounded-lg border-gray-300 text-sm w-32">
            <option value="pending,in_progress">Pendientes / En progreso</option>
            <option value="pending">Solo pendientes</option>
            <option value="in_progress">Solo en progreso</option>
            <option value="completed">Completadas</option>
            <option value="cancelled">Canceladas</option>
        </select>
    </div>

    <!-- Listado de OT (tarjetas) -->
    <div class="space-y-3">
        @forelse($orders as $order)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <span class="text-xs text-gray-500">#{{ $order->id }}</span>
                        <span
                            class="text-xs text-gray-500 ml-2">{{ $order->scheduled_date?->format('d/m/Y') ?? 'Sin fecha' }}</span>
                    </div>
                    <div>
                        @if($order->status == 'pending')
                            <span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full">Pendiente</span>
                        @elseif($order->status == 'in_progress')
                            <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">En progreso</span>
                        @elseif($order->status == 'completed')
                            <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">Completada</span>
                        @else
                            <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full">Cancelada</span>
                        @endif
                    </div>
                </div>
                <!-- Datos del cliente desde la relación -->
                <div class="text-sm font-medium text-gray-800 mb-1">{{ $order->client->name ?? 'Cliente no especificado' }}
                </div>
                <div class="text-xs text-gray-500 mb-2">{{ $order->client->address ?? 'Sin dirección' }}</div>
                @if($order->notes)
                    <div class="text-xs text-gray-500 mb-2 italic">"{{ Str::limit($order->notes, 60) }}"</div>
                @endif
                <div class="flex justify-end mt-2">
                    <a href="{{ route('mobile.work-orders.show', $order->id) }}"
                        class="text-blue-600 text-sm flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">visibility</span> Ver detalle
                    </a>
                </div>
            </div>
        @empty
            <div class="text-center text-gray-400 py-8">No tienes órdenes de trabajo asignadas.</div>
        @endforelse
    </div>

    <div class="mt-4">{{ $orders->links() }}</div>
</div>