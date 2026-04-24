<div>
    <h1 class="text-lg font-semibold mb-4">Dashboard</h1>

    <!-- Tarjetas resumen -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-red-500">
            <div class="text-sm text-gray-500">Stock bajo</div>
            <div class="text-2xl font-bold">{{ $lowStockCount }}</div>
            @if(module_active('reports'))
                <a href="{{ route('reports.stock') }}" class="text-xs text-blue-600">Ver reporte →</a>
            @endif
        </div>
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-yellow-500">
            <div class="text-sm text-gray-500">Solicitudes pendientes</div>
            <div class="text-2xl font-bold">{{ $pendingRequestsCount }}</div>
            <a href="{{ route('technician-requests.index') }}" class="text-xs text-blue-600">Gestionar →</a>
        </div>
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-blue-500">
            <div class="text-sm text-gray-500">Órdenes activas</div>
            <div class="text-2xl font-bold">{{ $activeWorkOrdersCount }}</div>
            <a href="{{ route('work-orders.index') }}" class="text-xs text-blue-600">Ver →</a>
        </div>
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-green-500">
            <div class="text-sm text-gray-500">Movimientos hoy</div>
            <div class="text-2xl font-bold">{{ $todayMovementsCount }}</div>
            <a href="{{ route('movements.index') }}" class="text-xs text-blue-600">Ver →</a>
        </div>
    </div>

    <!-- Últimos movimientos -->
    <div class="bg-white rounded-lg shadow p-4">
        <h2 class="font-medium mb-2">Últimos movimientos</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left">Fecha</th>
                        <th class="px-3 py-2 text-left">Producto</th>
                        <th class="px-3 py-2 text-center">Tipo</th>
                        <th class="px-3 py-2 text-right">Cantidad</th>
                        <th class="px-3 py-2 text-left">Usuario</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentMovements as $mov)
                        <tr class="border-b">
                            <td class="px-3 py-1">{{ $mov->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-3 py-1">{{ $mov->product->name }}</td>
                            <td class="px-3 py-1 text-center">{{ $mov->type }}</td>
                            <td class="px-3 py-1 text-right">{{ $mov->quantity }}</td>
                            <td class="px-3 py-1">{{ $mov->user->name }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>