<div class="max-w-7xl mx-auto">
    <!-- Tarjeta principal -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <!-- Encabezado con fondo sutil -->
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                    <span class="material-symbols-outlined text-gray-500">shopping_cart</span>
                    Historial de Compras
                </h1>
                <p class="text-sm text-gray-500 mt-1">Facturas y movimientos de adquisición</p>
            </div>
            <a href="{{ route('purchases.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 transition">
                <span class="material-symbols-outlined text-base">add_circle</span>
                Nueva Compra
            </a>
        </div>

        <!-- Contenido -->
        <div class="p-6 space-y-5">
            <!-- Filtros (ancho completo) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar por factura o proveedor..."
                        class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                </div>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">calendar_today</span>
                    <input type="date" wire:model.live="dateFrom"
                        class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                </div>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">event</span>
                    <input type="date" wire:model.live="dateTo"
                        class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                </div>
            </div>

            <!-- Tabla de compras -->
            <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">
                                <div class="flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">receipt_long</span>
                                    Factura
                                </div>
                            </th>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">
                                <div class="flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">warehouse</span>
                                    Proveedor
                                </div>
                            </th>
                            <th class="px-4 py-3 text-center text-gray-600 font-medium">
                                <div class="flex items-center justify-center gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">calendar_month</span>
                                    Fecha
                                </div>
                            </th>
                            <th class="px-4 py-3 text-right text-gray-600 font-medium">
                                <div class="flex items-center justify-end gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">attach_money</span>
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
                            <th class="px-4 py-3 text-center text-gray-600 font-medium">
                                <div class="flex items-center justify-center gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">settings</span>
                                    Acciones
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($purchases as $purchase)
                        <tr class="hover:bg-gray-50/80 transition">
                            <td class="px-4 py-3 font-mono text-xs text-gray-700">{{ $purchase->invoice_number }}</td>
                            <td class="px-4 py-3 text-gray-800">{{ $purchase->supplier->name }}</td>
                            <td class="px-4 py-3 text-center text-gray-700">
                                {{ $purchase->purchase_date->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3 text-right text-gray-700">
                                ${{ number_format($purchase->subtotal ?? 0, 2) }}
                            </td>
                            <td class="px-4 py-3 text-right text-gray-700">
                                ${{ number_format($purchase->iva_amount ?? 0, 2) }}
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-gray-800">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-gray-50 rounded-full text-xs font-medium text-gray-700">
                                    ${{ number_format($purchase->total ?? 0, 2) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('purchases.show', $purchase->id) }}"
                                        class="p-1.5 text-green-600 hover:bg-green-50 rounded-lg transition" title="Ver detalle">
                                        <span class="material-symbols-outlined text-lg">visibility</span>
                                    </a>
                                    <button type="button" onclick="printPurchase({{ $purchase->id }})"
                                        class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Imprimir">
                                        <span class="material-symbols-outlined text-lg">print</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center bg-gray-50/50">
                                <span class="material-symbols-outlined text-gray-300 text-4xl mb-2">inbox</span>
                                <p class="text-gray-500">No hay compras registradas</p>
                                <p class="text-sm text-gray-400 mt-1">Haz clic en "Nueva Compra" para registrar una</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            @if($purchases->hasPages())
            <div class="mt-5">
                {{ $purchases->links() }}
            </div>
            @endif

            <!-- Mensajes de sesión (exactamente igual que en movements y products) -->
            @if(session('message'))
            <div class="flex items-center gap-2 text-sm text-green-700 bg-green-50 px-4 py-3 rounded-lg border border-green-200">
                <span class="material-symbols-outlined text-green-600">check_circle</span>
                {{ session('message') }}
            </div>
            @endif
            @if(session('error'))
            <div class="flex items-center gap-2 text-sm text-red-700 bg-red-50 px-4 py-3 rounded-lg border border-red-200">
                <span class="material-symbols-outlined text-red-600">error</span>
                {{ session('error') }}
            </div>
            @endif
        </div>
    </div>

    <!-- Toast (consistente con el resto del sistema) -->
    <div x-data="{ toast: null, toastType: null, toastMessage: '' }"
        x-on:show-toast.window="toast = true; toastType = $event.detail.type; toastMessage = $event.detail.message; setTimeout(() => toast = false, 3500)"
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
        <div x-show="toastType === 'info'"
            class="bg-blue-600 text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-3">
            <span class="material-symbols-outlined">info</span>
            <span x-text="toastMessage" class="text-sm font-medium"></span>
        </div>
    </div>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</div>

<script>
    function printPurchase(id) {
        // Por ahora solo alert, luego se implementa el diseño de impresión
        alert('Funcionalidad de impresión en desarrollo para la compra #' + id);
    }
</script>