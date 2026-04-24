<div>
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-lg font-semibold text-gray-800">Devoluciones de Materiales</h1>
        <a href="{{ route('technician-returns.create') }}"
            class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700">
            <span class="material-symbols-outlined text-base">add</span> Nueva Devolución
        </a>
    </div>

    <div class="mb-4 flex gap-2">
        <input type="text" wire:model.live="search" placeholder="Buscar por producto o nota..."
            class="flex-1 rounded-md border-gray-300 text-sm">
        <select wire:model.live="typeFilter" class="rounded-md border-gray-300 text-sm w-32">
            <option value="">Todos</option>
            <option value="surplus">Sobrante</option>
            <option value="damage">Dañado</option>
        </select>
    </div>

    <div class="overflow-x-auto bg-white rounded-lg shadow-sm border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left">ID</th>
                    <th class="px-4 py-2 text-left">Tipo</th>
                    <th class="px-4 py-2 text-left">Producto</th>
                    <th class="px-4 py-2 text-center">Cantidad</th>
                    <th class="px-4 py-2 text-left">Solicitud #</th>
                    <th class="px-4 py-2 text-left">Notas</th>
                    <th class="px-4 py-2 text-left">Registrado por</th>
                    <th class="px-4 py-2 text-left">Fecha</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($returns as $ret)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2">#{{ $ret->id }}</td>
                        <td class="px-4 py-2">
                            @if($ret->type == 'surplus')
                                <span class="text-green-600">Sobrante (+stock)</span>
                            @else
                                <span class="text-red-600">Dañado (-stock)</span>
                            @endif
                        </td>
                        <td class="px-4 py-2">{{ $ret->product->name }}</td>
                        <td class="px-4 py-2 text-center">{{ $ret->quantity }}</td>
                        <td class="px-4 py-2">{{ $ret->request ? '#' . $ret->request->id : '—' }}</td>
                        <td class="px-4 py-2">{{ $ret->notes ?: '—' }}</td>
                        <td class="px-4 py-2">{{ $ret->user->name }}</td>
                        <td class="px-4 py-2">{{ $ret->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-4 text-center text-gray-400">No hay devoluciones registradas</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $returns->links() }}</div>
</div>