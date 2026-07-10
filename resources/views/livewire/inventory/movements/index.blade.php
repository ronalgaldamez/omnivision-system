<div class="max-w-7xl mx-auto">
    <x-ui.card title="Movimientos de Inventario" subtitle="Historial de entradas y salidas de productos" icon="inventory">
        <x-slot:headerActions>
            <x-ui.button variant="primary" size="sm" icon="add_circle" href="{{ route('movements.create') }}">Nuevo movimiento</x-ui.button>
        </x-slot:headerActions>

        <div class="space-y-5">
            @if($activeBranch)
                <x-ui.alert variant="info">
                    Filtrando por sucursal: <strong>{{ $activeBranch->name }}</strong>
                </x-ui.alert>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <x-ui.input icon="search" wire:model.live.debounce.300ms="search" placeholder="Buscar por producto o SKU..." />
                <x-ui.select icon="filter_alt" wire:model.live="typeFilter" placeholder="Todos los tipos">
                    <option value="entry">Entrada</option>
                    <option value="exit">Salida</option>
                    <option value="technician_out">Salida a técnico</option>
                    <option value="technician_return">Devolución de técnico</option>
                    <option value="damage">Dañado</option>
                    <option value="return_to_supplier">Devolución a proveedor</option>
                </x-ui.select>
                <x-ui.input type="date" icon="calendar_today" wire:model.live="dateFrom" />
                <x-ui.input type="date" icon="event" wire:model.live="dateTo" />
            </div>

            <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">
                                <div class="flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">calendar_month</span>
                                    Fecha
                                </div>
                            </th>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">
                                <div class="flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">inventory_2</span>
                                    Producto
                                </div>
                            </th>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">
                                <div class="flex items-center gap-1.5">
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
                            <th class="px-4 py-3 text-right text-gray-600 font-medium">
                                <div class="flex items-center justify-end gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">attach_money</span>
                                    Costo unitario
                                </div>
                            </th>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">
                                <div class="flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">person</span>
                                    Usuario
                                </div>
                            </th>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">
                                <div class="flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">link</span>
                                    Referencia
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($movements as $mov)
                            <tr class="hover:bg-gray-50/80 transition">
                                <td class="px-4 py-3 font-mono text-xs text-gray-700">{{ $mov->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3 text-gray-800">{{ $mov->product->name }}</td>
                                <td class="px-4 py-3">
                                    @php $td = $mov->type_display; @endphp
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium {{ $td['class'] }}">
                                        <span class="material-symbols-outlined text-sm">{{ $td['icon'] }}</span> {{ $td['label'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right font-mono text-gray-800">{{ $mov->quantity }}</td>
                                <td class="px-4 py-3 text-right text-gray-700">{{ $mov->unit_cost ? '$' . number_format($mov->unit_cost, 2) : '—' }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ $mov->user->name }}</td>
                                <td class="px-4 py-3 text-xs text-gray-500">
                                    @if($mov->reference_type == 'purchase' && $mov->reference_id)
                                        <span class="bg-gray-100 px-2 py-0.5 rounded-full">Compra #{{ $mov->reference_id }}</span>
                                    @elseif($mov->reference_type == 'distribution' && $mov->branch)
                                        <span class="bg-teal-100 text-teal-700 px-2 py-0.5 rounded-full">{{ $mov->branch->name }}</span>
                                    @elseif($mov->reference_type && $mov->reference_id)
                                        <span class="bg-gray-100 px-2 py-0.5 rounded-full">{{ $mov->reference_type }}: #{{ $mov->reference_id }}</span>
                                    @elseif($mov->description)
                                        <span class="text-gray-400 italic">{{ Str::limit($mov->description, 30) }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center bg-gray-50/50">
                                    <span class="material-symbols-outlined text-gray-300 text-4xl mb-2">inbox</span>
                                    <p class="text-gray-500">No hay movimientos registrados</p>
                                    <p class="text-sm text-gray-400 mt-1">Los movimientos que agregues aparecerán aquí</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($movements->hasPages())
                <div class="mt-5">{{ $movements->links() }}</div>
            @endif

            @if(session('message'))
                <x-ui.alert variant="success">{{ session('message') }}</x-ui.alert>
            @endif
            @if(session('error'))
                <x-ui.alert variant="danger">{{ session('error') }}</x-ui.alert>
            @endif
        </div>
    </x-ui.card>
</div>
