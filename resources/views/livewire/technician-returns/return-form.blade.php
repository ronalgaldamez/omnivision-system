<div class="max-w-2xl mx-auto bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <h1 class="text-lg font-semibold mb-4">Registrar Devolución de Materiales</h1>

    <form wire:submit.prevent="confirmSave" class="space-y-4">
        <div>
            <label class="block text-sm font-medium">Tipo *</label>
            <select wire:model="type" class="mt-1 w-full rounded-md border-gray-300">
                <option value="surplus">Sobrante (devuelve al inventario - recalcula costo promedio)</option>
                <option value="damage">Dañado (se descarta del inventario - resta stock)</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium">Orden de Trabajo *</label>
            <select wire:model.live="work_order_id" class="mt-1 w-full rounded-md border-gray-300">
                <option value="">Seleccione OT</option>
                @foreach($workOrders as $wo)
                    <option value="{{ $wo->id }}">#{{ $wo->id }} - {{ $wo->client->name ?? 'Cliente' }}
                        ({{ $wo->scheduled_date?->format('d/m/Y') }})</option>
                @endforeach
            </select>
            @error('work_order_id') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
        </div>

        @if($work_order_id && count($availableProducts) > 0)
            <div>
                <label class="block text-sm font-medium">Producto *</label>
                <div class="relative">
                    <input type="text" wire:model.live="productSearch"
                        placeholder="Buscar producto disponible para devolver..."
                        class="w-full rounded-md border-gray-300 text-sm">
                    @if($productSearch && count($availableProducts) > 0)
                        <ul
                            class="absolute z-10 bg-white border border-gray-300 rounded-md mt-1 w-full max-h-40 overflow-auto shadow-lg">
                            @foreach($availableProducts as $prod)
                                @if(stripos($prod['product_name'], $productSearch) !== false || stripos($prod['product_sku'], $productSearch) !== false)
                                    <li wire:click="selectProduct({{ $prod['product_id'] }}, {{ $prod['request_id'] }}, {{ $prod['available'] }})"
                                        class="p-2 hover:bg-gray-100 cursor-pointer text-sm">
                                        {{ $prod['product_name'] }} ({{ $prod['product_sku'] }}) - Disponible: {{ $prod['available'] }}
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    @endif
                </div>
                <select wire:model="product_id" class="mt-2 w-full rounded-md border-gray-300">
                    <option value="">Seleccione producto a devolver</option>
                    @foreach($availableProducts as $prod)
                        <option value="{{ $prod['product_id'] }}">{{ $prod['product_name'] }} (Disponible:
                            {{ $prod['available'] }})</option>
                    @endforeach
                </select>
                @error('product_id') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium">Cantidad a devolver *</label>
                <input type="number" wire:model="quantity" class="mt-1 w-full rounded-md border-gray-300" step="1">
                @error('quantity') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </div>
        @elseif($work_order_id && count($availableProducts) == 0)
            <div class="text-yellow-600 text-sm">No hay productos disponibles para devolver de esta OT.</div>
        @endif

        <div>
            <label class="block text-sm font-medium">Notas / Motivo</label>
            <textarea wire:model="notes" rows="2" class="mt-1 w-full rounded-md border-gray-300"></textarea>
        </div>

        <div class="flex justify-end space-x-2 pt-2">
            <a href="{{ route('technician-returns.index') }}"
                class="px-3 py-1.5 border border-gray-300 rounded-md text-sm hover:bg-gray-50">Cancelar</a>
            <button type="submit"
                class="px-3 py-1.5 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700">Registrar
                Devolución</button>
        </div>
    </form>

    <!-- Modal de confirmación (igual que antes) -->
    <div x-data="{ show: @entangle('showConfirmModal') }" x-show="show" x-cloak
        class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100">
                    <span class="material-symbols-outlined text-yellow-600">warning</span>
                </div>
                <h3 class="text-lg leading-6 font-medium text-gray-900 mt-2">Confirmar devolución</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500">{{ $confirmMessage }}</p>
                </div>
                <div class="items-center px-4 py-3">
                    <button wire:click="save"
                        class="px-4 py-2 bg-blue-600 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-blue-700">Sí,
                        registrar</button>
                    <button @click="show = false"
                        class="mt-2 px-4 py-2 bg-gray-300 text-gray-700 text-base font-medium rounded-md w-full hover:bg-gray-400">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast (si no está en el layout) -->
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