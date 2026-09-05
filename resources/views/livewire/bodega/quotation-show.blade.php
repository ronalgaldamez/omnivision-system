<div class="max-w-6xl mx-auto space-y-6">
    {{-- Encabezado --}}
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 flex-wrap">
                <span class="material-symbols-outlined text-gray-500">request_quote</span>
                <h1 class="text-xl font-semibold text-gray-900">Cotización {{ $quotation->code }}</h1>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusInfo[1] }}">{{ $statusInfo[0] }}</span>
            </div>
            <p class="text-sm text-gray-500 mt-1">
                Creada por {{ $quotation->creator?->name }} el {{ $quotation->created_at->format('d/m/Y H:i') }} · {{ $quotation->branch?->name }}
            </p>
        </div>
        <x-ui.button variant="ghost" icon="arrow_back" href="{{ route('bodega.quotations.index') }}">Volver</x-ui.button>
    </div>

    @if($quotation->status === 'rejected')
        <x-ui.alert variant="danger" title="Cotización rechazada">
            {{ $quotation->rejection_reason ?: 'El gerente administrativo la rechazó sin especificar motivo.' }}
        </x-ui.alert>
    @endif

    {{-- Resumen --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 p-4 grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
        <div>
            <span class="text-xs text-gray-500">Proveedor</span>
            <p class="font-medium text-gray-800">{{ $quotation->supplier?->name ?? '—' }}</p>
            <p class="text-xs text-gray-400">NIT: {{ $quotation->supplier?->nit ?? 'N/A' }}</p>
        </div>
        <div>
            <span class="text-xs text-gray-500">Sucursal</span>
            <p class="font-medium text-gray-800">{{ $quotation->branch?->name ?? '—' }}</p>
        </div>
        <div>
            <span class="text-xs text-gray-500">Productos</span>
            <p class="font-medium text-gray-800">{{ $quotation->items->count() }}</p>
        </div>
        <div>
            <span class="text-xs text-gray-500">Total</span>
            <p class="font-bold font-mono text-gray-900">${{ number_format($quotation->total, 2) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
{{-- PART2 --}}
            {{-- Productos --}}
            <x-ui.card title="Productos a cotizar" subtitle="{{ $quotation->supplier?->name }}" icon="inventory_2">
                @if($quotation->items->isNotEmpty())
                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200">
                                    <th class="px-4 py-3 text-left text-gray-600 font-semibold text-xs uppercase tracking-wider">Producto</th>
                                    <th class="px-4 py-3 text-center text-gray-600 font-semibold text-xs uppercase tracking-wider">Cantidad</th>
                                    <th class="px-4 py-3 text-center text-gray-600 font-semibold text-xs uppercase tracking-wider">Costo unit.</th>
                                    <th class="px-4 py-3 text-center text-gray-600 font-semibold text-xs uppercase tracking-wider">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @foreach($quotation->items as $item)
                                    <tr>
                                        <td class="px-4 py-3 text-gray-800">
                                            @if($item->isPending())
                                                <span class="font-medium text-amber-700">{{ $item->pending_name }}</span>
                                                <span class="text-xs bg-amber-50 text-amber-600 border border-amber-200 px-1.5 py-0.5 rounded-full ml-1">nuevo propuesto</span>
                                                @if($item->pendingCategory)
                                                    <p class="text-xs text-gray-400 mt-0.5">Categoría propuesta: {{ $item->pendingCategory->name }}</p>
                                                @endif
                                            @else
                                                {{ $item->product?->name ?? 'Producto eliminado' }}
                                                @if($item->product)
                                                    <p class="text-xs text-gray-400 mt-0.5 font-mono">{{ $item->product->sku }}</p>
                                                @endif
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center text-gray-700">{{ rtrim(rtrim(number_format((float) $item->quantity, 4), '0'), '.') }}</td>
                                        <td class="px-4 py-3 text-center font-mono">${{ number_format((float) $item->unit_cost, 2) }}</td>
                                        <td class="px-4 py-3 text-center font-medium font-mono">${{ number_format((float) $item->quantity * (float) $item->unit_cost, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <x-ui.empty-state icon="inventory_2" title="Sin productos"
                        description="Esta cotización no tiene productos registrados." />
                @endif
            </x-ui.card>

            @php
                $next = match ($quotation->status) {
                    'draft' => ['Borrador', 'Cotización en espera. Completala y enviála a aprobación para que entre al flujo.', 'neutral'],
                    'pending' => ['Aprobación pendiente', 'El gerente administrativo debe aprobar o rechazar esta cotización desde la lista.', 'info'],
                    'approved' => ['Pago pendiente', 'El subgerente administrativo debe marcar la cotización como pagada.', 'warning'],
                    'paid' => ['Recepción pendiente', 'El bodeguero debe recibir la cotización; eso genera la compra y entra el stock.', 'success'],
                    default => null,
                };
            @endphp
            @if($next)
                <x-ui.alert :variant="$next[2]" :title="$next[0]">{{ $next[1] }}</x-ui.alert>
            @endif
        </div>
        <div class="space-y-6">
            {{-- Progreso --}}
            <x-ui.card title="Progreso" icon="timeline">
                <div class="relative">
                    @foreach($steps as $step)
                        @php
                            $isRed = ($step['tone'] ?? null) === 'red';
                            $isDone = $step['completed'];
                            $isActive = $step['active'];
                        @endphp
                        <div class="flex items-start gap-4 pb-8 relative {{ $loop->last ? 'pb-0' : '' }}">
                            @if(!$loop->last)
                                <div class="absolute left-[15px] top-8 bottom-0 w-0.5 {{ $isDone ? ($isRed ? 'bg-red-400' : 'bg-blue-400') : 'bg-gray-200' }}"></div>
                            @endif
                            <div class="relative z-10 flex-shrink-0">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center shadow-sm
                                    {{ $isDone ? ($isRed ? 'bg-red-500 text-white' : 'bg-blue-500 text-white') : ($isActive ? 'bg-blue-500 text-white animate-pulse' : 'bg-gray-200 text-gray-400') }}">
                                    <span class="material-symbols-outlined text-sm">{{ $isDone ? 'check' : $step['icon'] }}</span>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0 pt-1">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-sm font-semibold {{ $isDone || $isActive ? ($isRed ? 'text-red-700' : 'text-gray-800') : 'text-gray-400' }}">{{ $step['name'] }}</p>
                                    @if($step['timestamp'])
                                        <span class="text-xs text-gray-400 flex-shrink-0">{{ $step['timestamp']->format('d/m/Y H:i') }}</span>
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

            {{-- Detalles financieros --}}
            <x-ui.card title="Detalles" icon="receipt_long">
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Subtotal</span>
                        <span class="font-mono">${{ number_format((float) $quotation->subtotal, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">IVA</span>
                        <span class="font-mono">${{ number_format((float) $quotation->iva_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between font-semibold border-t border-gray-200 pt-2">
                        <span>Total</span>
                        <span class="font-mono">${{ number_format((float) $quotation->total, 2) }}</span>
                    </div>
                </div>

                @if($quotation->notes)
                    <div class="mt-4 pt-3 border-t border-gray-100">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Notas</p>
                        <p class="text-xs text-gray-600 whitespace-pre-line">{{ $quotation->notes }}</p>
                    </div>
                @endif

                @if($quotation->purchase)
                    <div class="mt-4 pt-3 border-t border-gray-100">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Compra generada</p>
                        <x-ui.button variant="success" size="sm" icon="receipt_long"
                            href="{{ route('purchases.show', $quotation->purchase_id) }}">
                            Ver compra #{{ $quotation->purchase_id }}
                        </x-ui.button>
                    </div>
                @endif
            </x-ui.card>
        </div>
    </div>
</div>
