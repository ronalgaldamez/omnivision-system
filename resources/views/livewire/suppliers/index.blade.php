<div>
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-lg font-semibold text-gray-800">Proveedores</h1>
        <a href="{{ route('suppliers.create') }}"
            class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700">
            <span class="material-symbols-outlined text-base">add</span> Nuevo
        </a>
    </div>

    <div class="mb-4">
        <input type="text" wire:model.live="search" placeholder="Buscar proveedor..."
            class="w-full sm:w-64 rounded-md border-gray-300 shadow-sm focus:border-blue-300 text-sm">
    </div>

    <div class="overflow-x-auto bg-white rounded-lg shadow-sm border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left">Nombre</th>
                    <th class="px-4 py-2 text-left">Contacto</th>
                    <th class="px-4 py-2 text-left">Teléfono</th>
                    <th class="px-4 py-2 text-left">Email</th>
                    <th class="px-4 py-2 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($suppliers as $supplier)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2">{{ $supplier->name }}</td>
                        <td class="px-4 py-2">{{ $supplier->contact_name }}</td>
                        <td class="px-4 py-2">{{ $supplier->phone }}</td>
                        <td class="px-4 py-2">{{ $supplier->email }}</td>
                        <td class="px-4 py-2 text-center space-x-1">
                            <a href="{{ route('suppliers.edit', $supplier->id) }}"
                                class="text-blue-600 hover:text-blue-800 inline-flex items-center"><span
                                    class="material-symbols-outlined text-base">edit</span></a>
                            <button wire:click="delete({{ $supplier->id }})"
                                onclick="confirm('¿Eliminar?') || event.stopImmediatePropagation()"
                                class="text-red-500 hover:text-red-700 inline-flex items-center"><span
                                    class="material-symbols-outlined text-base">delete</span></button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-4 text-center text-gray-400">No hay proveedores</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $suppliers->links() }}</div>
    @if(session('message'))
    <div class="mt-2 text-sm text-green-600">{{ session('message') }}</div> @endif
    @if(session('error'))
    <div class="mt-2 text-sm text-red-600">{{ session('error') }}</div> @endif
</div>