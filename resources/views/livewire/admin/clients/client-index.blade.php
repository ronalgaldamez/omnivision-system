<div class="max-w-7xl mx-auto">
    <!-- Tarjeta principal -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <!-- Encabezado con fondo sutil -->
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                    <span class="material-symbols-outlined text-gray-500">people</span>
                    Clientes
                </h1>
                <p class="text-sm text-gray-500 mt-1">Gestión de clientes</p>
            </div>
            @can('create clients')
                <a href="{{ route('admin.clients.create') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 transition">
                    <span class="material-symbols-outlined text-base">add_circle</span>
                    Nuevo Cliente
                </a>
            @endcan
        </div>

        <!-- Contenido -->
        <div class="p-6 space-y-5">
            <!-- Filtro de búsqueda -->
            <div class="relative w-full">
                <span
                    class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                <input type="text" wire:model.live="search" placeholder="Buscar por nombre, DUI, correo..."
                    class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
            </div>

            <!-- Tabla de clientes -->
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
                                        @can('edit clients')
                                            <a href="{{ route('admin.clients.edit', $client->id) }}"
                                                class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition"
                                                title="Editar">
                                                <span class="material-symbols-outlined text-lg">edit</span>
                                            </a>
                                        @endcan
                                        @can('delete clients')
                                            <button wire:click="delete({{ $client->id }})"
                                                onclick="confirm('¿Eliminar cliente?') || event.stopImmediatePropagation()"
                                                class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition"
                                                title="Eliminar">
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

            <!-- Paginación -->
            @if($clients->hasPages())
                <div class="mt-5">
                    {{ $clients->links() }}
                </div>
            @endif

            <!-- Mensajes de sesión -->
            @if(session('message'))
                <div
                    class="flex items-center gap-2 text-sm text-green-700 bg-green-50 px-4 py-3 rounded-lg border border-green-200">
                    <span class="material-symbols-outlined text-green-600">check_circle</span>
                    {{ session('message') }}
                </div>
            @endif
            @if(session('error'))
                <div
                    class="flex items-center gap-2 text-sm text-red-700 bg-red-50 px-4 py-3 rounded-lg border border-red-200">
                    <span class="material-symbols-outlined text-red-600">error</span>
                    {{ session('error') }}
                </div>
            @endif
        </div>
    </div>
</div>