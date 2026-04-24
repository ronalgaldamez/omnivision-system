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