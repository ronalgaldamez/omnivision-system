<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50">
            <h1 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                <span class="material-symbols-outlined text-gray-500">home_repair_service</span>
                Mi Panel de Control
            </h1>
            <p class="text-sm text-gray-500 mt-1">Resumen de tu actividad como técnico</p>
        </div>

        <div class="p-6 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Requisiciones pendientes --}}
                <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm hover:shadow-md transition p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm text-gray-500 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-yellow-500 text-base">inventory_2</span>
                                Requisiciones pendientes
                            </p>
                            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $pendingRequisitionsCount }}</p>
                        </div>
                        <span class="material-symbols-outlined text-yellow-100 text-3xl">hourglass_top</span>
                    </div>
                    <a href="{{ route('technician.requisitions.index') }}" class="mt-3 inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-800 transition">
                        Ver
                        <span class="material-symbols-outlined text-xs">arrow_forward</span>
                    </a>
                </div>

                {{-- Órdenes activas --}}
                <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm hover:shadow-md transition p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm text-gray-500 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-blue-500 text-base">engineering</span>
                                Órdenes activas
                            </p>
                            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $activeWorkOrdersCount }}</p>
                        </div>
                        <span class="material-symbols-outlined text-blue-100 text-3xl">work</span>
                    </div>
                    <a href="{{ route('mobile.work-orders.list') }}" class="mt-3 inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-800 transition">
                        Ver
                        <span class="material-symbols-outlined text-xs">arrow_forward</span>
                    </a>
                </div>
            </div>

            {{-- Últimas requisiciones --}}
            <div class="border-t border-gray-200 pt-6">
                <h2 class="text-md font-semibold text-gray-800 flex items-center gap-2 mb-3">
                    <span class="material-symbols-outlined text-gray-500">history</span>
                    Mis últimas requisiciones
                </h2>
                <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">Fecha</th>
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">Productos</th>
                                <th class="px-4 py-3 text-center text-gray-600 font-medium">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($recentRequisitions as $req)
                                <tr class="hover:bg-gray-50/80 transition">
                                    <td class="px-4 py-3 font-mono text-xs text-gray-700">{{ $req->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-4 py-3 text-gray-800">
                                        @foreach($req->items as $item)
                                            <div class="text-sm">{{ $item->product->name }} ({{ $item->quantity_requested }})</div>
                                        @endforeach
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($req->status == 'open')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-yellow-50 text-yellow-700 rounded-full text-xs font-medium">
                                                <span class="material-symbols-outlined text-sm">schedule</span>
                                                Abierta
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-gray-100 text-gray-600 rounded-full text-xs font-medium">
                                                <span class="material-symbols-outlined text-sm">lock</span>
                                                Cerrada
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-12 text-center bg-gray-50/50">
                                        <span class="material-symbols-outlined text-gray-300 text-4xl mb-2">inbox</span>
                                        <p class="text-gray-500">No hay requisiciones recientes</p>
                                        <p class="text-sm text-gray-400 mt-1">Tus requisiciones aparecerán aquí</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>