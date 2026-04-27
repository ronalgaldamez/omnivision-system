<div class="max-w-2xl mx-auto">
    <!-- Tarjeta principal -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <!-- Encabezado con fondo sutil -->
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                    <span class="material-symbols-outlined text-gray-500">work</span>
                    Orden de Trabajo #{{ $workOrder->id }}
                </h1>
                <p class="text-sm text-gray-500 mt-1">Detalle de la orden asignada</p>
            </div>
            <a href="{{ route('mobile.work-orders.list') }}"
                class="inline-flex items-center gap-1.5 text-sm text-blue-600 hover:text-blue-800 transition">
                <span class="material-symbols-outlined text-base">arrow_back</span>
                Volver
            </a>
        </div>

        <!-- Contenido -->
        <div class="p-6 space-y-5">
            <!-- Datos del cliente -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100">
                    <span class="material-symbols-outlined text-gray-400">person</span>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Cliente</p>
                        <p class="text-gray-800 font-medium">{{ $workOrder->client->name ?? 'Cliente no especificado' }}</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100">
                    <span class="material-symbols-outlined text-gray-400">call</span>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Teléfono</p>
                        <p class="text-gray-700">{{ $workOrder->client->phone ?? '—' }}</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100 sm:col-span-2">
                    <span class="material-symbols-outlined text-gray-400">location_on</span>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Dirección</p>
                        <p class="text-gray-700">{{ $workOrder->client->address ?? '—' }}</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100">
                    <span class="material-symbols-outlined text-gray-400">calendar_month</span>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Fecha programada</p>
                        <p class="text-gray-700">{{ $workOrder->scheduled_date?->format('d/m/Y') ?? '—' }}</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100">
                    <span class="material-symbols-outlined text-gray-400">flag</span>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Estado</p>
                        @if($workOrder->status == 'pending')
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-yellow-50 text-yellow-700 rounded-full text-xs font-medium">
                                <span class="material-symbols-outlined text-sm">schedule</span>
                                Pendiente
                            </span>
                        @elseif($workOrder->status == 'in_progress')
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-blue-50 text-blue-700 rounded-full text-xs font-medium">
                                <span class="material-symbols-outlined text-sm">autorenew</span>
                                En progreso
                            </span>
                        @elseif($workOrder->status == 'completed')
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-green-50 text-green-700 rounded-full text-xs font-medium">
                                <span class="material-symbols-outlined text-sm">check_circle</span>
                                Completada
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-red-50 text-red-700 rounded-full text-xs font-medium">
                                <span class="material-symbols-outlined text-sm">cancel</span>
                                Cancelada
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Notas -->
            <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100">
                <span class="material-symbols-outlined text-gray-400">sticky_note_2</span>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Notas</p>
                    <p class="text-gray-700">{{ $workOrder->notes ?? '—' }}</p>
                </div>
            </div>

            <!-- Productos sugeridos -->
            @if($workOrder->products->count())
                <div class="border-t border-gray-200 pt-5">
                    <h2 class="text-md font-semibold text-gray-800 flex items-center gap-2 mb-3">
                        <span class="material-symbols-outlined text-gray-500">inventory_2</span>
                        Productos sugeridos / asignados
                    </h2>
                    <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200">
                                    <th class="px-4 py-3 text-left text-gray-600 font-medium">
                                        <div class="flex items-center gap-1.5">
                                            <span class="material-symbols-outlined text-gray-400 text-base">inventory_2</span>
                                            Producto
                                        </div>
                                    </th>
                                    <th class="px-4 py-3 text-center text-gray-600 font-medium">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <span class="material-symbols-outlined text-gray-400 text-base">numbers</span>
                                            Cantidad
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($workOrder->products as $item)
                                    <tr class="hover:bg-gray-50/80 transition">
                                        <td class="px-4 py-3 text-gray-800">{{ $item->product->name }}</td>
                                        <td class="px-4 py-3 text-center font-mono text-gray-800">{{ $item->quantity }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Enlace a Google Maps -->
            @if($workOrder->latitude && $workOrder->longitude)
                <a href="https://www.google.com/maps?q={{ $workOrder->latitude }},{{ $workOrder->longitude }}"
                    target="_blank"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-green-700 transition">
                    <span class="material-symbols-outlined text-base">map</span>
                    Ver en Google Maps
                </a>
            @endif

            <!-- Botones de acción -->
            @if(!in_array($workOrder->status, ['completed', 'cancelled']))
                <div class="border-t border-gray-200 pt-5 space-y-3">
                    <a href="{{ route('mobile.technician.requests.create', ['work_order_id' => $workOrder->id]) }}"
                        class="block w-full text-center px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-blue-700 transition">
                        Solicitar materiales para esta OT
                    </a>
                    @if(auth()->user()->can('complete work_orders'))
                        <button wire:click="completeWorkOrder"
                            onclick="return confirm('¿Marcar esta orden como completada? Esta acción no se puede deshacer.')"
                            class="block w-full text-center px-5 py-2.5 bg-green-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-green-700 transition">
                            Completar trabajo
                        </button>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>