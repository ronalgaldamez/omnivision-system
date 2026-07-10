<div class="max-w-7xl mx-auto">
    <x-ui.card icon="people" title="Clientes" subtitle="Gestión de clientes">
        <x-slot:headerActions>
            @can('create clients')
                <x-ui.button variant="primary" icon="add_circle" href="{{ route('admin.clients.create') }}">
                    Nuevo Cliente
                </x-ui.button>
            @endcan
        </x-slot:headerActions>

        <x-ui.input type="text" wire:model.live="search" placeholder="Buscar por nombre, DUI, correo..." icon="search" class="mb-5" />

        <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="px-4 py-3 text-left text-gray-600 font-medium">Nombre</th>
                        <th class="px-4 py-3 text-left text-gray-600 font-medium">Documento</th>
                        <th class="px-4 py-3 text-left text-gray-600 font-medium">Teléfono</th>
                        <th class="px-4 py-3 text-left text-gray-600 font-medium">Servicio</th>
                        <th class="px-4 py-3 text-center text-gray-600 font-medium">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($clients as $client)
                        <tr class="hover:bg-gray-50/80 transition">
                            <td class="px-4 py-3 text-gray-800">{{ $client->name }}</td>
                            <td class="px-4 py-3 text-gray-700">
                                {{ $client->document_type ? strtoupper($client->document_type) . ': ' . $client->document_number : 'N/A' }}
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ $client->phone ?? 'N/A' }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $client->service ?? 'N/A' }}</td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('admin.clients.show', $client->id) }}"
                                        class="p-1.5 text-green-600 hover:bg-green-50 rounded-lg transition" title="Ver detalle">
                                        <span class="material-symbols-outlined text-lg">visibility</span>
                                    </a>
                                    @can('edit clients')
                                        <a href="{{ route('admin.clients.edit', $client->id) }}"
                                            class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Editar">
                                            <span class="material-symbols-outlined text-lg">edit</span>
                                        </a>
                                    @endcan
                                    @can('delete clients')
                                        <button wire:click="delete({{ $client->id }})"
                                            onclick="confirm('¿Eliminar cliente?') || event.stopImmediatePropagation()"
                                            class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition" title="Eliminar">
                                            <span class="material-symbols-outlined text-lg">delete</span>
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center bg-gray-50/50">
                                <span class="material-symbols-outlined text-gray-300 text-4xl mb-2">inbox</span>
                                <p class="text-gray-500">No hay clientes registrados</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($clients->hasPages())
            <div class="mt-5">{{ $clients->links() }}</div>
        @endif

        @if(session('message'))
            <x-ui.alert variant="success">{{ session('message') }}</x-ui.alert>
        @endif
        @if(session('error'))
            <x-ui.alert variant="danger">{{ session('error') }}</x-ui.alert>
        @endif
    </x-ui.card>
</div>