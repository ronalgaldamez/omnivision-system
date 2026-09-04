<div class="max-w-7xl mx-auto">
    <x-ui.card title="Ventas entre empresas" icon="point_of_sale" subtitle="Registro de compra/venta entre empresas distintas">
        <x-slot:headerActions>
            <x-ui.button variant="primary" icon="add_circle" href="{{ route('bodega.intercompany-sales.create') }}">
                Nueva venta
            </x-ui.button>
        </x-slot:headerActions>

        <div class="space-y-3">
            @forelse($sales as $sale)
            @php
                $statusMap = [
                    'pending' => ['Pendiente', 'bg-gray-100 text-gray-700'],
                    'in_transit' => ['En tránsito', 'bg-blue-50 text-blue-700'],
                    'delivered' => ['Entregado', 'bg-amber-50 text-amber-700'],
                    'confirmed' => ['Confirmado', 'bg-green-50 text-green-700'],
                ];
                $st = $statusMap[$sale->status] ?? ['Desconocido', 'bg-gray-100 text-gray-700'];
            @endphp
            <a href="{{ route('bodega.intercompany-sales.show', $sale->id) }}" class="block border border-gray-200 rounded-xl p-4 hover:border-blue-300 hover:shadow-sm transition">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center">
                            <span class="material-symbols-outlined text-indigo-600">point_of_sale</span>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-800">{{ $sale->code }}</p>
                            <p class="text-xs text-gray-500">
                                <span class="text-red-700">{{ $sale->sellerBranch?->name }}</span>
                                <span class="material-symbols-outlined text-xs align-middle mx-0.5">arrow_forward</span>
                                <span class="text-green-700">{{ $sale->buyerBranch?->name }}</span>
                                · {{ $sale->created_at->format('d/m/Y H:i') }}
                            </p>
                        </div>
                    </div>
                    <div class="text-right flex flex-col items-end gap-1">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $st[1] }}">{{ $st[0] }}</span>
                        <p class="text-sm font-bold text-gray-800 font-mono">${{ number_format($sale->total, 2) }}</p>
                    </div>
                </div>
                @if($sale->items->isNotEmpty())
                <div class="mt-2 flex flex-wrap gap-1.5">
                    @foreach($sale->items as $item)
                    <span class="text-xs bg-gray-100 px-2 py-0.5 rounded text-gray-600">{{ $item->product_name }} ×{{ rtrim(rtrim(number_format($item->quantity, 4), '0'), '.') }} @ ${{ number_format($item->unit_cost, 2) }}</span>
                    @endforeach
                </div>
                @endif
            </a>
            @empty
            <div class="text-center py-12 text-gray-500">No hay ventas entre empresas registradas</div>
            @endforelse
        </div>
        @if($sales->hasPages())<div class="mt-4">{{ $sales->links() }}</div>@endif
    </x-ui.card>
</div>
