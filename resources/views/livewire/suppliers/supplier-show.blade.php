<div>
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-xl font-semibold">Proveedor: {{ $supplier->name }}</h1>
        <a href="{{ route('suppliers.index') }}" class="text-blue-600">← Volver al listado</a>
    </div>

    <!-- Información del proveedor -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="grid grid-cols-2 gap-4">
            <div><span class="font-medium">Nombre:</span> {{ $supplier->name }}</div>
            <div><span class="font-medium">Contacto:</span> {{ $supplier->contact_name ?? '-' }}</div>
            <div><span class="font-medium">Teléfono:</span> {{ $supplier->phone ?? '-' }}</div>
            <div><span class="font-medium">Email:</span> {{ $supplier->email ?? '-' }}</div>
            <div><span class="font-medium">Dirección:</span> {{ $supplier->address ?? '-' }}</div>
            <div><span class="font-medium">NRC:</span> {{ $supplier->nrc ?? '-' }}</div>
            <div><span class="font-medium">NIT:</span> {{ $supplier->nit ?? '-' }}</div>
            <div><span class="font-medium">Cuentas bancarias:</span>
                @if($supplier->bank_accounts)
                    <ul class="list-disc list-inside">
                        @foreach($supplier->bank_accounts as $account)
                            <li>{{ $account['bank_name'] ?? '' }}: {{ $account['account_number'] ?? '' }}</li>
                        @endforeach
                    </ul>
                @else -
                @endif
            </div>
        </div>
    </div>

    <!-- Compras del proveedor -->
    <h2 class="text-lg font-semibold mb-2">Facturas / Compras</h2>
    <div class="overflow-x-auto bg-white rounded shadow mb-6">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left">Factura</th>
                    <th class="px-4 py-2 text-left">Fecha</th>
                    <th class="px-4 py-2 text-right">Subtotal</th>
                    <th class="px-4 py-2 text-right">IVA</th>
                    <th class="px-4 py-2 text-right">Total</th>
                    <th class="px-4 py-2 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchases as $purchase)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-2">{{ $purchase->invoice_number }}</td>
                        <td class="px-4 py-2">{{ $purchase->purchase_date->format('d/m/Y') }}</td>
                        <td class="px-4 py-2 text-right">${{ number_format($purchase->subtotal ?? 0, 2) }}</td>
                        <td class="px-4 py-2 text-right">${{ number_format($purchase->iva_amount ?? 0, 2) }}</td>
                        <td class="px-4 py-2 text-right">${{ number_format($purchase->total ?? 0, 2) }}</td>
                        <td class="px-4 py-2 text-center">
                            <button wire:click="showPurchaseDetail({{ $purchase->id }})" class="text-blue-600">Ver
                                detalle</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">No hay compras registradas</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $purchases->links() }}

    <!-- Movimientos vinculados -->
    <h2 class="text-lg font-semibold mb-2">Movimientos de inventario (compras)</h2>
    <div class="overflow-x-auto bg-white rounded shadow">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left">Fecha</th>
                    <th class="px-4 py-2 text-left">Producto</th>
                    <th class="px-4 py-2 text-center">Tipo</th>
                    <th class="px-4 py-2 text-right">Cantidad</th>
                    <th class="px-4 py-2 text-left">Usuario</th>
                </tr>
            </thead>
            <tbody>
                @forelse($movements as $mov)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-2">{{ $mov->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-2">{{ $mov->product->name }}</td>
                        <td class="px-4 py-2 text-center">{{ $mov->type }}</td>
                        <td class="px-4 py-2 text-right">{{ $mov->quantity }}</td>
                        <td class="px-4 py-2">{{ $mov->user->name }}</td>
                    </tr>
                @empty
                    <td>
                    <td colspan="5" class="text-center py-4">No hay movimientos asociados</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $movements->links() }}

    <!-- Modal de detalle de compra -->
    @if($purchaseModalOpen && $selectedPurchase)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" x-data="{ open: true }"
            x-show="open" x-cloak>
            <div class="relative top-20 mx-auto p-5 border w-full max-w-3xl shadow-lg rounded-md bg-white">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Detalle de compra - {{ $selectedPurchase->invoice_number }}</h3>
                    <button @click="$wire.closeModal()" class="text-gray-500 hover:text-gray-700">&times;</button>
                </div>
                <div class="mb-2"><strong>Proveedor:</strong> {{ $selectedPurchase->supplier->name }}</div>
                <div class="mb-2"><strong>Fecha:</strong> {{ $selectedPurchase->purchase_date->format('d/m/Y') }}</div>
                <div class="mb-2"><strong>Notas:</strong> {{ $selectedPurchase->notes ?? 'Sin notas' }}</div>
                <table class="min-w-full text-sm border">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left">Producto</th>
                            <th class="px-3 py-2 text-center">Cantidad</th>
                            <th class="px-3 py-2 text-center">Costo unitario</th>
                            <th class="px-3 py-2 text-center">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($selectedPurchase->items as $item)
                            <tr class="border-b">
                                <td class="px-3 py-2">{{ $item->product->name }} ({{ $item->product->sku }})</td>
                                <td class="px-3 py-2 text-center">{{ $item->quantity }}</td>
                                <td class="px-3 py-2 text-center">${{ number_format($item->unit_cost, 4) }}</td>
                                <td class="px-3 py-2 text-center">${{ number_format($item->quantity * $item->unit_cost, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="mt-4 text-right">
                    <p><strong>Subtotal:</strong> ${{ number_format($selectedPurchase->subtotal, 2) }}</p>
                    <p><strong>IVA (13%):</strong> ${{ number_format($selectedPurchase->iva_amount, 2) }}</p>
                    <p><strong>Total:</strong> ${{ number_format($selectedPurchase->total, 2) }}</p>
                </div>
                <div class="flex justify-end mt-4">
                    <button @click="$wire.closeModal()" class="px-3 py-1 bg-gray-300 rounded">Cerrar</button>
                </div>
            </div>
        </div>
    @endif
</div>