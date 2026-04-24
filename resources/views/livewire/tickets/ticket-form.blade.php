<div class="max-w-2xl mx-auto bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <h1 class="text-lg font-semibold mb-4">Nuevo Ticket</h1>

    <form wire:submit.prevent="save" class="space-y-4">
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
                                <li wire:click="selectClient({{ $client->id }}, '{{ $client->name }}')"
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
            @error('client_id') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
        </div>

        <!-- Descripción -->
        <div>
            <label class="block text-sm font-medium">Descripción *</label>
            <textarea wire:model="description" rows="3" class="mt-1 w-full rounded-md border-gray-300"></textarea>
            @error('description') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
        </div>

        <!-- Tipo de servicio -->
        <div>
            <label class="block text-sm font-medium">Tipo de servicio *</label>
            <select wire:model="service_type" class="mt-1 w-full rounded-md border-gray-300">
                <option value="">Seleccione</option>
                <option value="instalacion">Instalación</option>
                <option value="traslado">Traslado</option>
                <option value="revision">Revisión</option>
                <option value="cobro_pendiente">Cobro pendiente</option>
                <option value="reconexion">Reconexión</option>
                <option value="desconexion">Desconexión</option>
            </select>
            @error('service_type') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
        </div>

        <!-- Requiere NOC -->
        <div class="flex items-center justify-between">
            <div>
                <label class="block text-sm font-medium">¿Requiere intervención del NOC?</label>
                <p class="text-xs text-gray-500">Si activas esta opción, el ticket se enviará al panel NOC y no se
                    creará OT automáticamente.</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" wire:model="requires_noc" class="sr-only peer">
                <div
                    class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600">
                </div>
            </label>
        </div>

        <div class="flex justify-end space-x-2 pt-2">
            <a href="{{ route('tickets.index') }}"
                class="px-3 py-1.5 border border-gray-300 rounded-md text-sm hover:bg-gray-50">Cancelar</a>
            <button type="submit" class="px-3 py-1.5 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700">Crear
                Ticket</button>
        </div>
    </form>

    <!-- Modal para crear cliente -->
    @if($showClientModal)
        <div x-data="{ open: true }" x-show="open" x-cloak
            class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Nuevo Cliente</h3>
                    <button @click="$wire.closeClientModal()" class="text-gray-500 hover:text-gray-700">&times;</button>
                </div>
                <livewire:clients.client-form />
            </div>
        </div>

        <script>
            document.addEventListener('livewire:init', () => {
                Livewire.on('clientCreated', (clientId, clientName) => {
                    @this.call('selectClient', clientId, clientName);
                    @this.call('closeClientModal');
                });
            });
        </script>
    @endif

    <!-- Toast (si no lo tienes en el layout, puedes incluirlo aquí) -->
    <div x-data="{ toast: null, toastType: null, toastMessage: '' }"
        x-on:show-toast.window="toast = true; toastType = $event.detail.type; toastMessage = $event.detail.message; setTimeout(() => toast = false, 5000)"
        x-show="toast" x-cloak class="fixed bottom-5 right-5 z-50 transition-all duration-300">
        <div x-show="toastType === 'success'"
            class="bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg flex items-center gap-2">
            <span class="material-symbols-outlined">check_circle</span> <span x-text="toastMessage"></span>
        </div>
        <div x-show="toastType === 'error'"
            class="bg-red-500 text-white px-4 py-2 rounded-lg shadow-lg flex items-center gap-2">
            <span class="material-symbols-outlined">error</span> <span x-text="toastMessage"></span>
        </div>
    </div>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</div>