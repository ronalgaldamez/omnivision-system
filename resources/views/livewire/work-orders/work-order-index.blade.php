<div class="max-w-7xl mx-auto">
    <!-- Tarjeta principal -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <!-- Encabezado con fondo sutil -->
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                    <span class="material-symbols-outlined text-gray-500">engineering</span>
                    Órdenes de Trabajo
                </h1>
                <p class="text-sm text-gray-500 mt-1">Listado de órdenes asignadas a técnicos</p>
            </div>
            <a href="{{ route('work-orders.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 transition">
                <span class="material-symbols-outlined text-base">add_circle</span>
                Nueva Orden
            </a>
        </div>

        <!-- Contenido -->
        <div class="p-6 space-y-5">
            <!-- Filtros -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                    <input type="text" wire:model.live="search" placeholder="Buscar por cliente o técnico..."
                        class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                </div>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">filter_alt</span>
                    <select wire:model.live="statusFilter"
                        class="w-full pl-9 pr-8 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm appearance-none">
                        <option value="">Todos los estados</option>
                        <option value="pending">Pendiente</option>
                        <option value="in_progress">En progreso</option>
                        <option value="completed">Completada</option>
                        <option value="cancelled">Cancelada</option>
                    </select>
                    <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
                </div>
            </div>

            <!-- Tabla de órdenes -->
            <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">
                                <div class="flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">tag</span>
                                    ID
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
                                    <span class="material-symbols-outlined text-gray-400 text-base">engineering</span>
                                    Técnico
                                </div>
                            </th>
                            <th class="px-4 py-3 text-center text-gray-600 font-medium">
                                <div class="flex items-center justify-center gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">flag</span>
                                    Estado
                                </div>
                            </th>
                            <th class="px-4 py-3 text-center text-gray-600 font-medium">
                                <div class="flex items-center justify-center gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">calendar_month</span>
                                    Fecha programada
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
                        @forelse($orders as $order)
                            <tr class="hover:bg-gray-50/80 transition">
                                <td class="px-4 py-3 font-mono text-xs text-gray-700">#{{ $order->id }}</td>
                                <td class="px-4 py-3 text-gray-800">{{ $order->client->name ?? 'No especificado' }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ $order->technician?->name ?? 'No asignado' }}</td>
                                <td class="px-4 py-3 text-center">
                                    @if($order->status == 'pending')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-yellow-50 text-yellow-700 rounded-full text-xs font-medium">
                                            Pendiente
                                        </span>
                                    @elseif($order->status == 'in_progress')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-blue-50 text-blue-700 rounded-full text-xs font-medium">
                                            En progreso
                                        </span>
                                    @elseif($order->status == 'completed')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-green-50 text-green-700 rounded-full text-xs font-medium">
                                            Completada
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-red-50 text-red-700 rounded-full text-xs font-medium">
                                            Cancelada
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center text-gray-700">
                                    {{ $order->scheduled_date ? $order->scheduled_date->format('d/m/Y') : '—' }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <a href="{{ route('work-orders.show', $order->id) }}"
                                            class="p-1.5 text-green-600 hover:bg-green-50 rounded-lg transition" title="Ver">
                                            <span class="material-symbols-outlined text-lg">visibility</span>
                                        </a>
                                        <a href="{{ route('work-orders.edit', $order->id) }}"
                                            class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Editar">
                                            <span class="material-symbols-outlined text-lg">edit</span>
                                        </a>
                                        <button wire:click="delete({{ $order->id }})"
                                            onclick="confirm('¿Eliminar esta orden?') || event.stopImmediatePropagation()"
                                            class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition" title="Eliminar">
                                            <span class="material-symbols-outlined text-lg">delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center bg-gray-50/50">
                                    <span class="material-symbols-outlined text-gray-300 text-4xl mb-2">inbox</span>
                                    <p class="text-gray-500">No hay órdenes de trabajo registradas</p>
                                    <p class="text-sm text-gray-400 mt-1">Haz clic en "Nueva Orden" para crear una</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            @if($orders->hasPages())
                <div class="mt-5">
                    {{ $orders->links() }}
                </div>
            @endif

            <!-- Mensajes de sesión (estilo unificado) -->
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

    <!-- Toast genérico (por si se emite desde el componente) -->
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
        <div x-show="toastType === 'info'"
            class="bg-blue-600 text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-3">
            <span class="material-symbols-outlined">info</span>
            <span x-text="toastMessage" class="text-sm font-medium"></span>
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</div>