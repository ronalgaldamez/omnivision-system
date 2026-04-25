<div class="max-w-7xl mx-auto">
    <!-- Tarjeta principal -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <!-- Encabezado con fondo sutil -->
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                    <span class="material-symbols-outlined text-gray-500">security</span>
                    Roles
                </h1>
                <p class="text-sm text-gray-500 mt-1">Gestión de roles y permisos del sistema</p>
            </div>
            <a href="{{ route('admin.roles.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 transition">
                <span class="material-symbols-outlined text-base">add_circle</span>
                Nuevo Rol
            </a>
        </div>

        <!-- Contenido -->
        <div class="p-6 space-y-5">
            <!-- Filtro de búsqueda -->
            <div class="relative w-full">
                <span
                    class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                <input type="text" wire:model.live="search" placeholder="Buscar rol..."
                    class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
            </div>

            <!-- Tabla de roles -->
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
                                                <span
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 bg-gray-100 text-gray-700 rounded-full text-xs">
                                                    {{ $perm->name }}
                                                </span>
                                            @endforeach
                                            @if($role->permissions->count() > 3)
                                                <span class="text-xs text-gray-500">+{{ $role->permissions->count() - 3 }}
                                                    más</span>
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
                                            class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition"
                                            title="Editar">
                                            <span class="material-symbols-outlined text-lg">edit</span>
                                        </a>
                                        <button wire:click="delete({{ $role->id }})"
                                            onclick="confirm('¿Eliminar?') || event.stopImmediatePropagation()"
                                            class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition"
                                            title="Eliminar">
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

            <!-- Paginación -->
            @if($roles->hasPages())
                <div class="mt-5">
                    {{ $roles->links() }}
                </div>
            @endif

            <!-- Mensajes de sesión (estilo unificado) -->
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