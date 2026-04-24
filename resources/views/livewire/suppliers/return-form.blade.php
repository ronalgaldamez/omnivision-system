<div class="max-w-6xl mx-auto">
    <!-- Tarjeta principal -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <!-- Encabezado con fondo sutil -->
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                        <span class="material-symbols-outlined text-gray-500">assignment_return</span>
                        Devolución a Proveedor
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">Registra la devolución de productos adquiridos</p>
                </div>
                <a href="{{ route('returns.index') }}"
                    class="inline-flex items-center gap-1.5 text-sm text-blue-600 hover:text-blue-800 transition">
                    <span class="material-symbols-outlined text-base">arrow_back</span>
                    Volver al listado
                </a>
            </div>
        </div>

        <!-- Contenido -->
        <div class="p-6">
            <form wire:submit.prevent="confirmReturn" class="space-y-6">
                <!-- Selección de proveedor -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-gray-400 text-base">warehouse</span>
                        Proveedor *
                    </label>
                    <div class="relative">
                        <select wire:model.live="supplier_id"
                            class="w-full pl-9 pr-8 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm appearance-none">
                            <option value="">Seleccione proveedor</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">business</span>
                        <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
                    </div>
                    @error('supplier_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Selección de factura/compra -->
                @if($supplier_id)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-gray-400 text-base">receipt</span>
                            Factura / Compra *
                        </label>
                        <div class="relative">
                            <select wire:model.live="purchase_id"
                                class="w-full pl-9 pr-8 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm appearance-none">
                                <option value="">Seleccione factura</option>
                                @foreach($purchases as $purchase)
                                    <option value="{{ $purchase->id }}">
                                        {{ $purchase->invoice_number }} - {{ $purchase->purchase_date->format('d/m/Y') }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">description</span>
                            <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
                        </div>
                        @error('purchase_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                @endif

                <!-- Modo de devolución (solo si hay items) -->
                @if($purchase_id && count($items) > 0)
                    <div class="border-t border-gray-200 pt-6">
                        <h2 class="text-md font-semibold text-gray-800 flex items-center gap-2 mb-4">
                            <span class="material-symbols-outlined text-gray-500">tune</span>
                            Modo de devolución
                        </h2>
                        <div class="flex flex-wrap gap-4">
                            <label class="flex items-center gap-2 p-3 bg-gray-50/80 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-100 transition">
                                <input type="radio" wire:model.live="returnMode" value="full"
                                    class="text-blue-600 focus:ring-2 focus:ring-blue-500/20">
                                <span class="text-sm text-gray-700">Devolución completa</span>
                            </label>
                            <label class="flex items-center gap-2 p-3 bg-gray-50/80 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-100 transition">
                                <input type="radio" wire:model.live="returnMode" value="individual"
                                    class="text-blue-600 focus:ring-2 focus:ring-blue-500/20">
                                <span class="text-sm text-gray-700">Devolución individual</span>
                            </label>
                            <label class="flex items-center gap-2 p-3 bg-gray-50/80 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-100 transition">
                                <input type="radio" wire:model.live="returnMode" value="partial"
                                    class="text-blue-600 focus:ring-2 focus:ring-blue-500/20">
                                <span class="text-sm text-gray-700">Devolución parcial</span>
                            </label>
                        </div>
                        @error('returnMode') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Tabla de productos -->
                    <div class="border-t border-gray-200 pt-6">
                        <h2 class="text-md font-semibold text-gray-800 flex items-center gap-2 mb-4">
                            <span class="material-symbols-outlined text-gray-500">inventory_2</span>
                            Productos
                        </h2>
                        <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="bg-gray-50 border-b border-gray-200">
                                        <th class="px-4 py-3 text-left text-gray-600 font-medium">
                                            <div class="flex items-center gap-1.5">
                                                <span class="material-symbols-outlined text-gray-400 text-base">inventory_2</span>
                                                Producto (SKU)
                                            </div>
                                        </th>
                                        <th class="px-4 py-3 text-center text-gray-600 font-medium">
                                            <div class="flex items-center justify-center gap-1.5">
                                                <span class="material-symbols-outlined text-gray-400 text-base">shopping_cart</span>
                                                Comprado
                                            </div>
                                        </th>
                                        <th class="px-4 py-3 text-center text-gray-600 font-medium">
                                            <div class="flex items-center justify-center gap-1.5">
                                                <span class="material-symbols-outlined text-gray-400 text-base">assignment_return</span>
                                                Ya devuelto
                                            </div>
                                        </th>
                                        <th class="px-4 py-3 text-center text-gray-600 font-medium">
                                            <div class="flex items-center justify-center gap-1.5">
                                                <span class="material-symbols-outlined text-gray-400 text-base">inventory</span>
                                                Disponible
                                            </div>
                                        </th>
                                        @if($returnMode == 'individual')
                                            <th class="px-4 py-3 text-center text-gray-600 font-medium">
                                                <div class="flex items-center justify-center gap-1.5">
                                                    <span class="material-symbols-outlined text-gray-400 text-base">select_check_box</span>
                                                    Devolver
                                                </div>
                                            </th>
                                        @elseif($returnMode == 'partial')
                                            <th class="px-4 py-3 text-center text-gray-600 font-medium">
                                                <div class="flex items-center justify-center gap-1.5">
                                                    <span class="material-symbols-outlined text-gray-400 text-base">numbers</span>
                                                    Cantidad a devolver
                                                </div>
                                            </th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($items as $item)
                                        <tr class="hover:bg-gray-50/80 transition">
                                            <td class="px-4 py-3 text-gray-800">
                                                {{ $item['product_name'] }}
                                                <span class="text-gray-500 text-xs ml-1">({{ $item['product_sku'] }})</span>
                                            </td>
                                            <td class="px-4 py-3 text-center text-gray-700">{{ $item['purchased_quantity'] }}</td>
                                            <td class="px-4 py-3 text-center text-gray-700">{{ $item['returned_quantity'] }}</td>
                                            <td class="px-4 py-3 text-center font-medium text-gray-800">
                                                {{ $item['available_quantity'] }}
                                            </td>
                                            @if($returnMode == 'individual')
                                                <td class="px-4 py-3 text-center">
                                                    <input type="checkbox" value="{{ $item['id'] }}" wire:model="selectedItems"
                                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-2 focus:ring-blue-500/20 w-4 h-4">
                                                </td>
                                            @elseif($returnMode == 'partial')
                                                <td class="px-4 py-3 text-center">
                                                    <input type="number" min="0" max="{{ $item['available_quantity'] }}" step="1"
                                                        wire:model="partialQuantities.{{ $item['id'] }}"
                                                        class="w-20 text-center rounded-lg border border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm py-1.5">
                                                    @error("partialQuantities.{$item['id']}")
                                                        <span class="text-xs text-red-500 block mt-1">{{ $message }}</span>
                                                    @enderror
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <!-- Botones de acción -->
                <div class="flex justify-end gap-3 pt-4">
                    <a href="{{ route('returns.index') }}"
                        class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-300 transition shadow-sm">
                        Cancelar
                    </a>
                    <button type="submit"
                        class="px-5 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 transition inline-flex items-center gap-2">
                        <span class="material-symbols-outlined text-base">save</span>
                        Registrar Devolución
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal de confirmación -->
    <div x-data="{ show: @entangle('showConfirmModal') }" x-show="show" x-cloak
        class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center"
        style="display: none;">
        <div class="relative mx-auto p-5 w-full max-w-md">
            <div class="bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden">
                <div class="p-6 text-center">
                    <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-yellow-100 mb-4">
                        <span class="material-symbols-outlined text-yellow-600 text-2xl">warning</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Confirmar devolución</h3>
                    <p class="text-sm text-gray-600 mt-2">
                        ¿Estás seguro de registrar esta devolución? El stock se actualizará automáticamente.
                    </p>
                </div>
                <div class="bg-gray-50 px-6 py-4 flex flex-col gap-3 sm:flex-row-reverse">
                    <button wire:click="performReturn"
                        class="w-full sm:w-auto px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-blue-700 transition">
                        Sí, registrar
                    </button>
                    <button @click="show = false"
                        class="w-full sm:w-auto px-5 py-2.5 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div x-data="{ toast: null, toastType: null, toastMessage: '' }"
        x-on:show-toast.window="toast = true; toastType = $event.detail.type; toastMessage = $event.detail.message; setTimeout(() => toast = false, 3500)"
        x-show="toast" x-cloak class="fixed bottom-5 right-5 z-50 transition-all duration-300"
        x-transition:enter="transform ease-out duration-300"
        x-transition:enter-start="translate-y-2 opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transform ease-in duration-200"
        x-transition:leave-start="translate-y-0 opacity-100"
        x-transition:leave-end="translate-y-2 opacity-0"
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

    <style>
        [x-cloak] { display: none !important; }
    </style>
</div>