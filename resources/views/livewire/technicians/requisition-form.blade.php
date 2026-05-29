<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50">
            <h1 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                <span class="material-symbols-outlined text-gray-500">inventory_2</span>
                Nueva Requisición de Material
            </h1>
            <p class="text-sm text-gray-500 mt-1">Solicitud de material para órdenes de trabajo asignadas</p>
        </div>

        <div class="p-6">
            <form wire:submit.prevent="promptSave" class="space-y-6">
                <!-- Selección de Órdenes de Trabajo -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-gray-400 text-base">engineering</span>
                        Órdenes de Trabajo *
                    </label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        @foreach($workOrders as $wo)
                            <label class="flex items-center gap-2 p-2 bg-gray-50/80 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-100 transition">
                                <input type="checkbox" value="{{ $wo->id }}" wire:model="selectedWorkOrders"
                                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-2 focus:ring-blue-500/20">
                                <span class="text-sm text-gray-700">#{{ $wo->id }} - {{ $wo->client->name ?? 'N/A' }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('selectedWorkOrders') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Inventario actual del técnico -->
                @if(count($technicianStock))
                <div class="bg-blue-50 rounded-xl border border-blue-200 p-4">
                    <h3 class="text-sm font-semibold text-blue-800 flex items-center gap-1.5 mb-2">
                        <span class="material-symbols-outlined text-blue-500 text-base">inventory</span>
                        Tu inventario actual
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                        @foreach($technicianStock as $productId => $data)
                            <div class="text-sm text-blue-700">
                                <span class="font-medium">{{ $data['name'] }}</span>:
                                <span class="font-mono">{{ $data['quantity'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Agregar productos -->
                <div class="border-t border-gray-200 pt-6">
                    <h2 class="text-md font-semibold text-gray-800 flex items-center gap-2 mb-4">
                        <span class="material-symbols-outlined text-gray-500">add_shopping_cart</span>
                        Material solicitado
                    </h2>

                    <div class="bg-gray-50/80 rounded-xl border border-gray-200 p-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="relative">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Producto</label>
                                <input type="text" wire:model.live.debounce.300ms="currentProductSearch"
                                    placeholder="Buscar por nombre o SKU..."
                                    class="w-full pl-9 pr-3 py-2 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                                <span class="material-symbols-outlined absolute left-3 top-7 text-gray-400 text-lg">search</span>
                                @if(count($productResults))
                                    <ul class="absolute z-10 mt-1 w-full bg-white rounded-lg border border-gray-200 shadow-lg max-h-56 overflow-auto divide-y divide-gray-100">
                                        @foreach($productResults as $product)
                                            <li wire:click="selectProduct({{ $product->id }})"
                                                class="px-4 py-2 hover:bg-blue-50 cursor-pointer text-sm">
                                                {{ $product->name }} ({{ $product->sku }})
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Cantidad</label>
                                <input type="number" step="any" wire:model="currentQuantity"
                                    class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <button type="button" wire:click="addItem"
                                class="inline-flex items-center gap-2 px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-blue-700 transition">
                                <span class="material-symbols-outlined text-base">add_circle</span>
                                Agregar producto
                            </button>
                        </div>
                    </div>

                    <!-- Tabla de productos agregados -->
                    @if(count($items))
                    <div class="mt-5 overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200">
                                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Producto</th>
                                    <th class="px-4 py-3 text-center text-gray-600 font-medium">Cantidad</th>
                                    <th class="px-4 py-3 text-center text-gray-600 font-medium">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($items as $index => $item)
                                <tr class="hover:bg-gray-50/80 transition">
                                    <td class="px-4 py-3 text-gray-800">{{ $item['product_name'] }} ({{ $item['product_sku'] }})</td>
                                    <td class="px-4 py-3 text-center">{{ $item['quantity'] }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <button type="button" wire:click="removeItem({{ $index }})"
                                            class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition">
                                            <span class="material-symbols-outlined text-lg">delete</span>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>

                <!-- Notas -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-gray-400 text-base">sticky_note_2</span>
                        Notas
                    </label>
                    <textarea wire:model="notes" rows="2"
                        class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm resize-none"
                        placeholder="Notas opcionales..."></textarea>
                </div>

                <!-- Botones -->
                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('technician.requisitions.index') }}"
                        class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition shadow-sm">
                        Cancelar
                    </a>
                    <button type="submit"
                        class="px-5 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium shadow-sm hover:bg-blue-700 transition inline-flex items-center gap-2">
                        <span class="material-symbols-outlined text-base">save</span>
                        Guardar Requisición
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal de confirmación -->
    @if($confirmingSave)
        <div x-data="{ open: true }" x-show="open" x-cloak
            class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center"
            style="display: none;">
            <div class="relative mx-auto p-5 w-full max-w-md">
                <div class="bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden">
                    <div class="p-6 text-center">
                        <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-blue-100 mb-4">
                            <span class="material-symbols-outlined text-blue-600 text-2xl">help</span>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Confirmar requisición</h3>
                        <p class="text-sm text-gray-600 mt-2">
                            ¿Estás seguro de crear esta requisición con {{ count($items) }} producto(s)?
                        </p>
                    </div>
                    <div class="bg-gray-50 px-6 py-4 flex flex-col gap-3 sm:flex-row-reverse">
                        <button wire:click="executeSave"
                            class="w-full sm:w-auto px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-blue-700 transition">
                            Sí, continuar
                        </button>
                        <button @click="open = false" wire:click="cancelSave"
                            class="w-full sm:w-auto px-5 py-2.5 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Toast -->
    <div x-data="{ toast: null, toastType: null, toastMessage: '' }"
        x-on:show-toast.window="toast = true; toastType = $event.detail.type; toastMessage = $event.detail.message; setTimeout(() => toast = false, 5000)"
        x-show="toast" x-cloak class="fixed bottom-5 right-5 z-50 transition-all duration-300"
        style="display: none;">
        <div x-show="toastType === 'success'"
            class="bg-green-600 text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-3">
            <span class="material-symbols-outlined">check_circle</span>
            <span x-text="toastMessage" class="text-sm font-medium"></span>
        </div>
        <div x-show="toastType === 'error'"
            class="bg-red-600 text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-3">
            <span class="material-symbols-outlined">error</span>
            <span x-text="toastMessage" class="text-sm font-medium"></span>
        </div>
    </div>

    <style>[x-cloak] { display: none !important; }</style>
</div>