@php
    use Milon\Barcode\DNS1D;
@endphp

<div class="max-w-4xl mx-auto">
    <!-- Tarjeta principal -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <!-- Encabezado con fondo sutil -->
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                    <span class="material-symbols-outlined text-gray-500">info</span>
                    Detalle del Producto
                </h1>
                <p class="text-sm text-gray-500 mt-1">Información completa del artículo</p>
            </div>
            <a href="{{ route('products.index') }}"
                class="inline-flex items-center gap-1.5 text-sm text-blue-600 hover:text-blue-800 transition">
                <span class="material-symbols-outlined text-base">arrow_back</span>
                Volver al listado
            </a>
        </div>

        <!-- Contenido -->
        <div class="p-6 space-y-6">
            <!-- Detalles del producto -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <!-- SKU -->
                <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100">
                    <span class="material-symbols-outlined text-gray-400">qr_code</span>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">SKU</p>
                        <p class="font-mono font-semibold text-gray-800 text-lg">{{ $product->sku }}</p>
                    </div>
                </div>
                <!-- Nombre -->
                <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100">
                    <span class="material-symbols-outlined text-gray-400">inventory_2</span>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Nombre</p>
                        <p class="font-semibold text-gray-800">{{ $product->name }}</p>
                    </div>
                </div>
                <!-- Medida -->
                <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100">
                    <span class="material-symbols-outlined text-gray-400">straighten</span>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Medida</p>
                        <p class="text-gray-700">
                            @if($product->unit_of_measure != 'unidad' && $product->measure_value)
                                {{ $product->measure_value }} {{ $product->unit_of_measure }}
                            @elseif($product->unit_of_measure != 'unidad' && !$product->measure_value)
                                {{ $product->unit_of_measure }}
                            @else
                                unidad
                            @endif
                        </p>
                    </div>
                </div>
                <!-- Stock actual -->
                <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100">
                    <span class="material-symbols-outlined text-gray-400">inventory</span>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Stock actual</p>
                        <p
                            class="{{ $product->current_stock <= $product->stock_min ? 'text-red-600 font-bold' : 'text-gray-700' }} text-lg">
                            {{ $product->current_stock }}
                        </p>
                    </div>
                </div>
                <!-- Stock mínimo -->
                <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100">
                    <span class="material-symbols-outlined text-gray-400">arrow_downward</span>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Stock mínimo</p>
                        <p class="text-gray-700">{{ $product->stock_min }}</p>
                    </div>
                </div>
                <!-- Stock máximo -->
                <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100">
                    <span class="material-symbols-outlined text-gray-400">arrow_upward</span>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Stock máximo</p>
                        <p class="text-gray-700">{{ $product->stock_max ?? '—' }}</p>
                    </div>
                </div>
                <!-- Descripción -->
                <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100 md:col-span-2">
                    <span class="material-symbols-outlined text-gray-400">description</span>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Descripción</p>
                        <p class="text-gray-700">{{ $product->description ?: 'Sin descripción' }}</p>
                    </div>
                </div>
            </div>

            {{-- Empaques --}}
            <div class="border-t border-gray-200 pt-6">
                <h2 class="text-md font-semibold text-gray-800 flex items-center gap-2 mb-4">
                    <span class="material-symbols-outlined text-gray-500">package_2</span>
                    Empaques
                </h2>

                @if(count($currentPackagings) > 0)
                <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm mb-4">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-2.5 text-left text-gray-600 font-medium">Tipo</th>
                                <th class="px-4 py-2.5 text-left text-gray-600 font-medium">Nombre</th>
                                <th class="px-4 py-2.5 text-center text-gray-600 font-medium">Unid. base</th>
                                <th class="px-4 py-2.5 text-center text-gray-600 font-medium">Default</th>
                                <th class="px-4 py-2.5 text-center text-gray-600 font-medium">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($currentPackagings as $pkg)
                            <tr class="hover:bg-gray-50/80 transition {{ $pkg->id == $currentPackagingId ? 'bg-blue-50/50' : '' }}">
                                <td class="px-4 py-2.5 text-gray-700">{{ $pkg->packagingType?->name ?? '—' }}</td>
                                <td class="px-4 py-2.5 text-gray-800">{{ $pkg->name }}</td>
                                <td class="px-4 py-2.5 text-center text-gray-700 font-mono">{{ rtrim(rtrim(number_format($pkg->quantity_in_base_unit, 4), '0'), '.') }}</td>
                                <td class="px-4 py-2.5 text-center">
                                    @if($pkg->is_default_for_purchase)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-green-50 text-green-700 rounded-full text-xs font-medium">
                                        <span class="material-symbols-outlined text-xs">check</span> Default
                                    </span>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <button type="button" wire:click="editPackaging({{ $pkg->id }})"
                                            class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Editar">
                                            <span class="material-symbols-outlined text-lg">edit</span>
                                        </button>
                                        <button type="button" wire:click="deletePackaging({{ $pkg->id }})"
                                            class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition"
                                            title="Eliminar"
                                            onclick="return confirm('¿Eliminar este empaque?')">
                                            <span class="material-symbols-outlined text-lg">delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="bg-gray-50/50 rounded-xl border border-dashed border-gray-300 py-8 text-center mb-4">
                    <span class="material-symbols-outlined text-gray-300 text-3xl mb-2">package_2</span>
                    <p class="text-gray-500 text-sm">Sin empaques definidos.</p>
                </div>
                @endif

                @if($showPackagingForm)
                <div class="space-y-2 p-4 bg-blue-50 rounded-xl border border-blue-100">
                    <p class="text-xs font-medium text-blue-700">{{ $editingPackagingId ? 'Editando empaque' : 'Nuevo empaque' }}</p>
                    <div class="flex items-end gap-2 flex-wrap">
                        <div class="flex-1 min-w-[150px]">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Tipo</label>
                            <select wire:model.live="newPackagingTypeId" class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm">
                                <option value="">Seleccioná...</option>
                                @foreach($packagingTypes as $pt)
                                    <option value="{{ $pt->id }}">{{ $pt->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Unidades que trae</label>
                            <input type="number" step="1" wire:model.live.debounce.500ms="newPackagingQuantity"
                                class="w-44 px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm" min="1">
                        </div>
                        <button type="button" wire:click="savePackaging"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition whitespace-nowrap">
                            {{ $editingPackagingId ? 'Actualizar' : 'Guardar' }}
                        </button>
                        <button type="button" wire:click="cancelEditPackaging"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 bg-white hover:bg-gray-50 transition">
                            Cancelar
                        </button>
                    </div>
                </div>
                @else
                <button type="button" wire:click="editPackaging(0)"
                    class="text-sm text-blue-600 hover:text-blue-800 transition flex items-center gap-1">
                    <span class="material-symbols-outlined text-base">add</span>
                    Agregar empaque
                </button>
                @endif
            </div>

            <!-- Código de barras -->
            <div class="border-t border-gray-200 pt-6">
                <h2 class="text-md font-semibold text-gray-800 flex items-center gap-2 mb-4">
                    <span class="material-symbols-outlined text-gray-500">barcode</span>
                    Código de barras
                </h2>
                <div class="flex flex-col items-center">
                    <div class="inline-block p-6 bg-white rounded-xl shadow-sm border border-gray-200">
                        @php
                            $barcode = new DNS1D();
                            $barcode->setStorPath(storage_path('framework/cache/'));
                            echo $barcode->getBarcodeHTML($product->sku, 'C128', 2.5, 70);
                        @endphp
                    </div>
                    <p class="text-xs text-gray-500 mt-3 flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">link</span>
                        Basado en SKU: <span class="font-mono">{{ $product->sku }}</span>
                    </p>
                </div>
            </div>

            <!-- Últimos movimientos -->
            <div class="border-t border-gray-200 pt-6">
                <h2 class="text-md font-semibold text-gray-800 flex items-center gap-2 mb-3">
                    <span class="material-symbols-outlined text-gray-500">history</span>
                    Últimos movimientos
                </h2>
                @if($product->movements->count())
                    <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-4 py-2.5 text-left text-gray-600 font-medium">
                                        <div class="flex items-center gap-1.5">
                                            <span
                                                class="material-symbols-outlined text-gray-400 text-base">calendar_month</span>
                                            Fecha
                                        </div>
                                    </th>
                                    <th class="px-4 py-2.5 text-left text-gray-600 font-medium">
                                        <div class="flex items-center gap-1.5">
                                            <span class="material-symbols-outlined text-gray-400 text-base">swap_vert</span>
                                            Tipo
                                        </div>
                                    </th>
                                    <th class="px-4 py-2.5 text-right text-gray-600 font-medium">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <span class="material-symbols-outlined text-gray-400 text-base">numbers</span>
                                            Cantidad
                                        </div>
                                    </th>
                                    <th class="px-4 py-2.5 text-left text-gray-600 font-medium">
                                        <div class="flex items-center gap-1.5">
                                            <span class="material-symbols-outlined text-gray-400 text-base">person</span>
                                            Usuario
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($product->movements->take(5) as $mov)
                                    <tr class="hover:bg-gray-50/80 transition">
                                        <td class="px-4 py-2.5 font-mono text-xs text-gray-700">
                                            {{ $mov->created_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="px-4 py-2.5">
                                            @if($mov->type == 'entry')
                                                <span
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 bg-green-50 text-green-700 rounded-full text-xs font-medium">
                                                    <span class="material-symbols-outlined text-xs">arrow_upward</span> Entrada
                                                </span>
                                            @elseif($mov->type == 'exit')
                                                <span
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 bg-red-50 text-red-700 rounded-full text-xs font-medium">
                                                    <span class="material-symbols-outlined text-xs">arrow_downward</span> Salida
                                                </span>
                                            @else
                                                <span class="text-gray-600 text-xs">{{ ucfirst($mov->type) }}</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2.5 text-right font-mono text-gray-800">{{ $mov->quantity }}</td>
                                        <td class="px-4 py-2.5 text-gray-700">{{ $mov->user->name }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($product->movements->count() > 5)
                        <p class="text-xs text-gray-400 mt-2 flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">info</span>
                            Mostrando 5 de {{ $product->movements->count() }} movimientos
                        </p>
                    @endif
                @else
                    <div class="bg-gray-50/50 rounded-xl border border-dashed border-gray-300 py-10 text-center">
                        <span class="material-symbols-outlined text-gray-300 text-4xl mb-2">history</span>
                        <p class="text-gray-500">No hay movimientos registrados</p>
                        <p class="text-sm text-gray-400 mt-1">Los movimientos de inventario aparecerán aquí</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>