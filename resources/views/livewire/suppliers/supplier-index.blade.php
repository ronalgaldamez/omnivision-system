<div class="max-w-7xl mx-auto">
    <x-ui.card title="Proveedores" icon="warehouse" subtitle="Gestión de proveedores y contactos">
        <x-slot:headerActions>
            <x-ui.button variant="primary" icon="add_circle" href="{{ route('suppliers.create') }}">Nuevo proveedor</x-ui.button>
        </x-slot:headerActions>

        <div class="space-y-5">
            <div class="relative w-full">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar proveedor..."
                    class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
            </div>

            <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">
                                <div class="flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">badge</span>
                                    Nombre
                                </div>
                            </th>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">
                                <div class="flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">person</span>
                                    Contacto
                                </div>
                            </th>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">
                                <div class="flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">call</span>
                                    Teléfono
                                </div>
                            </th>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">
                                <div class="flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">mail</span>
                                    Email
                                </div>
                            </th>
                            <th class="px-4 py-3 text-center text-gray-600 font-medium">
                                <div class="flex items-center justify-center gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">settings</span>
                                    Acciones
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($suppliers as $supplier)
                            <tr class="hover:bg-gray-50/80 transition">
                                <td class="px-4 py-3 text-gray-800">{{ $supplier->name }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ $supplier->contact_name ?? '-' }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ implode(', ', $supplier->phones ?? []) ?: '—' }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ $supplier->email ?? '-' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <a href="{{ route('suppliers.show', $supplier->id) }}"
                                            class="p-1.5 text-green-600 hover:bg-green-50 rounded-lg transition" title="Ver">
                                            <span class="material-symbols-outlined text-lg">visibility</span>
                                        </a>
                                        <a href="{{ route('suppliers.edit', $supplier->id) }}"
                                            class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Editar">
                                            <span class="material-symbols-outlined text-lg">edit</span>
                                        </a>
                                        <button wire:click="confirmDelete({{ $supplier->id }})"
                                            class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition" title="Eliminar">
                                            <span class="material-symbols-outlined text-lg">delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-12 text-center bg-gray-50/50">
                                    <span class="material-symbols-outlined text-gray-300 text-4xl mb-2">inbox</span>
                                    <p class="text-gray-500">No hay proveedores registrados</p>
                                    <p class="text-sm text-gray-400 mt-1">Haz clic en "Nuevo proveedor" para agregar uno</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($suppliers->hasPages())
                <div class="mt-5">{{ $suppliers->links() }}</div>
            @endif

            @if(session('message'))
                <x-ui.alert variant="success">{{ session('message') }}</x-ui.alert>
            @endif
            @if(session('error'))
                <x-ui.alert variant="danger">{{ session('error') }}</x-ui.alert>
            @endif
        </div>
    </x-ui.card>

    {{-- Modal de confirmación de eliminación --}}
    <div x-data="{ show: @entangle('showDeleteModal') }" x-show="show" x-cloak
        class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center"
        style="display: none;">
        <div class="relative mx-auto p-5 w-full max-w-md">
            <div class="bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden">
                <div class="p-6 text-center">
                    <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-red-100 mb-4">
                        <span class="material-symbols-outlined text-red-600 text-2xl">delete_forever</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Confirmar eliminación</h3>
                    <p class="text-sm text-gray-600 mt-2">
                        ¿Estás seguro de eliminar a <strong>{{ $deleteName }}</strong>? Esta acción no se puede deshacer.
                    </p>
                    @if($deleteHasPurchases)
                    <p class="text-xs text-red-600 bg-red-50 rounded-lg px-3 py-2 mt-2">
                        Este proveedor tiene compras asociadas. Elimínalas primero.
                    </p>
                    @endif
                </div>
                <div class="bg-gray-50 px-6 py-4 flex flex-col gap-3 sm:flex-row-reverse">
                    <button wire:click="delete" wire:loading.attr="disabled" wire:target="delete"
                        class="w-full sm:w-auto px-5 py-2.5 bg-red-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-red-700 transition disabled:opacity-50">
                        <span wire:loading.remove wire:target="delete">Sí, eliminar</span>
                        <span wire:loading wire:target="delete" class="inline-flex items-center gap-2">
                            <span class="animate-spin material-symbols-outlined text-base">progress_activity</span>Eliminando...
                        </span>
                    </button>
                    <button @click="show = false"
                        class="w-full sm:w-auto px-5 py-2.5 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
