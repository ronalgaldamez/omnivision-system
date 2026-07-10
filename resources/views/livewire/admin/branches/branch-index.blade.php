<div class="max-w-7xl mx-auto">
    <x-ui.card icon="store" title="Sucursales" subtitle="Gestión de sucursales de la empresa">
        <x-slot:headerActions>
            <x-ui.button variant="primary" icon="add_circle" href="{{ route('admin.branches.create') }}">
                Nueva sucursal
            </x-ui.button>
        </x-slot:headerActions>

        <x-ui.input type="text" wire:model.live="search" placeholder="Buscar por nombre o código..." icon="search" class="mb-5" />

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
                                <span class="material-symbols-outlined text-gray-400 text-base">tag</span>
                                Código
                            </div>
                        </th>
                        <th class="px-4 py-3 text-left text-gray-600 font-medium">
                            <div class="flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-base">location_on</span>
                                Dirección
                            </div>
                        </th>
                        <th class="px-4 py-3 text-center text-gray-600 font-medium">
                            <div class="flex items-center justify-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-base">toggle_on</span>
                                Estado
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
                    @forelse($branches as $branch)
                        <tr class="hover:bg-gray-50/80 transition {{ !$branch->is_active ? 'opacity-60' : '' }}">
                            <td class="px-4 py-3 text-gray-800">{{ $branch->name }}</td>
                            <td class="px-4 py-3">
                                <x-ui.badge variant="neutral">{{ $branch->code }}</x-ui.badge>
                            </td>
                            <td class="px-4 py-3 text-gray-600 text-xs">{{ $branch->address ?? '—' }}</td>
                            <td class="px-4 py-3 text-center">
                                <button wire:click="toggleActive({{ $branch->id }})"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium transition {{ $branch->is_active ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-red-100 text-red-700 hover:bg-red-200' }}">
                                    <span class="material-symbols-outlined text-sm">{{ $branch->is_active ? 'check_circle' : 'block' }}</span>
                                    {{ $branch->is_active ? 'Activo' : 'Inactivo' }}
                                </button>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('admin.branches.edit', $branch->id) }}"
                                        class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Editar">
                                        <span class="material-symbols-outlined text-lg">edit</span>
                                    </a>
                                    <button wire:click="delete({{ $branch->id }})"
                                        onclick="confirm('¿Eliminar esta sucursal?') || event.stopImmediatePropagation()"
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
                                <p class="text-gray-500">No hay sucursales registradas</p>
                                <p class="text-sm text-gray-400 mt-1">Haz clic en "Nueva sucursal" para agregar una</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($branches->hasPages())
            <div class="mt-5">{{ $branches->links() }}</div>
        @endif

        @if(session('message'))
            <x-ui.alert variant="success">{{ session('message') }}</x-ui.alert>
        @endif
        @if(session('error'))
            <x-ui.alert variant="danger">{{ session('error') }}</x-ui.alert>
        @endif
    </x-ui.card>
</div>