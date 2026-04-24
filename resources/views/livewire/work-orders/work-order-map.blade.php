<div>
    <h1 class="text-lg font-semibold mb-4">Mapa de Órdenes de Trabajo</h1>
    <div id="map" style="height: 500px; width: 100%;" class="border rounded"></div>

    <script>
        document.addEventListener('livewire:load', function () {
            var map = L.map('map').setView([13.6929, -89.2182], 12);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            var workOrders = @json($workOrders);
            workOrders.forEach(function (order) {
                if (order.latitude && order.longitude) {
                    var marker = L.marker([order.latitude, order.longitude]).addTo(map);
                    marker.bindPopup(`
                        <strong>#${order.id}</strong><br>
                        Cliente: ${order.client_name}<br>
                        Dirección: ${order.client_address || 'N/A'}<br>
                        <a href="/work-orders/${order.id}/show">Ver detalle</a>
                    `);
                }
            });
        });
    </script>
</div>