<div class="max-w-7xl mx-auto">
    <!-- Tarjeta principal -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <!-- Encabezado con fondo sutil -->
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                    <span class="material-symbols-outlined text-gray-500">warehouse</span>
                    Proveedor: {{ $supplier->name }}
                </h1>
                <p class="text-sm text-gray-500 mt-1">Información del proveedor y su actividad</p>
            </div>
            <a href="{{ route('suppliers.index') }}"
                class="inline-flex items-center gap-1.5 text-sm text-blue-600 hover:text-blue-800 transition">
                <span class="material-symbols-outlined text-base">arrow_back</span>
                Volver al listado
            </a>
        </div>

        <!-- Contenido -->
        <div class="p-6 space-y-6">
            <!-- Información del proveedor -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <!-- Detalles en tarjetas -->
                <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100">
                    <span class="material-symbols-outlined text-gray-400">business</span>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Nombre</p>
                        <p class="font-semibold text-gray-800">{{ $supplier->name }}</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100">
                    <span class="material-symbols-outlined text-gray-400">person</span>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Contacto</p>
                        <p class="text-gray-700">{{ $supplier->contact_name ?? '—' }}</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100">
                    <span class="material-symbols-outlined text-gray-400">call</span>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Teléfono</p>
                        <p class="text-gray-700">{{ $supplier->phone ?? '—' }}</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100">
                    <span class="material-symbols-outlined text-gray-400">mail</span>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Email</p>
                        <p class="text-gray-700">{{ $supplier->email ?? '—' }}</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100">
                    <span class="material-symbols-outlined text-gray-400">location_on</span>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Dirección</p>
                        <p class="text-gray-700">{{ $supplier->address ?? '—' }}</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100">
                    <span class="material-symbols-outlined text-gray-400">description</span>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Documentos</p>
                        <p class="text-gray-700">
                            NRC: {{ $supplier->nrc ?? '—' }} / NIT: {{ $supplier->nit ?? '—' }}
                        </p>
                    </div>
                </div>
                <!-- Cuentas bancarias -->
                <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100 md:col-span-2">
                    <span class="material-symbols-outlined text-gray-400">account_balance</span>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Cuentas bancarias</p>
                        @if($supplier->bank_accounts)
                            <ul class="list-disc list-inside text-gray-700">
                                @foreach($supplier->bank_accounts as $account)
                                    <li>{{ $account['bank_name'] ?? '' }}: {{ $account['account_number'] ?? '' }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-gray-700">—</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Compras del proveedor -->
            <div class="border-t border-gray-200 pt-6">
                <h2 class="text-md font-semibold text-gray-800 flex items-center gap-2 mb-3">
                    <span class="material-symbols-outlined text-gray-500">receipt_long</span>
                    Facturas / Compras
                </h2>
                <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">
                                    <div class="flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-gray-400 text-base">receipt</span>
                                        Factura
                                    </div>
                                </th>
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">
                                    <div class="flex items-center gap-1.5">
                                        <span
                                            class="material-symbols-outlined text-gray-400 text-base">calendar_month</span>
                                        Fecha
                                    </div>
                                </th>
                                <th class="px-4 py-3 text-right text-gray-600 font-medium">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <span
                                            class="material-symbols-outlined text-gray-400 text-base">attach_money</span>
                                        Subtotal
                                    </div>
                                </th>
                                <th class="px-4 py-3 text-right text-gray-600 font-medium">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <span class="material-symbols-outlined text-gray-400 text-base">percent</span>
                                        IVA
                                    </div>
                                </th>
                                <th class="px-4 py-3 text-right text-gray-600 font-medium">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <span class="material-symbols-outlined text-gray-400 text-base">payments</span>
                                        Total
                                    </div>
                                </th>
                                <th class="px-4 py-3 text-center text-gray-600 font-medium">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($purchases as $purchase)
                                <tr class="hover:bg-gray-50/80 transition">
                                    <td class="px-4 py-3 font-mono text-xs text-gray-700">{{ $purchase->invoice_number }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">{{ $purchase->purchase_date->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3 text-right text-gray-700">
                                        ${{ number_format($purchase->subtotal ?? 0, 2) }}</td>
                                    <td class="px-4 py-3 text-right text-gray-700">
                                        ${{ number_format($purchase->iva_amount ?? 0, 2) }}</td>
                                    <td class="px-4 py-3 text-right text-gray-800 font-semibold">
                                        ${{ number_format($purchase->total ?? 0, 2) }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <button wire:click="showPurchaseDetail({{ $purchase->id }})"
                                            class="text-blue-600 hover:text-blue-800 font-medium text-sm">
                                            Ver detalle
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-12 text-center bg-gray-50/50">
                                        <span
                                            class="material-symbols-outlined text-gray-300 text-3xl mb-2">receipt_long</span>
                                        <p class="text-gray-500">No hay compras registradas</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $purchases->links() }}</div>
            </div>

            <!-- Movimientos vinculados -->
            <div class="border-t border-gray-200 pt-6">
                <h2 class="text-md font-semibold text-gray-800 flex items-center gap-2 mb-3">
                    <span class="material-symbols-outlined text-gray-500">inventory</span>
                    Movimientos de inventario (compras)
                </h2>
                <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">
                                    <div class="flex items-center gap-1.5">
                                        <span
                                            class="material-symbols-outlined text-gray-400 text-base">calendar_month</span>
                                        Fecha
                                    </div>
                                </th>
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">
                                    <div class="flex items-center gap-1.5">
                                        <span
                                            class="material-symbols-outlined text-gray-400 text-base">inventory_2</span>
                                        Producto
                                    </div>
                                </th>
                                <th class="px-4 py-3 text-center text-gray-600 font-medium">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <span class="material-symbols-outlined text-gray-400 text-base">swap_vert</span>
                                        Tipo
                                    </div>
                                </th>
                                <th class="px-4 py-3 text-right text-gray-600 font-medium">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <span class="material-symbols-outlined text-gray-400 text-base">numbers</span>
                                        Cantidad
                                    </div>
                                </th>
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">
                                    <div class="flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-gray-400 text-base">person</span>
                                        Usuario
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($movements as $mov)
                                <tr class="hover:bg-gray-50/80 transition">
                                    <td class="px-4 py-3 font-mono text-xs text-gray-700">
                                        {{ $mov->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-4 py-3 text-gray-800">{{ $mov->product->name }}</td>
                                    <td class="px-4 py-3 text-center text-gray-700">{{ $mov->type }}</td>
                                    <td class="px-4 py-3 text-right text-gray-800">{{ $mov->quantity }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ $mov->user->name }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-12 text-center bg-gray-50/50">
                                        <span class="material-symbols-outlined text-gray-300 text-3xl mb-2">inventory</span>
                                        <p class="text-gray-500">No hay movimientos asociados</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $movements->links() }}</div>
            </div>
        </div>
    </div>

    <!-- Modal de detalle de compra (estilo unificado) -->
    @if($purchaseModalOpen && $selectedPurchase)
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center"
            x-data="{ open: true }" x-show="open" x-cloak>
            <div class="relative mx-auto p-5 w-full max-w-3xl">
                <div class="bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                        <h3 class="text-lg font-semibold flex items-center gap-2">
                            <span class="material-symbols-outlined text-gray-500">receipt</span>
                            Detalle de compra - {{ $selectedPurchase->invoice_number }}
                        </h3>
                        <button @click="$wire.closeModal()" class="text-gray-400 hover:text-gray-600 transition">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>
                    <div class="p-5 space-y-3">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="flex items-center gap-2 text-sm">
                                <span class="material-symbols-outlined text-gray-400">warehouse</span>
                                <strong>Proveedor:</strong> {{ $selectedPurchase->supplier->name }}
                            </div>
                            <div class="flex items-center gap-2 text-sm">
                                <span class="material-symbols-outlined text-gray-400">calendar_month</span>
                                <strong>Fecha:</strong> {{ $selectedPurchase->purchase_date->format('d/m/Y') }}
                            </div>
                            <div class="flex items-center gap-2 text-sm sm:col-span-2">
                                <span class="material-symbols-outlined text-gray-400">sticky_note_2</span>
                                <strong>Notas:</strong> {{ $selectedPurchase->notes ?? 'Sin notas' }}
                            </div>
                        </div>
                        <div class="overflow-x-auto rounded-lg border border-gray-200">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left">Producto</th>
                                        <th class="px-4 py-2 text-center">Cantidad</th>
                                        <th class="px-4 py-2 text-center">Costo unitario</th>
                                        <th class="px-4 py-2 text-center">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($selectedPurchase->items as $item)
                                        <tr>
                                            <td class="px-4 py-2">{{ $item->product->name }} <span
                                                    class="text-gray-500 text-xs">({{ $item->product->sku }})</span></td>
                                            <td class="px-4 py-2 text-center">{{ $item->quantity }}</td>
                                            <td class="px-4 py-2 text-center">${{ number_format($item->unit_cost, 4) }}</td>
                                            <td class="px-4 py-2 text-center">
                                                ${{ number_format($item->quantity * $item->unit_cost, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="flex justify-end">
                            <div class="w-64 space-y-1 text-sm">
                                <div class="flex justify-between"><strong>Subtotal:</strong>
                                    ${{ number_format($selectedPurchase->subtotal, 2) }}</div>
                                <div class="flex justify-between"><strong>IVA (13%):</strong>
                                    ${{ number_format($selectedPurchase->iva_amount, 2) }}</div>
                                <div class="flex justify-between border-t pt-1 text-base font-bold"><strong>Total:</strong>
                                    ${{ number_format($selectedPurchase->total, 2) }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-gray-50 text-right">
                        <button @click="$wire.closeModal()"
                            class="px-5 py-2.5 bg-gray-200 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-300 transition">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>