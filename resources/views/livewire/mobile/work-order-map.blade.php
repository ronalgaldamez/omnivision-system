<div>
    <x-ui.card icon="map" title="Mapa de mis órdenes de trabajo"
        subtitle="{{ $workOrders->count() }} órdenes con ubicación">
        @if ($workOrders->isEmpty())
            <div class="py-16 text-center">
                <span class="material-symbols-outlined text-gray-300 text-6xl">map</span>
                <p class="text-gray-500 font-medium mt-3">No tenés órdenes de trabajo con ubicación registrada</p>
                <p class="text-sm text-gray-400 mt-1">Registrá las coordenadas en la vista de la OT (o capturá tu ubicación) para verlas aquí</p>
                <a href="{{ route('mobile.work-orders.list') }}"
                    class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
                    <span class="material-symbols-outlined text-base">engineering</span>
                    Ver mis órdenes
                </a>
            </div>
        @else
            <div id="map" style="height: 520px; width: 100%;" class="border border-gray-200 rounded-xl"></div>

            {{-- Leyenda de prioridades --}}
            <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 text-xs text-gray-600">
                <span class="inline-flex items-center gap-1.5 font-medium text-gray-500 uppercase tracking-wider text-[11px]">Prioridad:</span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-full" style="background:#ef4444"></span> P1
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-full" style="background:#f59e0b"></span> P2
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-full" style="background:#2563eb"></span> P3
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-full" style="background:#6b7280"></span> P4
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-full border-2 border-white ring-1 ring-gray-300" style="background:#10b981"></span> Sin prioridad
                </span>
                <span class="text-gray-400 ml-auto">El marcador más grande = OT en progreso</span>
            </div>
        @endif
    </x-ui.card>
</div>

@if ($workOrders->isNotEmpty())
    @push('scripts')
        <script>
            function initWorkOrdersMap() {
                var container = document.getElementById('map');
                if (!container || window.__workOrdersMapInitialized) return;
                window.__workOrdersMapInitialized = true;

                var map = L.map('map').setView([13.6929, -89.2182], 12);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OSM' }).addTo(map);

                var workOrders = @json($workOrders);

                var priorityColors = {
                    P1: '#ef4444',
                    P2: '#f59e0b',
                    P3: '#2563eb',
                    P4: '#6b7280'
                };
                var defaultColor = '#10b981';

                var bounds = [];
                workOrders.forEach(function (order) {
                    if (!order.latitude || !order.longitude) return;

                    var color = priorityColors[order.priority] || defaultColor;
                    var isInProgress = order.status === 'in_progress';

                    var marker = L.circleMarker([order.latitude, order.longitude], {
                        radius: isInProgress ? 11 : 9,
                        color: '#ffffff',
                        weight: 2,
                        fillColor: color,
                        fillOpacity: 0.85,
                    }).addTo(map);

                    var navUrl = 'https://www.google.com/maps/dir/?api=1&destination=' + order.latitude + ',' + order.longitude;

                    marker.bindPopup(`
                        <div style="font-family: sans-serif; font-size: 13px; min-width: 190px;">
                            <div style="font-weight: 700; font-size: 14px; margin-bottom: 6px; display:flex; align-items:center; gap:6px;">
                                <span style="width:10px;height:10px;border-radius:9999px;display:inline-block;background:${color}"></span>
                                #${order.id} — ${order.code}
                            </div>
                            <div style="margin-bottom: 3px;"><b>Cliente:</b> ${order.client_name}</div>
                            <div style="margin-bottom: 3px;"><b>Dirección:</b> ${order.client_address || 'N/A'}</div>
                            <div style="margin-bottom: 3px;"><b>Prioridad:</b> <b style="color:${color}">${order.priority}</b></div>
                            <div style="margin-bottom: 8px;"><b>Estado:</b> ${order.status === 'in_progress' ? 'En progreso' : order.status === 'pending' ? 'Pendiente' : order.status === 'paused' ? 'Pausada' : order.status}</div>
                            <div style="display:flex; gap:6px;">
                                <a href="/mobile/technician/work-orders/${order.id}" style="flex:1; text-align:center; background:#2563eb; color:#fff; padding:6px 10px; border-radius:8px; text-decoration:none; font-weight:600;">Ver detalle</a>
                                <a href="${navUrl}" target="_blank" style="flex:1; text-align:center; background:#16a34a; color:#fff; padding:6px 10px; border-radius:8px; text-decoration:none; font-weight:600;">Cómo llegar</a>
                            </div>
                        </div>
                    `);

                    bounds.push([order.latitude, order.longitude]);
                });

                if (bounds.length > 0) {
                    map.fitBounds(bounds, { padding: [45, 45] });
                }
            }

            document.addEventListener('livewire:initialized', initWorkOrdersMap);
            if (document.readyState !== 'loading') {
                setTimeout(initWorkOrdersMap, 50);
            } else {
                document.addEventListener('DOMContentLoaded', function () {
                    setTimeout(initWorkOrdersMap, 50);
                });
            }
        </script>
    @endpush
@endif
