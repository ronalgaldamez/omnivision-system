<div>
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-lg font-semibold">Reporte de Movimientos</h1>
        <button wire:click="exportExcel" class="bg-green-600 text-white px-3 py-1 rounded text-sm">Exportar a
            Excel</button>
    </div>

    <!-- Filtros -->
    <div class="mb-4 grid grid-cols-1 sm:grid-cols-3 gap-2">
        <select wire:model.live="typeFilter" class="border rounded px-2 py-1 text-sm">
            <option value="">Todos los tipos</option>
            <option value="entry">Entrada</option>
            <option value="exit">Salida</option>
            <option value="technician_out">Salida a técnico</option>
            <option value="technician_return">Devolución técnico</option>
            <option value="damage">Dañado</option>
            <option value="return_to_supplier">Dev. proveedor</option>
        </select>
        <input type="date" wire:model.live="dateFrom" class="border rounded px-2 py-1 text-sm" placeholder="Desde">
        <input type="date" wire:model.live="dateTo" class="border rounded px-2 py-1 text-sm" placeholder="Hasta">
    </div>

    <!-- Tabla de movimientos -->
    <div class="overflow-x-auto bg-white rounded shadow">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-2 text-left">Fecha</th>
                    <th class="px-3 py-2 text-left">Producto</th>
                    <th class="px-3 py-2 text-center">Tipo</th>
                    <th class="px-3 py-2 text-right">Cantidad</th>
                    <th class="px-3 py-2 text-left">Usuario</th>
                    <th class="px-3 py-2 text-left">Descripción</th>
                </tr>
            </thead>
            <tbody>
                @foreach($movements as $mov)
                    <tr class="border-b">
                        <td class="px-3 py-1">{{ $mov->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-3 py-1">{{ $mov->product->name }}</td>
                        <td class="px-3 py-1 text-center">{{ $mov->type }}</td>
                        <td class="px-3 py-1 text-right">{{ $mov->quantity }}</td>
                        <td class="px-3 py-1">{{ $mov->user->name }}</td>
                        <td class="px-3 py-1">{{ $mov->description ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>