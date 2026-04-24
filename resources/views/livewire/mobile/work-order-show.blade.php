<div class="max-w-lg mx-auto px-3 py-4">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <div class="flex justify-between items-start mb-3">
            <h1 class="text-lg font-semibold">Orden de Trabajo #{{ $workOrder->id }}</h1>
            <a href="{{ route('mobile.work-orders.list') }}" class="text-blue-600 text-sm">← Volver</a>
        </div>

        <div class="space-y-2 text-sm">
            <div class="border-b pb-2">
                <span class="text-gray-500">Cliente:</span>
                <p class="font-medium">{{ $workOrder->client->name ?? 'Cliente no especificado' }}</p>
            </div>
            <div class="border-b pb-2">
                <span class="text-gray-500">Teléfono:</span>
                <p>{{ $workOrder->client->phone ?? '—' }}</p>
            </div>
            <div class="border-b pb-2">
                <span class="text-gray-500">Dirección:</span>
                <p>{{ $workOrder->client->address ?? '—' }}</p>
            </div>
            <div class="border-b pb-2">
                <span class="text-gray-500">Fecha programada:</span>
                <p>{{ $workOrder->scheduled_date?->format('d/m/Y') ?? '—' }}</p>
            </div>
            <div class="border-b pb-2">
                <span class="text-gray-500">Estado:</span>
                <p>
                    @if($workOrder->status == 'pending')
                        <span class="text-yellow-600">Pendiente</span>
                    @elseif($workOrder->status == 'in_progress')
                        <span class="text-blue-600">En progreso</span>
                    @elseif($workOrder->status == 'completed')
                        <span class="text-green-600">Completada</span>
                    @else
                        <span class="text-red-600">Cancelada</span>
                    @endif
                </p>
            </div>
            <div class="border-b pb-2">
                <span class="text-gray-500">Notas:</span>
                <p>{{ $workOrder->notes ?? '—' }}</p>
            </div>
        </div>

        @if($workOrder->products->count())
            <div class="mt-4">
                <h2 class="font-medium text-sm mb-2">Productos sugeridos / asignados</h2>
                <div class="space-y-1 text-sm">
                    @foreach($workOrder->products as $item)
                        <div class="flex justify-between border-b py-1">
                            <span>{{ $item->product->name }}</span>
                            <span class="font-mono">{{ $item->quantity }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if($workOrder->latitude && $workOrder->longitude)
            <div class="mt-4">
                <a href="https://www.google.com/maps?q={{ $workOrder->latitude }},{{ $workOrder->longitude }}"
                    target="_blank" class="text-blue-600 text-sm flex items-center gap-1">
                    <span class="material-symbols-outlined text-base">map</span> Ver en mapa
                </a>
            </div>
        @endif

        <!-- Botón para solicitar materiales (si no está completada) -->
        @if(!in_array($workOrder->status, ['completed', 'cancelled']))
            <div class="mt-6 pt-3 border-t">
                <a href="{{ route('mobile.technician.requests.create', ['work_order_id' => $workOrder->id]) }}"
                    class="block w-full text-center bg-blue-600 text-white py-2 rounded-lg text-sm">
                    Solicitar materiales para esta OT
                </a>
            </div>
        @endif

        <!-- Botón para completar trabajo (solo si no está completada y el usuario tiene permiso) -->
        @if(!in_array($workOrder->status, ['completed', 'cancelled']) && auth()->user()->can('complete work_orders'))
            <div class="mt-3">
                <button wire:click="completeWorkOrder"
                    onclick="confirm('¿Marcar esta orden como completada? Esta acción no se puede deshacer.') || event.stopImmediatePropagation()"
                    class="block w-full text-center bg-green-600 text-white py-2 rounded-lg text-sm">
                    Completar trabajo
                </button>
            </div>
        @endif
    </div>
</div>