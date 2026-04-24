<div>
    <h1 class="text-lg font-semibold">Panel NOC - Tickets pendientes</h1>
    @if(session('message')) <div class="text-green-600">{{ session('message') }}</div> @endif
    <table class="min-w-full border mt-4">
        <thead>
            <tr><th>ID</th><th>Cliente</th><th>Servicio</th><th>Descripción</th><th>Acciones</th></tr>
        </thead>
        <tbody>
            @foreach($tickets as $ticket)
            <tr>
                <td>{{ $ticket->id }}</td>
                <td>{{ $ticket->client->name }}</td>
                <td>{{ $ticket->service_type }}</td>
                <td>{{ Str::limit($ticket->description, 100) }}</td>
                <td class="space-x-2">
                    <button wire:click="resolveRemote({{ $ticket->id }})" class="bg-green-600 text-white px-2 py-1 rounded">Resolver remoto</button>
                    <button wire:click="createWorkOrder({{ $ticket->id }})" class="bg-blue-600 text-white px-2 py-1 rounded">Crear OT</button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>