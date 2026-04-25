<div>
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-lg font-semibold text-gray-800">Órdenes de Trabajo</h1>
        <a href="{{ route('work-orders.create') }}" class="bg-blue-600 text-white px-3 py-1 rounded">Nueva Orden</a>
    </div>

    <div class="mb-4 flex gap-2">
        <input type="text" wire:model.live="search" placeholder="Buscar por cliente o técnico..."
            class="border rounded px-2 py-1 w-64">
        <select wire:model.live="statusFilter" class="border rounded px-2 py-1">
            <option value="">Todos los estados</option>
            <option value="pending">Pendiente</option>
            <option value="in_progress">En progreso</option>
            <option value="completed">Completada</option>
            <option value="cancelled">Cancelada</option>
        </select>
    </div>

    <div class="overflow-x-auto bg-white rounded shadow">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left">ID</th>
                    <th class="px-4 py-2 text-left">Cliente</th>
                    <th class="px-4 py-2 text-left">Técnico</th>
                    <th class="px-4 py-2 text-center">Estado</th>
                    <th class="px-4 py-2 text-center">Fecha programada</th>
                    <th class="px-4 py-2 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr class="border-b">
                        <td class="px-4 py-2">#{{ $order->id }}</td>
                        <td class="px-4 py-2">{{ $order->client->name ?? 'No especificado' }}</td>
                        <td class="px-4 py-2">{{ $order->technician?->name ?? 'No asignado' }}</td>
                        <td class="px-4 py-2 text-center">
                            @if($order->status == 'pending') <span class="text-yellow-600">Pendiente</span>
                            @elseif($order->status == 'in_progress') <span class="text-blue-600">En progreso</span>
                            @elseif($order->status == 'completed') <span class="text-green-600">Completada</span>
                            @else <span class="text-red-600">Cancelada</span> @endif
                        </td>
                        <td class="px-4 py-2 text-center">
                            {{ $order->scheduled_date ? $order->scheduled_date->format('d/m/Y') : '-' }}</td>
                        <td class="px-4 py-2 text-center space-x-2">
                            <a href="{{ route('work-orders.show', $order->id) }}" class="text-green-600">Ver</a>
                            <a href="{{ route('work-orders.edit', $order->id) }}" class="text-blue-600">Editar</a>
                            <button wire:click="delete({{ $order->id }})" class="text-red-600">Eliminar</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">No hay órdenes</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $orders->links() }}</div>
</div>