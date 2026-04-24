<div class="max-w-4xl mx-auto bg-white p-6 rounded shadow">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-xl font-bold">Orden #{{ $workOrder->id }}</h1>
        <div class="space-x-2">
            <a href="{{ route('work-orders.edit', $workOrder->id) }}" class="text-blue-600">Editar</a>
            <a href="{{ route('work-orders.index') }}" class="text-gray-600">← Volver</a>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4 mb-4">
        <!-- Datos del cliente desde la relación -->
        <div><strong>Cliente:</strong> {{ $workOrder->client->name ?? 'N/A' }}</div>
        <div><strong>Técnico:</strong> {{ $workOrder->technician->name ?? 'N/A' }}</div>
        <div><strong>Teléfono:</strong> {{ $workOrder->client->phone ?? '-' }}</div>
        <div><strong>Estado:</strong> {{ ucfirst($workOrder->status) }}</div>
        <div><strong>Dirección:</strong> {{ $workOrder->client->address ?? '-' }}</div>
        <div><strong>Coordenadas:</strong> {{ $workOrder->latitude }}, {{ $workOrder->longitude }}</div>
        <div><strong>Fecha programada:</strong> {{ $workOrder->scheduled_date?->format('d/m/Y') ?? '-' }}</div>
        <div><strong>Notas:</strong> {{ $workOrder->notes ?? '-' }}</div>
    </div>

    <h2 class="font-bold mt-4 mb-2">Productos</h2>
    <table class="min-w-full border">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-3 py-2 text-left">Producto</th>
                <th class="px-3 py-2 text-center">Cantidad</th>
                <th class="px-3 py-2 text-right">Costo unitario</th>
            </tr>
        </thead>
        <tbody>
            @foreach($workOrder->products as $item)
                <tr>
                    <td class="px-3 py-1">{{ $item->product->name }}</td>
                    <td class="px-3 py-1 text-center">{{ $item->quantity }}</td>
                    <td class="px-3 py-1 text-right">{{ number_format($item->unit_cost_at_time ?? 0, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Botón para completar trabajo (solo si no está completada/cancelada y el usuario tiene permiso) -->
    @if(!in_array($workOrder->status, ['completed', 'cancelled']) && auth()->user()->can('complete work_orders'))
        <div class="mt-6 flex justify-end">
            <button wire:click="completeWorkOrder"
                onclick="return confirm('¿Marcar esta orden como completada? Esta acción no se puede deshacer.')"
                class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                Completar trabajo
            </button>
        </div>
    @endif
</div>