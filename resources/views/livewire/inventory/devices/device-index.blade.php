<div class="max-w-7xl mx-auto">
    <x-ui.card title="Dispositivos" icon="settings_ethernet" subtitle="Control de routers y equipos por MAC">
        <x-slot:headerActions>
            <x-ui.button variant="primary" icon="add_circle" href="{{ route('devices.register') }}">Registrar</x-ui.button>
        </x-slot:headerActions>

        <div class="space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar por MAC o PON SN..."
                        class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                </div>
                <x-ui.select wire:model.live="statusFilter" placeholder="Todos los estados">
                    @foreach($statuses as $st)
                        <option value="{{ $st->code }}">{{ $st->name }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.select wire:model.live="productFilter" placeholder="Todos los productos">
                    @foreach(\App\Models\Product::whereHas('devices')->get() as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                </x-ui.select>
            </div>

            <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">MAC Address</th>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">Producto</th>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">PON SN</th>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">Estado</th>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">Técnico</th>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">Compra</th>
                            <th class="px-4 py-3 text-center text-gray-600 font-medium">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($devices as $d)
                            <tr class="hover:bg-gray-50/80 transition">
                                <td class="px-4 py-3 font-mono text-xs text-gray-800">{{ $d->mac_address }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ $d->product?->name }}</td>
                                <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ $d->pon_sn ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    @php $ds = $d->deviceStatus; @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $ds?->color_class ?? 'bg-gray-100 text-gray-700' }}">{{ $ds?->name ?? $d->status }}</span>
                                </td>
                                <td class="px-4 py-3 text-gray-700">{{ $d->technician?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-xs text-gray-500">{{ $d->purchase?->invoice_number ?? '—' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('devices.show', $d->id) }}" class="p-1 text-green-600 hover:text-green-800 rounded transition" title="Ver detalle">
                                        <span class="material-symbols-outlined text-lg">visibility</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-12 text-center text-gray-500">No hay dispositivos registrados</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($devices->hasPages())
                <div class="mt-5">{{ $devices->links() }}</div>
            @endif
        </div>
    </x-ui.card>
</div>
