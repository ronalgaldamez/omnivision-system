<div class="max-w-6xl mx-auto">
    <!-- Tarjeta principal -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <!-- Encabezado con fondo sutil -->
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                        <span class="material-symbols-outlined text-gray-500">add_shopping_cart</span>
                        Nueva Compra
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">Registra una nueva factura de adquisición</p>
                </div>
                <a href="{{ route('purchases.index') }}"
                    class="inline-flex items-center gap-1.5 text-sm text-blue-600 hover:text-blue-800 transition">
                    <span class="material-symbols-outlined text-base">arrow_back</span>
                    Volver al listado
                </a>
            </div>
        </div>

        <!-- Contenido -->
        <div class="p-6">
            <form wire:submit.prevent="save" class="space-y-6">
                <!-- Datos de compra -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Proveedor con búsqueda -->
                    <div class="relative">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-gray-400 text-base">warehouse</span>
                            Proveedor *
                        </label>
                        <div class="relative">
                            <input type="text" wire:model.live.debounce.300ms="supplierSearch"
                                placeholder="Buscar por nombre, NIT o NRC..."
                                class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                            <span
                                class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                        </div>
                        @if(count($supplierResults) > 0)
                            <ul
                                class="absolute z-10 mt-1 w-full bg-white rounded-lg border border-gray-200 shadow-lg max-h-56 overflow-auto divide-y divide-gray-100">
                                @foreach($supplierResults as $supplier)
                                    <li wire:click="selectSupplier({{ $supplier->id }})"
                                        class="px-4 py-2.5 hover:bg-blue-50 cursor-pointer transition text-sm flex items-center justify-between">
                                        <div>
                                            <span class="font-medium text-gray-800">{{ $supplier->name }}</span>
                                        </div>
                                        <span class="text-xs text-gray-500">NIT: {{ $supplier->nit ?? 'N/A' }} | NRC:
                                            {{ $supplier->nrc ?? 'N/A' }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                        @error('supplier_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Número de factura -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-gray-400 text-base">receipt</span>
                            Número de factura *
                        </label>
                        <div class="relative">
                            <input type="text" wire:model="invoice_number"
                                class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm"
                                placeholder="Ej: FAC-001">
                            <span
                                class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">tag</span>
                        </div>
                        @error('invoice_number') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Fecha de compra -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-gray-400 text-base">calendar_month</span>
                            Fecha de compra *
                        </label>
                        <div class="relative">
                            <input type="date" wire:model="purchase_date"
                                class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                            <span
                                class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">event</span>
                        </div>
                        @error('purchase_date') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Notas -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-gray-400 text-base">sticky_note_2</span>
                            Notas
                        </label>
                        <div class="relative">
                            <textarea wire:model="notes" rows="1"
                                class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm resize-none"
                                placeholder="Notas o comentarios de la compra"></textarea>
                            <span
                                class="material-symbols-outlined absolute left-3 top-2.5 text-gray-400 text-lg">edit_note</span>
                        </div>
                    </div>
                </div>

                <!-- Agregar productos -->
                <div class="border-t border-gray-200 pt-6">
                    <h2 class="text-md font-semibold text-gray-800 flex items-center gap-2 mb-4">
                        <span class="material-symbols-outlined text-gray-500">inventory_2</span>
                        Productos de la compra
                    </h2>

                    <!-- Campos para agregar un producto -->
                    <div class="bg-gray-50/80 rounded-xl border border-gray-200 p-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Búsqueda de producto -->
                            <div class="relative">
                                <label class="block text-xs font-medium text-gray-600 mb-1 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-gray-400 text-sm">inventory_2</span>
                                    Producto
                                </label>
                                <div class="relative">
                                    <input type="text" wire:model.live.debounce.300ms="currentProductSearch"
                                        placeholder="Buscar por nombre o SKU..."
                                        class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                                    <span
                                        class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                                </div>
                                @if(count($productSearchResults) > 0)
                                    <ul
                                        class="absolute z-10 mt-1 w-full bg-white rounded-lg border border-gray-200 shadow-lg max-h-56 overflow-auto divide-y divide-gray-100">
                                        @foreach($productSearchResults as $result)
                                            <li wire:click="selectProduct({{ $result->id }})"
                                                class="px-4 py-2.5 hover:bg-blue-50 cursor-pointer transition text-sm flex items-center justify-between">
                                                <span class="font-medium text-gray-800">{{ $result->name }}</span>
                                                <span class="text-xs text-gray-500">({{ $result->sku }})</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                                @error('currentProductId') <span
                                class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- Cantidad -->
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-gray-400 text-sm">numbers</span>
                                    Cantidad
                                </label>
                                <div class="relative">
                                    <input type="number" step="any" wire:model="currentQuantity"
                                        class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                                    <span
                                        class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">tag</span>
                                </div>
                                @error('currentQuantity') <span
                                class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- Costo unitario -->
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-gray-400 text-sm">attach_money</span>
                                    Costo unitario
                                </label>
                                <div class="relative">
                                    <input type="number" step="0.01" wire:model="currentUnitCost"
                                        class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                                    <span
                                        class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">payments</span>
                                </div>
                                @error('currentUnitCost') <span
                                class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <button type="button" wire:click="addItem"
                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 transition">
                                <span class="material-symbols-outlined text-base">add_circle</span>
                                Agregar producto
                            </button>
                        </div>
                    </div>

                    <!-- Tabla de productos agregados -->
                    @if(count($items) > 0)
                        <div class="mt-5 space-y-4">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-medium text-gray-700 flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-gray-500 text-base">list_alt</span>
                                    Productos agregados ({{ count($items) }})
                                </h3>
                            </div>
                            <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                                <table class="min-w-full text-sm">
                                    <thead>
                                        <tr class="bg-gray-50 border-b border-gray-200">
                                            <th class="px-4 py-3 text-left text-gray-600 font-medium">
                                                <div class="flex items-center gap-1.5">
                                                    <span
                                                        class="material-symbols-outlined text-gray-400 text-base">inventory_2</span>
                                                    Producto
                                                </div>
                                            </th>
                                            <th class="px-4 py-3 text-center text-gray-600 font-medium">
                                                <div class="flex items-center justify-center gap-1.5">
                                                    <span
                                                        class="material-symbols-outlined text-gray-400 text-base">numbers</span>
                                                    Cantidad
                                                </div>
                                            </th>
                                            <th class="px-4 py-3 text-center text-gray-600 font-medium">
                                                <div class="flex items-center justify-center gap-1.5">
                                                    <span
                                                        class="material-symbols-outlined text-gray-400 text-base">attach_money</span>
                                                    Costo unitario
                                                </div>
                                            </th>
                                            <th class="px-4 py-3 text-center text-gray-600 font-medium">
                                                <div class="flex items-center justify-center gap-1.5">
                                                    <span
                                                        class="material-symbols-outlined text-gray-400 text-base">payments</span>
                                                    Total
                                                </div>
                                            </th>
                                            <th class="px-4 py-3 text-center text-gray-600 font-medium">
                                                <div class="flex items-center justify-center gap-1.5">
                                                    <span
                                                        class="material-symbols-outlined text-gray-400 text-base">settings</span>
                                                    Acciones
                                                </div>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach($items as $index => $item)
                                            <tr class="hover:bg-gray-50/80 transition">
                                                <td class="px-4 py-3 text-gray-800">
                                                    {{ $item['product_name'] }}
                                                    <span class="text-gray-500 text-xs ml-1">({{ $item['product_sku'] }})</span>
                                                </td>
                                                <td class="px-4 py-3 text-center text-gray-700">{{ $item['quantity'] }}</td>
                                                <td class="px-4 py-3 text-center text-gray-700">
                                                    ${{ number_format($item['unit_cost'], 2) }}
                                                </td>
                                                <td class="px-4 py-3 text-center font-medium text-gray-800">
                                                    ${{ number_format($item['quantity'] * $item['unit_cost'], 2) }}
                                                </td>
                                                <td class="px-4 py-3 text-center">
                                                    <div class="flex items-center justify-center gap-1">
                                                        <button type="button" wire:click="confirmAction('edit', {{ $index }})"
                                                            class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition"
                                                            title="Editar">
                                                            <span class="material-symbols-outlined text-lg">edit</span>
                                                        </button>
                                                        <button type="button" wire:click="confirmAction('delete', {{ $index }})"
                                                            class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition"
                                                            title="Eliminar">
                                                            <span class="material-symbols-outlined text-lg">delete</span>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="bg-gray-50 border-t border-gray-200">
                                            <td colspan="3" class="px-4 py-3 text-right font-medium text-gray-700">
                                                Total compra:
                                            </td>
                                            <td class="px-4 py-3 text-center font-bold text-gray-800">
                                                ${{ number_format(array_sum(array_map(fn($i) => $i['quantity'] * $i['unit_cost'], $items)), 2) }}
                                            </td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <!-- Resumen de pago -->
                            <div class="flex justify-end">
                                <div class="w-full sm:w-72 space-y-3 p-4 bg-gray-50/80 rounded-xl border border-gray-200">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Subtotal</span>
                                        <span class="font-medium">${{ number_format($subtotal, 2) }}</span>
                                    </div>
                                    <div class="flex items-center justify-between text-sm">
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" wire:model.live="includeIva"
                                                class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-2 focus:ring-blue-500/20">
                                            <span class="text-gray-600">Incluye IVA (13%)</span>
                                        </label>
                                        <span class="font-medium">${{ number_format($ivaAmount, 2) }}</span>
                                    </div>
                                    <div class="flex justify-between text-base font-semibold border-t border-gray-200 pt-3">
                                        <span>Total a Pagar</span>
                                        <span>${{ number_format($total, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="bg-gray-50/50 rounded-xl border border-dashed border-gray-300 py-12 text-center mt-5">
                            <span class="material-symbols-outlined text-gray-300 text-5xl mb-3">inbox</span>
                            <p class="text-gray-500">No hay productos agregados.</p>
                            <p class="text-sm text-gray-400 mt-1">Usa el formulario para agregar productos a la compra.</p>
                        </div>
                    @endif
                    @error('items') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Botones de acción -->
                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('purchases.index') }}"
                        class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-300 transition shadow-sm">
                        Cancelar
                    </a>
                    <button type="submit"
                        class="px-5 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 transition inline-flex items-center gap-2">
                        <span class="material-symbols-outlined text-base">save</span>
                        Registrar Compra
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal de confirmación de acción (editar/eliminar) -->
    <div x-data="{ show: @entangle('showConfirmModal') }" x-show="show" x-cloak
        class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center"
        style="display: none;">
        <div class="relative mx-auto p-5 w-full max-w-md">
            <div class="bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden">
                <div class="p-6 text-center">
                    <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-blue-100 mb-4">
                        <span class="material-symbols-outlined text-blue-600 text-2xl">help</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Confirmar acción</h3>
                    <p class="text-sm text-gray-600 mt-2">{{ $modalMessage }}</p>
                </div>
                <div class="bg-gray-50 px-6 py-4 flex flex-col gap-3 sm:flex-row-reverse">
                    <button wire:click="executeAction"
                        class="w-full sm:w-auto px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-blue-700 transition">
                        Sí, continuar
                    </button>
                    <button @click="show = false" wire:click="closeModal"
                        class="w-full sm:w-auto px-5 py-2.5 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación de guardado -->
    <div x-data="{ showSaveModal: false }" x-on:confirm-save.window="showSaveModal = true" x-show="showSaveModal"
        x-cloak
        class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center"
        style="display: none;">
        <div class="relative mx-auto p-5 w-full max-w-md">
            <div class="bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden">
                <div class="p-6 text-center">
                    <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-green-100 mb-4">
                        <span class="material-symbols-outlined text-green-600 text-2xl">shopping_cart_checkout</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Confirmar registro</h3>
                    <p class="text-sm text-gray-600 mt-2">
                        ¿Estás seguro de registrar esta compra? Los movimientos de stock se actualizarán
                        automáticamente.
                    </p>
                </div>
                <div class="bg-gray-50 px-6 py-4 flex flex-col gap-3 sm:flex-row-reverse">
                    <button wire:click="confirmSave"
                        class="w-full sm:w-auto px-5 py-2.5 bg-green-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-green-700 transition">
                        Sí, registrar compra
                    </button>
                    <button @click="showSaveModal = false"
                        class="w-full sm:w-auto px-5 py-2.5 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div x-data="{ toast: null, toastType: null, toastMessage: '' }"
        x-on:show-toast.window="toast = true; toastType = $event.detail.type; toastMessage = $event.detail.message; setTimeout(() => toast = false, 5000)"
        x-show="toast" x-cloak class="fixed bottom-5 right-5 z-50 transition-all duration-300"
        x-transition:enter="transform ease-out duration-300" x-transition:enter-start="translate-y-2 opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100" x-transition:leave="transform ease-in duration-200"
        x-transition:leave-start="translate-y-0 opacity-100" x-transition:leave-end="translate-y-2 opacity-0"
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
        [x-cloak] {
            display: none !important;
        }
    </style>
</div>