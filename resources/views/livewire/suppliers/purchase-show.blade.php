<div class="max-w-4xl mx-auto">
    <!-- Tarjeta principal -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <!-- Encabezado con fondo sutil -->
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                    <span class="material-symbols-outlined text-gray-500">receipt_long</span>
                    Detalle de Compra
                </h1>
                <p class="text-sm text-gray-500 mt-1">Factura {{ $purchase->invoice_number }}</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('purchases.index') }}"
                    class="inline-flex items-center gap-1.5 text-sm text-blue-600 hover:text-blue-800 transition">
                    <span class="material-symbols-outlined text-base">arrow_back</span>
                    Volver al listado
                </a>
                <button onclick="window.print()"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-gray-700 transition">
                    <span class="material-symbols-outlined text-base">print</span>
                    Imprimir
                </button>
            </div>
        </div>

        <!-- Contenido -->
        <div class="p-6 space-y-6">
            <!-- Datos de la compra -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Factura -->
                <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100">
                    <span class="material-symbols-outlined text-gray-400">receipt</span>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Número de factura</p>
                        <p class="font-mono font-semibold text-gray-800">{{ $purchase->invoice_number }}</p>
                    </div>
                </div>
                <!-- Fecha de compra -->
                <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100">
                    <span class="material-symbols-outlined text-gray-400">calendar_month</span>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Fecha de compra</p>
                        <p class="text-gray-700">{{ $purchase->purchase_date->format('d/m/Y') }}</p>
                    </div>
                </div>
                <!-- Proveedor -->
                <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100">
                    <span class="material-symbols-outlined text-gray-400">warehouse</span>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Proveedor</p>
                        <p class="text-gray-700">{{ $purchase->supplier->name }}</p>
                    </div>
                </div>
                <!-- Registrado por -->
                <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100">
                    <span class="material-symbols-outlined text-gray-400">person</span>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Registrado por</p>
                        <p class="text-gray-700">{{ $purchase->user->name }}</p>
                    </div>
                </div>
                <!-- Notas (ocupa ancho completo) -->
                <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100 md:col-span-2">
                    <span class="material-symbols-outlined text-gray-400">sticky_note_2</span>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Notas</p>
                        <p class="text-gray-700">{{ $purchase->notes ?: 'Sin notas' }}</p>
                    </div>
                </div>
            </div>

            <!-- Productos comprados -->
            <div class="border-t border-gray-200 pt-5">
                <h2 class="text-md font-semibold text-gray-800 flex items-center gap-2 mb-3">
                    <span class="material-symbols-outlined text-gray-500">inventory_2</span>
                    Productos comprados
                </h2>
                <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">
                                    <div class="flex items-center gap-1.5">
                                        <span
                                            class="material-symbols-outlined text-gray-400 text-base">inventory_2</span>
                                        Producto (SKU)
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
                                        <span
                                            class="material-symbols-outlined text-gray-400 text-base">attach_money</span>
                                        Costo unitario
                                    </div>
                                </th>
                                <th class="px-4 py-3 text-center text-gray-600 font-medium">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <span class="material-symbols-outlined text-gray-400 text-base">payments</span>
                                        Total
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($purchase->items as $item)
                                <tr class="hover:bg-gray-50/80 transition">
                                    <td class="px-4 py-3 text-gray-800">
                                        {{ $item->product->name }}
                                        <span class="text-gray-500 text-xs ml-1">({{ $item->product->sku }})</span>
                                    </td>
                                    <td class="px-4 py-3 text-center text-gray-700">{{ $item->quantity }}</td>
                                    <td class="px-4 py-3 text-center text-gray-700">
                                        ${{ number_format($item->unit_cost, 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-center font-medium text-gray-800">
                                        ${{ number_format($item->quantity * $item->unit_cost, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Resumen de pago -->
            <div class="border-t border-gray-200 pt-5">
                <h2 class="text-md font-semibold text-gray-800 flex items-center gap-2 mb-3">
                    <span class="material-symbols-outlined text-gray-500">payment</span>
                    Resumen de pago
                </h2>
                <div class="flex justify-end">
                    <div class="w-full sm:w-72 space-y-3 p-4 bg-gray-50/80 rounded-xl border border-gray-200">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Subtotal</span>
                            <span class="font-medium">${{ number_format($purchase->subtotal ?? 0, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">IVA (13%)</span>
                            <span class="font-medium">${{ number_format($purchase->iva_amount ?? 0, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-base font-semibold border-t border-gray-200 pt-3">
                            <span>Total a Pagar</span>
                            <span>${{ number_format($purchase->total ?? 0, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>