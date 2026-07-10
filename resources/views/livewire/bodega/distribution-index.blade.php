<div class="max-w-7xl mx-auto">
    <x-ui.card title="Envíos a sucursales" icon="local_shipping" subtitle="Tracking y control de reparticiones">
        <x-slot:headerActions>
            <x-ui.button variant="secondary" icon="qr_code_scanner" href="{{ route('bodega.shipments.receive') }}">
                Recibir
            </x-ui.button>
            <x-ui.button variant="primary" icon="add_circle" href="{{ route('bodega.shipments.create') }}">
                Nuevo envío
            </x-ui.button>
        </x-slot:headerActions>

        <div class="space-y-4">
            <x-ui.select wire:model.live="statusFilter" placeholder="Todos" label="">
                <option value="">Todos</option>
                <option value="pending">Pendiente</option>
                <option value="in_transit">En tránsito</option>
                <option value="delivered">Entregado</option>
                <option value="confirmed">Confirmado</option>
            </x-ui.select>

            <div class="space-y-3">
                @forelse($shipments as $s)
                @php $statusMap = ['pending' => ['Pendiente', 'bg-gray-100 text-gray-700', 'schedule'], 'in_transit' => ['En tránsito', 'bg-blue-50 text-blue-700', 'local_shipping'], 'delivered' => ['Entregado', 'bg-amber-50 text-amber-700', 'inventory_2'], 'confirmed' => ['Confirmado', 'bg-green-50 text-green-700', 'check_circle']]; @endphp
                <a href="{{ route('bodega.shipments.show', $s->id) }}" class="block border border-gray-200 rounded-xl p-4 hover:border-blue-300 hover:shadow-sm transition">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                                <span class="material-symbols-outlined text-blue-600">{{ $statusMap[$s->status][2] }}</span>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-800">{{ $s->code }}</p>
                                <p class="text-xs text-gray-500">{{ $s->branch?->name }} · {{ $s->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusMap[$s->status][1] }}">{{ $statusMap[$s->status][0] }}</span>
                    </div>
                    @if($s->items->isNotEmpty())
                    <div class="mt-2 flex flex-wrap gap-1.5">
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
    </x-ui.card>
</div>
