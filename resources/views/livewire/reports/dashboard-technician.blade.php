<div>
    <h1 class="text-lg font-semibold mb-4">Mi Panel de Control</h1>

    <!-- Tarjetas resumen -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-yellow-500">
            <div class="text-sm text-gray-500">Solicitudes pendientes</div>
            <div class="text-2xl font-bold">{{ $pendingRequestsCount }}</div>
            <a href="{{ route('mobile.technician.requests') }}" class="text-xs text-blue-600">Ver →</a>
        </div>
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-blue-500">
            <div class="text-sm text-gray-500">Órdenes activas</div>
            <div class="text-2xl font-bold">{{ $activeWorkOrdersCount }}</div>
            <a href="{{ route('mobile.work-orders.list') }}" class="text-xs text-blue-600">Ver →</a>
        </div>
    </div>

    <!-- Últimas solicitudes -->
    <div class="bg-white rounded-lg shadow p-4">
        <h2 class="font-medium mb-2">Mis últimas solicitudes</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left">Fecha</th>
                        <th class="px-3 py-2 text-left">Productos</th>
                        <th class="px-3 py-2 text-center">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentRequests as $req)
                        <tr class="border-b">
                            <td class="px-3 py-1">{{ $req->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-3 py-1">
                                @foreach($req->products as $rp)
                                    {{ $rp->product->name }} ({{ $rp->quantity_requested }})<br>
                                @endforeach
                            </td>
                            <td class="px-3 py-1 text-center">
                                @if($req->status == 'pending')
                                    <span class="text-yellow-600">Pendiente</span>
                                @elseif($req->status == 'delivered')
                                    <span class="text-green-600">Entregada</span>
                                @else
                                    <span class="text-red-600">Rechazada</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-gray-400 py-4">No hay solicitudes</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>