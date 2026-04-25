<div class="max-w-5xl mx-auto">
    <!-- Tarjeta principal -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <!-- Encabezado con fondo sutil -->
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                    <span class="material-symbols-outlined text-gray-500">work</span>
                    Orden #{{ $order->id }}
                </h1>
                <p class="text-sm text-gray-500 mt-1">Detalle de la orden de trabajo</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('work-orders.index') }}"
                    class="inline-flex items-center gap-1.5 text-sm text-blue-600 hover:text-blue-800 transition">
                    <span class="material-symbols-outlined text-base">arrow_back</span>
                    Volver
                </a>
                <a href="{{ route('work-orders.edit', $order->id) }}"
                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-blue-700 transition">
                    <span class="material-symbols-outlined text-base">edit</span>
                    Editar
                </a>
            </div>
        </div>

        <!-- Contenido -->
        <div class="p-6 space-y-6">
            <!-- Datos de la orden -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Cliente -->
                <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100">
                    <span class="material-symbols-outlined text-gray-400">person</span>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Cliente</p>
                        <p class="text-gray-800">{{ $order->client->name ?? 'N/A' }}</p>
                    </div>
                </div>
                <!-- Técnico -->
                <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100">
                    <span class="material-symbols-outlined text-gray-400">handyman</span>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Técnico</p>
                        <p class="text-gray-800">{{ $order->technician->name ?? 'N/A' }}</p>
                    </div>
                </div>
                <!-- Teléfono -->
                <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100">
                    <span class="material-symbols-outlined text-gray-400">call</span>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Teléfono</p>
                        <p class="text-gray-700">{{ $order->client->phone ?? '-' }}</p>
                    </div>
                </div>
                <!-- Estado -->
                <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100">
                    <span class="material-symbols-outlined text-gray-400">flag</span>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Estado</p>
                        @php
                            $statusColors = [
                                'pending' => 'bg-yellow-50 text-yellow-700',
                                'in_progress' => 'bg-blue-50 text-blue-700',
                                'completed' => 'bg-green-50 text-green-700',
                                'cancelled' => 'bg-red-50 text-red-700',
                            ];
                            $statusColor = $statusColors[$order->status] ?? 'bg-gray-50 text-gray-700';
                        @endphp
                        <span
                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                </div>
                <!-- Dirección -->
                <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100">
                    <span class="material-symbols-outlined text-gray-400">location_on</span>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Dirección</p>
                        <p class="text-gray-700">{{ $order->client->address ?? '-' }}</p>
                    </div>
                </div>
                <!-- Coordenadas -->
                <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100">
                    <span class="material-symbols-outlined text-gray-400">pin_drop</span>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Coordenadas</p>
                        <p class="text-gray-700 font-mono text-xs">{{ $order->latitude }}, {{ $order->longitude }}</p>
                    </div>
                </div>
                <!-- Fecha programada -->
                <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100">
                    <span class="material-symbols-outlined text-gray-400">calendar_month</span>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Fecha programada</p>
                        <p class="text-gray-700">{{ $order->scheduled_date?->format('d/m/Y') ?? '-' }}</p>
                    </div>
                </div>
                <!-- Notas -->
                <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100 md:col-span-2">
                    <span class="material-symbols-outlined text-gray-400">sticky_note_2</span>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Notas</p>
                        <p class="text-gray-700">{{ $order->notes ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- Productos -->
            <div class="border-t border-gray-200 pt-5">
                <h2 class="text-md font-semibold text-gray-800 flex items-center gap-2 mb-3">
                    <span class="material-symbols-outlined text-gray-500">inventory_2</span>
                    Productos
                </h2>
                <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">
                                    <div class="flex items-center gap-1.5">
                                        <span
                                            class="material-symbols-outlined text-gray-400 text-base">inventory_2</span>
                                        Producto
                                    </div>
                                </th>
                                <th class="px-4 py-3 text-center text-gray-600 font-medium">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <span class="material-symbols-outlined text-gray-400 text-base">numbers</span>
                                        Cantidad
                                    </div>
                                </th>
                                <th class="px-4 py-3 text-right text-gray-600 font-medium">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <span
                                            class="material-symbols-outlined text-gray-400 text-base">attach_money</span>
                                        Costo unitario
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($order->products as $item)
                                <tr class="hover:bg-gray-50/80 transition">
                                    <td class="px-4 py-3 text-gray-800">{{ $item->product->name }}</td>
                                    <td class="px-4 py-3 text-center text-gray-700">{{ $item->quantity }}</td>
                                    <td class="px-4 py-3 text-right text-gray-700">
                                        ${{ number_format($item->unit_cost_at_time ?? 0, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-10 text-center bg-gray-50/50">
                                        <span class="material-symbols-outlined text-gray-300 text-3xl mb-2">inbox</span>
                                        <p class="text-gray-500">Sin productos asignados</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Acción de completar -->
            @if(!in_array($order->status, ['completed', 'cancelled']) && auth()->user()->can('complete work_orders') && $order->technician_id)
                <div class="flex justify-end pt-4 border-t border-gray-200">
                    <button wire:click="completeWorkOrder"
                        onclick="return confirm('¿Marcar esta orden como completada? Esta acción no se puede deshacer.')"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-green-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition">
                        <span class="material-symbols-outlined text-base">check_circle</span>
                        Completar trabajo
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>