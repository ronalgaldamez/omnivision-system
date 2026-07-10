<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-blue-50/50 to-white flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-blue-100 flex items-center justify-center">
                    <span class="material-symbols-outlined text-blue-600 text-2xl">local_shipping</span>
                </div>
                <div>
                    <h1 class="text-lg font-semibold text-gray-800">Envíos a sucursales</h1>
                    <p class="text-sm text-gray-500">Tracking y control de reparticiones</p>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('bodega.shipments.receive') }}" class="inline-flex items-center gap-1.5 px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg bg-white hover:bg-gray-50 transition">
                    <span class="material-symbols-outlined text-base">qr_code_scanner</span>
                    Recibir
                </a>
                <a href="{{ route('bodega.shipments.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-blue-700 transition">
                    <span class="material-symbols-outlined text-base">add_circle</span>
                    Nuevo envío
                </a>
            </div>
        </div>
        <div class="p-6 space-y-4">
            <div class="flex gap-2">
                <select wire:model.live="statusFilter" class="w-48 px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm">
                    <option value="">Todos</option>
                    <option value="pending">Pendiente</option>
                    <option value="in_transit">En tránsito</option>
                    <option value="delivered">Entregado</option>
                    <option value="confirmed">Confirmado</option>
                </select>
            </div>
            <div class="space-y-3">
                @forelse($shipments as $s)
                @php $statusMap = ['pending' => ['Pendiente', 'bg-gray-100 text-gray-700'], 'in_transit' => ['En tránsito', 'bg-blue-50 text-blue-700'], 'delivered' => ['Entregado', 'bg-amber-50 text-amber-700'], 'confirmed' => ['Confirmado', 'bg-green-50 text-green-700']]; @endphp
                <a href="{{ route('bodega.shipments.show', $s->id) }}" class="block border border-gray-200 rounded-xl p-4 hover:border-blue-300 hover:shadow-sm transition">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                                <span class="material-symbols-outlined text-blue-600">local_shipping</span>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-800">{{ $s->code }}</p>
                                <p class="text-xs text-gray-500">{{ $s->branch?->name }} · {{ $s->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusMap[$s->status][1] }}">{{ $statusMap[$s->status][0] }}</span>
                    </div>
                    @if($s->items->isNotEmpty())
                    <div class="mt-2 flex gap-2">
                        @foreach($s->items as $item)
                        <span class="text-xs bg-gray-100 px-2 py-0.5 rounded text-gray-600">{{ $item->product_name }} ×{{ (int) $item->quantity }}</span>
                        @endforeach
                    </div>
                    @endif
                </a>
                @empty
                <div class="text-center py-12 text-gray-500">No hay envíos registrados</div>
                @endforelse
            </div>
            @if($shipments->hasPages())<div class="mt-4">{{ $shipments->links() }}</div>@endif
        </div>
    </div>
</div>
