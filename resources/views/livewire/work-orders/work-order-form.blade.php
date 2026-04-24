<div class="max-w-4xl mx-auto bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <h1 class="text-xl font-bold mb-4">{{ $orderId ? 'Editar' : 'Nueva' }} Orden de Trabajo</h1>
    <form wire:submit.prevent="save" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium">Técnico *</label>
                <select wire:model="technician_id" class="mt-1 w-full rounded-md border-gray-300">
                    <option value="">Seleccione</option>
                    @foreach($technicians as $tech)
                        <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                    @endforeach
                </select>
                @error('technician_id') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
            </div>

            <!-- Cliente (buscador + botón nuevo) -->
            <div>
                <label class="block text-sm font-medium">Cliente *</label>
                <div class="flex gap-2">
                    <div class="relative flex-1">
                        <input type="text" wire:model.live.debounce.300ms="clientSearch"
                            placeholder="Buscar por nombre o teléfono..." class="w-full rounded-md border-gray-300">
                        @if(count($clientSearchResults) > 0)
                            <ul
                                class="absolute z-10 bg-white border border-gray-300 rounded-md mt-1 w-full max-h-40 overflow-auto shadow-lg">
                                @foreach($clientSearchResults as $client)
                                    <li wire:click="selectClient({{ $client->id }}, '{{ $client->name }}', '{{ $client->phone }}')"
                                        class="p-2 hover:bg-gray-100 cursor-pointer text-sm">
                                        {{ $client->name }} - {{ $client->phone ?? 'Sin teléfono' }}
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                    <button type="button" wire:click="openClientModal"
                        class="px-3 py-1.5 bg-green-600 text-white rounded-md text-sm hover:bg-green-700">
                        Nuevo Cliente
                    </button>
                </div>
                @error('client_id') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium">Fecha programada</label>
                <input type="date" wire:model="scheduled_date" class="mt-1 w-full rounded-md border-gray-300">
            </div>

            <div>
                <label class="block text-sm font-medium">Latitud</label>
                <input type="text" wire:model="latitude" readonly
                    class="mt-1 w-full rounded-md border-gray-300 bg-gray-100">
                @error('latitude') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium">Longitud</label>
                <input type="text" wire:model="longitude" readonly
                    class="mt-1 w-full rounded-md border-gray-300 bg-gray-100">
                @error('longitude') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium">Ubicación en el mapa</label>
                <div id="map" style="height: 300px; width: 100%;" class="border rounded mt-1"></div>
                <div class="flex gap-2 mt-2">
                    <button type="button" id="getLocationBtn"
                        class="px-3 py-1 bg-green-600 text-white rounded text-sm">Mi ubicación</button>
                    <button type="button" id="clearLocationBtn"
                        class="px-3 py-1 bg-gray-500 text-white rounded text-sm">Limpiar</button>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium">Estado</label>
                <select wire:model="status" class="mt-1 w-full rounded-md border-gray-300">
                    <option value="pending">Pendiente</option>
                    <option value="in_progress">En progreso</option>
                    <option value="completed">Completada</option>
                    <option value="cancelled">Cancelada</option>
                </select>
                @error('status') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium">Notas</label>
                <textarea wire:model="notes" rows="2" class="mt-1 w-full rounded-md border-gray-300"></textarea>
            </div>
        </div>

        <!-- Productos de la orden -->
        <div class="border-t pt-4">
            <h2 class="font-medium mb-2">Productos de la orden</h2>
            <div class="border rounded-lg p-3 bg-gray-50 mb-3">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div class="relative">
                        <label class="block text-xs">Producto</label>
                        <input type="text" wire:model.live.debounce.300ms="currentProductSearch"
                            placeholder="Buscar por nombre o SKU..." class="w-full rounded border-gray-300 text-sm">
                        @if(count($searchResults) > 0)
                            <ul
                                class="absolute z-10 bg-white border border-gray-300 rounded-md mt-1 w-full max-h-40 overflow-auto shadow-lg">
                                @foreach($searchResults as $res)
                                    <li wire:click="selectProduct({{ $res->id }})"
                                        class="p-2 hover:bg-gray-100 cursor-pointer text-sm">
                                        {{ $res->name }} ({{ $res->sku }})
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                    <div>
                        <label class="block text-xs">Cantidad</label>
                        <input type="number" step="any" wire:model="currentQuantity"
                            class="w-full rounded border-gray-300 text-sm">
                    </div>
                    <div class="flex items-end">
                        <button type="button" wire:click="addProduct"
                            class="bg-blue-600 text-white px-3 py-1 rounded text-sm">Agregar</button>
                    </div>
                </div>
            </div>

            @if(count($products) > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm border-collapse">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left border">Producto</th>
                                <th class="px-3 py-2 text-center border">Cantidad</th>
                                <th class="px-3 py-2 text-center border">Costo unitario</th>
                                <th class="px-3 py-2 text-center border">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $index => $prod)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="px-3 py-2 border">{{ $prod['product_name'] }} ({{ $prod['product_sku'] }})</td>
                                    <td class="px-3 py-2 border text-center">{{ $prod['quantity'] }}</td>
                                    <td class="px-3 py-2 border text-center">
                                        {{ number_format($prod['unit_cost_at_time'] ?? 0, 2) }}</td>
                                    <td class="px-3 py-2 border text-center">
                                        <button type="button" wire:click="removeProduct({{ $index }})"
                                            class="text-red-600">Eliminar</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center text-gray-400 py-2">No hay productos agregados</div>
            @endif
        </div>

        <div class="flex justify-end gap-2 pt-2">
            <a href="{{ route('work-orders.index') }}"
                class="px-3 py-1.5 border border-gray-300 rounded-md text-sm hover:bg-gray-50">Cancelar</a>
            <button type="submit"
                class="px-3 py-1.5 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700">Guardar</button>
        </div>
    </form>

    <!-- Modal para crear cliente -->
    @if($showClientModal)
        <div x-data="{ open: true }" x-show="open" x-cloak
            class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Nuevo Cliente</h3>
                    <button type="button" wire:click="closeClientModal"
                        class="text-gray-500 hover:text-gray-700">&times;</button>
                </div>
                <livewire:clients.client-form />
            </div>
        </div>
    @endif

    <!-- Script para manejar la creación de cliente y selección automática -->
    @push('scripts')
        <script>
            document.addEventListener('livewire:init', () => {
                Livewire.on('clientCreated', (clientId, clientName, clientPhone) => {
                    // Seleccionar el cliente recién creado
                    @this.call('selectClient', clientId, clientName, clientPhone);
                    // Cerrar el modal
                    @this.call('closeClientModal');
                });
            });
        </script>
    @endpush
</div>

<script>
    document.addEventListener('livewire:load', function () {
        var map = L.map('map').setView([13.6929, -89.2182], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a>'
        }).addTo(map);
        var marker = null;

        var lat = @json($latitude ?? null);
        var lng = @json($longitude ?? null);
        if (lat && lng) {
            map.setView([lat, lng], 15);
            marker = L.marker([lat, lng]).addTo(map);
        }

        map.on('click', function (e) {
            if (marker) map.removeLayer(marker);
            marker = L.marker(e.latlng).addTo(map);
            @this.set('latitude', e.latlng.lat);
            @this.set('longitude', e.latlng.lng);
        });

        document.getElementById('getLocationBtn').addEventListener('click', function () {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function (position) {
                    var pos = [position.coords.latitude, position.coords.longitude];
                    map.setView(pos, 15);
                    if (marker) map.removeLayer(marker);
                    marker = L.marker(pos).addTo(map);
                    @this.set('latitude', pos[0]);
                    @this.set('longitude', pos[1]);
                });
            } else {
                alert('Geolocalización no soportada');
            }
        });

        document.getElementById('clearLocationBtn').addEventListener('click', function () {
            if (marker) map.removeLayer(marker);
            @this.set('latitude', null);
            @this.set('longitude', null);
            map.setView([13.6929, -89.2182], 13);
        });
    });
</script>