<div class="max-w-7xl mx-auto">
    <!-- Tarjeta principal -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <!-- Encabezado con fondo sutil -->
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50">
            <h1 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                <span class="material-symbols-outlined text-gray-500">monitoring</span>
                Rendimiento de Técnicos
            </h1>
            <p class="text-sm text-gray-500 mt-1">Resumen de actividad y devoluciones por técnico</p>
        </div>

        <!-- Contenido -->
        <div class="p-6 space-y-5">
            <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">
                                <div class="flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">engineering</span>
                                    Técnico
                                </div>
                            </th>
                            <th class="px-4 py-3 text-center text-gray-600 font-medium">
                                <div class="flex items-center justify-center gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">list_alt</span>
                                    Solicitudes totales
                                </div>
                            </th>
                            <th class="px-4 py-3 text-center text-gray-600 font-medium">
                                <div class="flex items-center justify-center gap-1.5">
                                    <span class="material-symbols-outlined text-green-600 text-base">check_circle</span>
                                    Aprobadas
                                </div>
                            </th>
                            <th class="px-4 py-3 text-center text-gray-600 font-medium">
                                <div class="flex items-center justify-center gap-1.5">
                                    <span class="material-symbols-outlined text-red-600 text-base">cancel</span>
                                    Rechazadas
                                </div>
                            </th>
                            <th class="px-4 py-3 text-center text-gray-600 font-medium">
                                <div class="flex items-center justify-center gap-1.5">
                                    <span class="material-symbols-outlined text-blue-600 text-base">arrow_upward</span>
                                    Sobrantes
                                </div>
                            </th>
                            <th class="px-4 py-3 text-center text-gray-600 font-medium">
                                <div class="flex items-center justify-center gap-1.5">
                                    <span
                                        class="material-symbols-outlined text-orange-600 text-base">broken_image</span>
                                    Dañados
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($technicians as $tech)
                            <tr class="hover:bg-gray-50/80 transition">
                                <td class="px-4 py-3 text-gray-800 font-medium">{{ $tech->name }}</td>
                                <td class="px-4 py-3 text-center text-gray-700">{{ $tech->total_requests }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span
                                        class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-green-50 text-green-700 rounded-full text-xs font-medium">
                                        {{ $tech->approved_requests }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span
                                        class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-red-50 text-red-700 rounded-full text-xs font-medium">
                                        {{ $tech->rejected_requests }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span
                                        class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-blue-50 text-blue-700 rounded-full text-xs font-medium">
                                        {{ $tech->surplus_returns ?? 0 }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span
                                        class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-orange-50 text-orange-700 rounded-full text-xs font-medium">
                                        {{ $tech->damage_returns ?? 0 }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center bg-gray-50/50">
                                    <span class="material-symbols-outlined text-gray-300 text-4xl mb-2">monitoring</span>
                                    <p class="text-gray-500">No se encontraron datos de rendimiento</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mensajes de sesión -->
            @if(session('message'))
                <div
                    class="flex items-center gap-2 text-sm text-green-700 bg-green-50 px-4 py-3 rounded-lg border border-green-200">
                    <span class="material-symbols-outlined text-green-600">check_circle</span>
                    {{ session('message') }}
                </div>
            @endif
            @if(session('error'))
                <div
                    class="flex items-center gap-2 text-sm text-red-700 bg-red-50 px-4 py-3 rounded-lg border border-red-200">
                    <span class="material-symbols-outlined text-red-600">error</span>
                    {{ session('error') }}
                </div>
            @endif
        </div>
    </div>
</div>