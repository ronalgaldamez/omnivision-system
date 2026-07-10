<div class="max-w-6xl mx-auto">
    <x-ui.card title="Devolución a Proveedor" icon="assignment_return" subtitle="Registra la devolución de productos adquiridos" overflow="visible">
        <x-slot:headerActions>
            <x-ui.button variant="ghost" icon="arrow_back" href="{{ route('returns.index') }}">Volver al listado</x-ui.button>
        </x-slot:headerActions>

        <form wire:submit.prevent="confirmReturn" class="space-y-6">
            <div class="relative">
                <x-forms.label icon="warehouse" required>Proveedor</x-forms.label>
                <div class="relative">
                    <input type="text" wire:model.live.debounce.300ms="supplierSearch"
                        placeholder="Buscar por nombre, NIT o NRC..."
                        class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                </div>
                @if(count($supplierResults) > 0 && !$supplier_id)
                    <ul class="absolute z-10 mt-1 w-full bg-white rounded-lg border border-gray-200 shadow-lg max-h-56 overflow-auto divide-y divide-gray-100">
                        @foreach($supplierResults as $supplier)
                            <li wire:click="selectSupplier({{ $supplier->id }})"
                                class="px-4 py-2.5 hover:bg-blue-50 cursor-pointer transition text-sm flex items-center justify-between">
                                <div>
                                    <span class="font-medium text-gray-800">{{ $supplier->name }}</span>
                                </div>
                                <span class="text-xs text-gray-500">NIT: {{ $supplier->nit ?? 'N/A' }} | NRC: {{ $supplier->nrc ?? 'N/A' }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
                @error('supplier_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            @if($supplier_id)
                <div>
                    <x-forms.label icon="receipt" required>Factura / Compra</x-forms.label>
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

            @if($purchase_id && count($items) > 0)
                <div class="border-t border-gray-200 pt-6">
                    <h2 class="text-md font-semibold text-gray-800 flex items-center gap-2 mb-4">
                        <span class="material-symbols-outlined text-gray-500">tune</span>
                        Modo de devolución
                    </h2>
                    <div class="flex flex-wrap gap-4">
                        <label class="flex items-center gap-2 p-3 bg-gray-50/80 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-100 transition">
                            <input type="radio" wire:model.live="returnMode" value="full" class="text-blue-600 focus:ring-2 focus:ring-blue-500/20">
                            <span class="text-sm text-gray-700">Devolución completa</span>
                        </label>
                        <label class="flex items-center gap-2 p-3 bg-gray-50/80 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-100 transition">
                            <input type="radio" wire:model.live="returnMode" value="individual" class="text-blue-600 focus:ring-2 focus:ring-blue-500/20">
                            <span class="text-sm text-gray-700">Devolución individual</span>
                        </label>
                        <label class="flex items-center gap-2 p-3 bg-gray-50/80 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-100 transition">
                            <input type="radio" wire:model.live="returnMode" value="partial" class="text-blue-600 focus:ring-2 focus:ring-blue-500/20">
                            <span class="text-sm text-gray-700">Devolución parcial</span>
                        </label>
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-6">
                    <h2 class="text-md font-semibold text-gray-800 flex items-center gap-2 mb-4">
                        <span class="material-symbols-outlined text-gray-500">inventory_2</span>
                        Productos
                    </h2>
                    <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200">
                                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Producto (SKU)</th>
                                    <th class="px-4 py-3 text-center text-gray-600 font-medium">Comprado</th>
                                    <th class="px-4 py-3 text-center text-gray-600 font-medium">Devuelto</th>
                                    <th class="px-4 py-3 text-center text-gray-600 font-medium">Disponible</th>
                                    @if($returnMode == 'individual')
                                        <th class="px-4 py-3 text-center text-gray-600 font-medium">Devolver</th>
                                    @elseif($returnMode == 'partial')
                                        <th class="px-4 py-3 text-center text-gray-600 font-medium">Cantidad</th>
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
                                        <td class="px-4 py-3 text-center font-medium text-gray-800">{{ $item['available_quantity'] }}</td>
                                        @if($returnMode == 'individual')
                                            <td class="px-4 py-3 text-center">
                                                <input type="checkbox" value="{{ $item['id'] }}" wire:model="selectedItems"
                                                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-2 focus:ring-blue-500/20 w-4 h-4">
                                            </td>
                                        @elseif($returnMode == 'partial')
                                            <td class="px-4 py-3 text-center">
                                                <input type="number" min="0" max="{{ $item['available_quantity'] }}" step="1"
                                                    wire:model.lazy="partialQuantities.{{ $item['id'] }}"
                                                    wire:key="partial-qty-{{ $item['id'] }}"
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

            <div class="flex justify-end gap-3 pt-4">
                <x-ui.button variant="secondary" href="{{ route('returns.index') }}">Cancelar</x-ui.button>
                <x-ui.button variant="primary" icon="save" type="submit">Registrar Devolución</x-ui.button>
            </div>
        </form>
    </x-ui.card>

    {{-- Modal de confirmación --}}
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
                    <x-ui.button variant="primary" wire:click="performReturn">Sí, registrar</x-ui.button>
                    <button @click="show = false"
                        class="w-full sm:w-auto px-5 py-2.5 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
