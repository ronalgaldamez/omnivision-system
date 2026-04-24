<div>
    <h1 class="text-lg font-semibold">Tickets</h1>
    <div class="flex justify-between mb-4">
        <input type="text" wire:model.live="search" placeholder="Buscar por cliente o descripción..."
            class="border rounded px-2 py-1">
        <select wire:model.live="statusFilter" class="border rounded px-2 py-1">
            <option value="">Todos</option>
            <option value="pending">Pendiente</option>
            <option value="in_progress">En progreso</option>
            <option value="resolved">Resuelto</option>
            <option value="closed">Cerrado</option>
        </select>
        @can('create tickets')
            <a href="{{ route('tickets.create') }}" class="bg-blue-600 text-white px-3 py-1 rounded">Nuevo Ticket</a>
        @endcan
    </div>
    <table class="min-w-full border">
        <thead>
            <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Tipo</th>
                <th>Descripción</th>
                <th>NOC</th>
                <th>Estado</th>
                <th>Creado</th>
                <th>OT</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tickets as $ticket)
                <tr>
                    <td>{{ $ticket->id }}</td>
                    <td>{{ $ticket->client->name }}</td>
                    <td>{{ $ticket->service_type }}</td>
                    <td>{{ Str::limit($ticket->description, 50) }}</td>
                    <td>{{ $ticket->requires_noc ? 'Sí' : 'No' }}</td>
                    <td>{{ $ticket->status }}</td>
                    <td>{{ $ticket->created_at->format('d/m/Y') }}</td>
                    <td>
                        @if($ticket->workOrder)
                            <a href="{{ route('work-orders.show', $ticket->workOrder->id) }}">Ver OT</a>
                        @else
                            @if($ticket->requires_noc && Auth::user()->hasRole('noc'))
                                <button wire:click="createOt({{ $ticket->id }})" class="text-green-600">Crear OT</button>
                            @endif
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $tickets->links() }}
</div>