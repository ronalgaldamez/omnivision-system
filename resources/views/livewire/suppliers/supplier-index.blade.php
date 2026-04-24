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
            class="w-full sm:w-64 rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 text-sm">
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
                        <td class="px-4 py-2">{{ $supplier->contact_name ?? '-' }}</td>
                        <td class="px-4 py-2">{{ $supplier->phone ?? '-' }}</td>
                        <td class="px-4 py-2">{{ $supplier->email ?? '-' }}</td>
                        <td class="px-4 py-2 text-center space-x-1">
                            <a href="{{ route('suppliers.show', $supplier->id) }}"
                                class="text-green-600 hover:text-green-800 inline-flex items-center" title="Ver">
                                <span class="material-symbols-outlined text-base">visibility</span>
                            </a>
                            <a href="{{ route('suppliers.edit', $supplier->id) }}"
                                class="text-blue-600 hover:text-blue-800 inline-flex items-center" title="Editar">
                                <span class="material-symbols-outlined text-base">edit</span>
                            </a>
                            <button wire:click="confirmDelete({{ $supplier->id }})"
                                class="text-red-500 hover:text-red-700 inline-flex items-center" title="Eliminar">
                                <span class="material-symbols-outlined text-base">delete</span>
                            </button>
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

    <!-- Modal de confirmación de eliminación -->
    <div x-data="{ show: @entangle('showDeleteModal') }" x-show="show" x-cloak
        class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" style="display: none;">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                    <span class="material-symbols-outlined text-red-600">warning</span>
                </div>
                <h3 class="text-lg leading-6 font-medium text-gray-900 mt-2">Confirmar eliminación</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500">¿Estás seguro de eliminar este proveedor? Esta acción no se puede
                        deshacer.</p>
                </div>
                <div class="items-center px-4 py-3">
                    <button wire:click="delete"
                        class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-red-700">Sí,
                        eliminar</button>
                    <button @click="show = false"
                        class="mt-2 px-4 py-2 bg-gray-300 text-gray-700 text-base font-medium rounded-md w-full hover:bg-gray-400">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div x-data="{ toast: null, toastType: null, toastMessage: '' }"
        x-on:show-toast.window="toast = true; toastType = $event.detail.type; toastMessage = $event.detail.message; setTimeout(() => toast = false, 5000)"
        x-show="toast" x-cloak class="fixed bottom-5 right-5 z-50 transition-all duration-300" style="display: none;">
        <div x-show="toastType === 'success'"
            class="bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg flex items-center gap-2">
            <span class="material-symbols-outlined">check_circle</span> <span x-text="toastMessage"></span>
        </div>
        <div x-show="toastType === 'error'"
            class="bg-red-500 text-white px-4 py-2 rounded-lg shadow-lg flex items-center gap-2">
            <span class="material-symbols-outlined">error</span> <span x-text="toastMessage"></span>
        </div>
    </div>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</div>