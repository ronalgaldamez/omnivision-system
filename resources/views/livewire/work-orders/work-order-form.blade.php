<div class="max-w-5xl mx-auto">
    <!-- Tarjeta principal -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <!-- Encabezado con fondo sutil -->
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                        <span class="material-symbols-outlined text-gray-500">
                            {{ $orderId ? 'edit' : 'engineering' }}
                        </span>
                        {{ $orderId ? 'Editar' : 'Nueva' }} Orden de Trabajo
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ $orderId ? 'Modifica los datos de la orden' : 'Asigna un técnico y registra una nueva orden' }}
                    </p>
                </div>
                <a href="{{ route('work-orders.index') }}"
                    class="inline-flex items-center gap-1.5 text-sm text-blue-600 hover:text-blue-800 transition">
                    <span class="material-symbols-outlined text-base">arrow_back</span>
                    Volver al listado
                </a>
            </div>
        </div>

        <!-- Contenido del formulario -->
        <div class="p-6">
            <form wire:submit.prevent="save" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- Técnico -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-gray-400 text-base">engineering</span>
                            Técnico *
                        </label>
                        <div class="relative">
                            <select wire:model="technician_id"
                                class="w-full pl-9 pr-8 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm appearance-none">
                                <option value="">Seleccione</option>
                                @foreach($technicians as $tech)
                                    <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                                @endforeach
                            </select>
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">person</span>
                            <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
                        </div>
                        @error('technician_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Cliente (buscador + botón nuevo) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-gray-400 text-base">person</span>
                            Cliente *
                        </label>
                        <div class="flex gap-2">
                            <div class="relative flex-1">
                                <input type="text" wire:model.live.debounce.300ms="clientSearch"
                                    placeholder="Buscar por nombre o teléfono..."
                                    class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                                @if(count($clientSearchResults) > 0)
                                    <ul class="absolute z-10 mt-1 w-full bg-white rounded-lg border border-gray-200 shadow-lg max-h-56 overflow-auto divide-y divide-gray-100">
                                        @foreach($clientSearchResults as $client)
                                            <li wire:click="selectClient({{ $client->id }}, '{{ $client->name }}', '{{ $client->phone }}')"
                                                class="px-4 py-2.5 hover:bg-blue-50 cursor-pointer transition text-sm flex items-center justify-between">
                                                <span class="font-medium text-gray-800">{{ $client->name }}</span>
                                                <span class="text-xs text-gray-500">{{ $client->phone ?? 'Sin teléfono' }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                            <button type="button" wire:click="openClientModal"
                                class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-green-700 transition flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-base">person_add</span>
                                Nuevo
                            </button>
                        </div>
                        @error('client_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Fecha programada -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-gray-400 text-base">calendar_today</span>
                            Fecha programada
                        </label>
                        <div class="relative">
                            <input type="date" wire:model="scheduled_date"
                                class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">event</span>
                        </div>
                    </div>

                    <!-- Estado -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-gray-400 text-base">flag</span>
                            Estado
                        </label>
                        <div class="relative">
                            <select wire:model="status"
                                class="w-full pl-9 pr-8 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm appearance-none">
                                <option value="pending">Pendiente</option>
                                <option value="in_progress">En progreso</option>
                                <option value="completed">Completada</option>
                                <option value="cancelled">Cancelada</option>
                            </select>
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">info</span>
                            <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
                        </div>
                        @error('status') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Latitud -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-gray-400 text-base">pin_drop</span>
                            Latitud
                        </label>
                        <div class="relative">
                            <input type="text" wire:model="latitude" readonly
                                class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-gray-50 text-gray-700 shadow-sm text-sm">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">location_on</span>
                        </div>
                        @error('latitude') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Longitud -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-gray-400 text-base">pin_drop</span>
                            Longitud
                        </label>
                        <div class="relative">
                            <input type="text" wire:model="longitude" readonly
                                class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-gray-50 text-gray-700 shadow-sm text-sm">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">location_on</span>
                        </div>
                        @error('longitude') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Mapa -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-gray-400 text-base">map</span>
                            Ubicación en el mapa
                        </label>
                        <div id="map" style="height: 300px; width: 100%;" class="rounded-lg border border-gray-300 shadow-sm"></div>
                        <div class="flex gap-2 mt-3">
                            <button type="button" id="getLocationBtn"
                                class="inline-flex items-center gap-1.5 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-green-700 transition">
                                <span class="material-symbols-outlined text-base">my_location</span>
                                Mi ubicación
                            </button>
                            <button type="button" id="clearLocationBtn"
                                class="inline-flex items-center gap-1.5 px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-300 transition shadow-sm">
                                <span class="material-symbols-outlined text-base">delete</span>
                                Limpiar
                            </button>
                        </div>
                    </div>

                    <!-- Notas -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-gray-400 text-base">sticky_note_2</span>
                            Notas
                        </label>
                        <div class="relative">
                            <textarea wire:model="notes" rows="3"
                                class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm resize-none"
                                placeholder="Notas o indicaciones adicionales"></textarea>
                            <span class="material-symbols-outlined absolute left-3 top-2.5 text-gray-400 text-lg">edit_note</span>
                        </div>
                    </div>
                </div>

                <!-- Productos de la orden -->
                <div class="border-t border-gray-200 pt-6">
                    <h2 class="text-md font-semibold text-gray-800 flex items-center gap-2 mb-4">
                        <span class="material-symbols-outlined text-gray-500">inventory_2</span>
                        Productos de la orden
                    </h2>
                    <div class="bg-gray-50/80 rounded-xl border border-gray-200 p-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="relative">
                                <label class="block text-xs font-medium text-gray-600 mb-1 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-gray-400 text-sm">inventory_2</span>
                                    Producto
                                </label>
                                <div class="relative">
                                    <input type="text" wire:model.live.debounce.300ms="currentProductSearch"
                                        placeholder="Buscar por nombre o SKU..."
                                        class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                                </div>
                                @if(count($searchResults) > 0)
                                    <ul class="absolute z-10 mt-1 w-full bg-white rounded-lg border border-gray-200 shadow-lg max-h-56 overflow-auto divide-y divide-gray-100">
                                        @foreach($searchResults as $res)
                                            <li wire:click="selectProduct({{ $res->id }})"
                                                class="px-4 py-2.5 hover:bg-blue-50 cursor-pointer transition text-sm flex items-center justify-between">
                                                <span class="font-medium text-gray-800">{{ $res->name }}</span>
                                                <span class="text-xs text-gray-500">({{ $res->sku }})</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-gray-400 text-sm">numbers</span>
                                    Cantidad
                                </label>
                                <div class="relative">
                                    <input type="number" step="any" wire:model="currentQuantity"
                                        class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">tag</span>
                                </div>
                            </div>
                            <div class="flex items-end">
                                <button type="button" wire:click="addProduct"
                                    class="w-full px-4 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-blue-700 transition inline-flex items-center justify-center gap-1.5">
                                    <span class="material-symbols-outlined text-base">add_circle</span>
                                    Agregar
                                </button>
                            </div>
                        </div>
                    </div>

                    @if(count($products) > 0)
                        <div class="mt-5 space-y-4">
                            <h3 class="text-sm font-medium text-gray-700 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-500 text-base">list_alt</span>
                                Productos agregados ({{ count($products) }})
                            </h3>
                            <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                                <table class="min-w-full text-sm">
                                    <thead>
                                        <tr class="bg-gray-50 border-b border-gray-200">
                                            <th class="px-4 py-3 text-left text-gray-600 font-medium">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="material-symbols-outlined text-gray-400 text-base">inventory_2</span>
                                                    Producto
                                                </div>
                                            </th>
                                            <th class="px-4 py-3 text-center text-gray-600 font-medium">
                                                <div class="flex items-center justify-center gap-1.5">
                                                    <span class="material-symbols-outlined text-gray-400 text-base">numbers</span>
                                                    Cantidad
                                                </div>
                                            </th>
                                            <th class="px-4 py-3 text-center text-gray-600 font-medium">
                                                <div class="flex items-center justify-center gap-1.5">
                                                    <span class="material-symbols-outlined text-gray-400 text-base">attach_money</span>
                                                    Costo unitario
                                                </div>
                                            </th>
                                            <th class="px-4 py-3 text-center text-gray-600 font-medium">
                                                <div class="flex items-center justify-center gap-1.5">
                                                    <span class="material-symbols-outlined text-gray-400 text-base">settings</span>
                                                    Acciones
                                                </div>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach($products as $index => $prod)
                                            <tr class="hover:bg-gray-50/80 transition">
                                                <td class="px-4 py-3 text-gray-800">
                                                    {{ $prod['product_name'] }}
                                                    <span class="text-gray-500 text-xs ml-1">({{ $prod['product_sku'] }})</span>
                                                </td>
                                                <td class="px-4 py-3 text-center text-gray-700">{{ $prod['quantity'] }}</td>
                                                <td class="px-4 py-3 text-center text-gray-700">
                                                    {{ number_format($prod['unit_cost_at_time'] ?? 0, 2) }}
                                                </td>
                                                <td class="px-4 py-3 text-center">
                                                    <button type="button" wire:click="removeProduct({{ $index }})"
                                                        class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition" title="Eliminar">
                                                        <span class="material-symbols-outlined text-lg">delete</span>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @else
                        <div class="bg-gray-50/50 rounded-xl border border-dashed border-gray-300 py-10 text-center mt-5">
                            <span class="material-symbols-outlined text-gray-300 text-5xl mb-3">inbox</span>
                            <p class="text-gray-500">No hay productos agregados</p>
                            <p class="text-sm text-gray-400 mt-1">Usa el formulario para agregar productos a la orden</p>
                        </div>
                    @endif
                </div>

                <!-- Botones de acción -->
                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('work-orders.index') }}"
                        class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-300 transition shadow-sm">
                        Cancelar
                    </a>
                    <button type="submit"
                        class="px-5 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 transition inline-flex items-center gap-2">
                        <span class="material-symbols-outlined text-base">save</span>
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para crear cliente -->
    @if($showClientModal)
        <div x-data="{ open: true }" x-show="open" x-cloak
            class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center">
            <div class="relative mx-auto p-5 w-full max-w-md">
                <div class="bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                        <h3 class="text-lg font-semibold flex items-center gap-2">
                            <span class="material-symbols-outlined text-gray-500">person_add</span>
                            Nuevo Cliente
                        </h3>
                        <button type="button" wire:click="closeClientModal" class="text-gray-400 hover:text-gray-600 transition">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>
                    <div class="p-5">
                        <livewire:clients.client-form />
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Script para manejar la creación de cliente y selección automática -->
    @push('scripts')
        <script>
            document.addEventListener('livewire:init', () => {
                Livewire.on('clientCreated', (clientId, clientName, clientPhone) => {
                    @this.call('selectClient', clientId, clientName, clientPhone);
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