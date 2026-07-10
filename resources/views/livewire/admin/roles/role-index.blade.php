<div class="max-w-7xl mx-auto">
    <x-ui.card icon="security" title="Roles" subtitle="Gestión de roles y permisos del sistema">
        <x-slot:headerActions>
            <x-ui.button variant="primary" icon="add_circle" href="{{ route('admin.roles.create') }}">
                Nuevo Rol
            </x-ui.button>
        </x-slot:headerActions>

        <x-ui.input type="text" wire:model.live="search" placeholder="Buscar rol..." icon="search" class="mb-5" />

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
                                <span class="material-symbols-outlined text-gray-400 text-base">lock</span>
                                Permisos
                            </div>
                        </th>
                        <th class="px-4 py-3 text-left text-gray-600 font-medium">
                            <div class="flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-base">people</span>
                                Usuarios
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
                    @forelse($roles as $role)
                        <tr class="hover:bg-gray-50/80 transition">
                            <td class="px-4 py-3 text-gray-800">{{ $role->name }}</td>
                            <td class="px-4 py-3">
                                @if($role->permissions->isNotEmpty())
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($role->permissions->take(3) as $perm)
                                            <x-ui.badge variant="neutral">{{ $perm->name }}</x-ui.badge>
                                        @endforeach
                                        @if($role->permissions->count() > 3)
                                            <span class="text-xs text-gray-500">+{{ $role->permissions->count() - 3 }} más</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-gray-400 text-xs">Sin permisos</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ $role->users()->count() }}</td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('admin.roles.edit', $role->id) }}"
                                        class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Editar">
                                        <span class="material-symbols-outlined text-lg">edit</span>
                                    </a>
                                    <button wire:click="delete({{ $role->id }})"
                                        onclick="confirm('¿Eliminar?') || event.stopImmediatePropagation()"
                                        class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition" title="Eliminar">
                                        <span class="material-symbols-outlined text-lg">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-12 text-center bg-gray-50/50">
                                <span class="material-symbols-outlined text-gray-300 text-4xl mb-2">inbox</span>
                                <p class="text-gray-500">No hay roles registrados</p>
                                <p class="text-sm text-gray-400 mt-1">Haz clic en "Nuevo Rol" para agregar uno</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($roles->hasPages())
            <div class="mt-5">{{ $roles->links() }}</div>
        @endif

        @if(session('message'))
            <x-ui.alert variant="success">{{ session('message') }}</x-ui.alert>
        @endif
        @if(session('error'))
            <x-ui.alert variant="danger">{{ session('error') }}</x-ui.alert>
        @endif
    </x-ui.card>
</div>