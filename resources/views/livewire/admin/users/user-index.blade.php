<div class="max-w-7xl mx-auto">
    <x-ui.card icon="group" title="Usuarios" subtitle="Gestión de usuarios del sistema">
        <x-slot:headerActions>
            <x-ui.button variant="primary" icon="add_circle" href="{{ route('admin.users.create') }}">
                Nuevo usuario
            </x-ui.button>
        </x-slot:headerActions>

        <div class="flex items-center gap-4 mb-5">
            <x-ui.input type="text" wire:model.live="search" placeholder="Buscar por nombre o email..." icon="search" class="flex-1" />
            <x-ui.select wire:model.live="filterStatus" class="w-40">
                <option value="">Todos</option>
                <option value="1">Activos</option>
                <option value="0">Inactivos</option>
            </x-ui.select>
        </div>

        <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="px-4 py-3 text-left text-gray-600 font-medium">Usuario</th>
                        <th class="px-4 py-3 text-left text-gray-600 font-medium">Email</th>
                        <th class="px-4 py-3 text-left text-gray-600 font-medium">Rol</th>
                        <th class="px-4 py-3 text-left text-gray-600 font-medium">Sucursal</th>
                        <th class="px-4 py-3 text-center text-gray-600 font-medium">Estado</th>
                        <th class="px-4 py-3 text-center text-gray-600 font-medium">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50/80 transition {{ !$user->is_active ? 'opacity-60' : '' }}">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-sm font-semibold uppercase flex-shrink-0">
                                        {{ substr($user->name, 0, 2) }}
                                    </div>
                                    <div>
                                        <p class="text-gray-800 font-medium">@if($user->is_super_admin)<span class="material-symbols-outlined text-base align-middle text-amber-500 me-0.5">star</span>@endif{{ $user->name }}</p>
                                        <p class="text-gray-400 text-xs">{{ $user->username }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $user->email }}</td>
                            <td class="px-4 py-3">
                                @foreach($user->getRoleNames() as $role)
                                    <x-ui.badge variant="info">{{ $role }}</x-ui.badge>
                                @endforeach
                            </td>
                            <td class="px-4 py-3 text-gray-600 text-xs">{{ optional($user->branch)->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-center">
                                <button wire:click="toggleActive({{ $user->id }})"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium transition {{ $user->is_active ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-red-100 text-red-700 hover:bg-red-200' }}">
                                    <span class="material-symbols-outlined text-sm">{{ $user->is_active ? 'check_circle' : 'block' }}</span>
                                    {{ $user->is_active ? 'Activo' : 'Inactivo' }}
                                </button>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('admin.users.edit', $user->id) }}"
                                        class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Editar">
                                        <span class="material-symbols-outlined text-lg">edit</span>
                                    </a>
                                    <button wire:click="delete({{ $user->id }})"
                                        onclick="confirm('¿Eliminar este usuario?') || event.stopImmediatePropagation()"
                                        class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition" title="Eliminar">
                                        <span class="material-symbols-outlined text-lg">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center bg-gray-50/50">
                                <span class="material-symbols-outlined text-gray-300 text-4xl mb-2">people_outline</span>
                                <p class="text-gray-500">No hay usuarios registrados</p>
                                <p class="text-sm text-gray-400 mt-1">Haz clic en "Nuevo usuario" para agregar uno</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="mt-5">{{ $users->links() }}</div>
        @endif
    </x-ui.card>
</div>