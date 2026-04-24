<div>
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-lg font-semibold text-gray-800">Roles</h1>
        <a href="{{ route('admin.roles.create') }}"
            class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700">
            <span class="material-symbols-outlined text-base">add</span> Nuevo Rol
        </a>
    </div>

    <div class="mb-4">
        <input type="text" wire:model.live="search" placeholder="Buscar rol..."
            class="w-full sm:w-64 rounded-md border-gray-300 text-sm">
    </div>

    <div class="overflow-x-auto bg-white rounded-lg shadow-sm border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left">Nombre</th>
                    <th class="px-4 py-2 text-left">Permisos</th>
                    <th class="px-4 py-2 text-left">Usuarios</th>
                    <th class="px-4 py-2 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($roles as $role)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2">{{ $role->name }}</td>
                        <td class="px-4 py-2">
                            {{ $role->permissions->pluck('name')->implode(', ') ?: 'Sin permisos' }}
                        </td>
                        <td class="px-4 py-2">{{ $role->users()->count() }}</td>
                        <td class="px-4 py-2 text-center space-x-1">
                            <a href="{{ route('admin.roles.edit', $role->id) }}"
                                class="text-blue-600 hover:text-blue-800 inline-flex items-center"><span
                                    class="material-symbols-outlined text-base">edit</span></a>
                            <button wire:click="delete({{ $role->id }})"
                                onclick="confirm('¿Eliminar?') || event.stopImmediatePropagation()"
                                class="text-red-500 hover:text-red-700 inline-flex items-center"><span
                                    class="material-symbols-outlined text-base">delete</span></button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-4 text-center text-gray-400">No hay roles</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $roles->links() }}</div>
    @if(session('message'))
    <div class="mt-2 text-sm text-green-600">{{ session('message') }}</div> @endif
    @if(session('error'))
    <div class="mt-2 text-sm text-red-600">{{ session('error') }}</div> @endif
</div>