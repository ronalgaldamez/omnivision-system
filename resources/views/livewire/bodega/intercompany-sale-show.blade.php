<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900 flex items-center gap-2">
                <span class="material-symbols-outlined text-gray-500">point_of_sale</span>
                Venta {{ $sale->code }}
            </h1>
            <p class="text-sm text-gray-500 mt-1">Registrada {{ $sale->created_at->format('d/m/Y H:i') }} por {{ $sale->user?->name }}</p>
        </div>
        <x-ui.button variant="ghost" icon="arrow_back" href="{{ route('bodega.intercompany-sales.index') }}">Volver</x-ui.button>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 p-4 grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
        <div>
            <span class="text-xs text-gray-500">Vendedora</span>
            <p class="font-medium text-red-700">{{ $sale->sellerBranch?->name ?? '—' }}</p>
        </div>
        <div>
            <span class="text-xs text-gray-500">Compradora</span>
            <p class="font-medium text-green-700">{{ $sale->buyerBranch?->name ?? '—' }}</p>
        </div>
        <div>
            <span class="text-xs text-gray-500">Estado</span>
            <p><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusInfo[1] }}">{{ $statusInfo[0] }}</span></p>
        </div>
        <div>
            <span class="text-xs text-gray-500">Total</span>
            <p class="font-bold font-mono">${{ number_format($sale->total, 2) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <x-ui.card title="Progreso de la venta" icon="timeline">
                <div class="relative">
                    @foreach($steps as $step)
                        <div class="flex items-start gap-4 pb-8 relative {{ $loop->last ? 'pb-0' : '' }}">
                            @if(!$loop->last)
                                <div class="absolute left-[15px] top-8 bottom-0 w-0.5 {{ $step['isCompleted'] ? 'bg-blue-400' : 'bg-gray-200' }}"></div>
                            @endif
                            <div class="relative z-10 flex-shrink-0">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center shadow-sm
                                    {{ $step['isCompleted'] ? 'bg-blue-500 text-white' : ($step['isActive'] ? 'bg-blue-500 text-white animate-pulse' : 'bg-gray-200 text-gray-400') }}">
                                    <span class="material-symbols-outlined text-sm">{{ $step['isCompleted'] ? 'check' : $step['icon'] }}</span>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0 pt-1">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-semibold {{ $step['isCompleted'] || $step['isActive'] ? 'text-gray-800' : 'text-gray-400' }}">{{ $step['name'] }}</p>
                                    @if($step['timestamp'])
                                        <span class="text-xs text-gray-400">{{ $step['timestamp']->format('d/m/Y H:i') }}</span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-500 mt-0.5">{{ $step['description'] }}</p>
                                @if($step['user'])
                                    <p class="text-xs text-gray-400 mt-0.5">por {{ $step['user'] }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-ui.card>
        </div>

        <div class="space-y-6">
            <x-ui.card title="Acciones" icon="bolt">
                @if($sale->status === 'pending')
                    <button wire:click="markInTransit"
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
                        <span class="material-symbols-outlined text-base">local_shipping</span>
                        Marcar en tránsito
                    </button>
                    <p class="text-xs text-gray-400 mt-2">La vendedora despachó el material.</p>
                @elseif($sale->status === 'in_transit')
                    <button wire:click="markDelivered"
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-amber-600 text-white text-sm font-medium rounded-lg hover:bg-amber-700 transition">
                        <span class="material-symbols-outlined text-base">inventory_2</span>
                        Marcar entregado
                    </button>
                    <p class="text-xs text-gray-400 mt-2">El material llegó a la compradora.</p>
                @elseif($sale->status === 'delivered')
                    <button wire:click="confirm"
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition">
                        <span class="material-symbols-outlined text-base">check_circle</span>
                        Confirmar recepción (mover stock)
                    </button>
                    <p class="text-xs text-gray-400 mt-2">La compradora confirma que recibió. Recién acá se mueve el stock.</p>
                @else
                    <div class="bg-green-50 border border-green-200 rounded-lg p-3 text-sm text-green-700 flex items-center gap-2">
                        <span class="material-symbols-outlined">verified</span>
                        Venta confirmada el {{ $sale->confirmed_at?->format('d/m/Y H:i') }}
                    </div>
                @endif
            </x-ui.card>

            <x-ui.card title="Detalle" icon="receipt">
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500">Subtotal</span><span class="font-medium font-mono">${{ number_format($sale->subtotal, 2) }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">IVA (13%)</span><span class="font-medium font-mono">${{ number_format($sale->iva_amount, 2) }}</span></div>
                    <div class="flex justify-between pt-2 border-t border-gray-200 font-semibold"><span>Total</span><span class="font-mono">${{ number_format($sale->total, 2) }}</span></div>
                </div>

                @if($sale->items->isNotEmpty())
                    <div class="mt-4 overflow-x-auto rounded-lg border border-gray-200">
                        <table class="min-w-full text-sm">
                            <thead><tr class="bg-gray-50"><th class="px-3 py-2 text-left text-xs text-gray-600">Producto</th><th class="px-3 py-2 text-center text-xs text-gray-600">Cant.</th><th class="px-3 py-2 text-right text-xs text-gray-600">Costo</th></tr></thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($sale->items as $item)
                                    <tr>
                                        <td class="px-3 py-2 text-gray-800">{{ $item->product_name }}</td>
                                        <td class="px-3 py-2 text-center font-mono">{{ rtrim(rtrim(number_format($item->quantity, 4), '0'), '.') }}</td>
                                        <td class="px-3 py-2 text-right font-mono">${{ number_format($item->unit_cost, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-ui.card>
        </div>
    </div>
</div>
