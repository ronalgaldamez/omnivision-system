<div class="max-w-3xl mx-auto bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <h1 class="text-lg font-semibold mb-4">Revisar Solicitud #{{ $request->id }}</h1>

    <div class="mb-4">
        <p><strong>Técnico:</strong> {{ $request->technician->name }}</p>
        <p><strong>Orden de Trabajo asociada:</strong>
            @if($request->workOrder)
                #{{ $request->workOrder->id }} - {{ $request->workOrder->client_name }}
                ({{ $request->workOrder->scheduled_date?->format('d/m/Y') }})
            @else
                No asociada a ninguna OT
            @endif
        </p>
        <p><strong>Fecha de solicitud:</strong> {{ $request->created_at->format('d/m/Y H:i') }}</p>
        <p><strong>Notas:</strong> {{ $request->notes ?: 'Sin notas' }}</p>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm border">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left">Producto</th>
                    <th class="px-4 py-2 text-center">Cantidad solicitada</th>
                    <th class="px-4 py-2 text-center">Stock actual</th>
                </tr>
            </thead>
            <tbody>
                @foreach($request->products as $rp)
                    <tr class="border-t">
                        <td class="px-4 py-2">{{ $rp->product->name }}</td>
                        <td class="px-4 py-2 text-center">{{ $rp->quantity_requested }}</td>
                        <td
                            class="px-4 py-2 text-center {{ $rp->product->current_stock < $rp->quantity_requested ? 'text-red-600 font-bold' : '' }}">
                            {{ $rp->product->current_stock }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="flex justify-end space-x-2 mt-6">
        <button wire:click="reject"
            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">Rechazar</button>
        <button wire:click="approve" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">Aprobar y
            Entregar</button>
    </div>

    @if(session('message'))
    <div class="mt-3 text-sm text-green-600">{{ session('message') }}</div> @endif
    @if(session('error'))
    <div class="mt-3 text-sm text-red-600">{{ session('error') }}</div> @endif
</div>