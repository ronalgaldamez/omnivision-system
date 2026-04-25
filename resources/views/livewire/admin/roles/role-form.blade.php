<div class="max-w-3xl mx-auto">
    <!-- Tarjeta principal -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <!-- Encabezado con fondo sutil -->
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50">
            <h1 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                <span class="material-symbols-outlined text-gray-500">
                    {{ $roleId ? 'edit' : 'add_circle' }}
                </span>
                {{ $roleId ? 'Editar' : 'Nuevo' }} Rol
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                {{ $roleId ? 'Modifica los datos y permisos del rol' : 'Registra un nuevo rol y asigna sus permisos' }}
            </p>
        </div>

        <!-- Contenido del formulario -->
        <div class="p-6">
            <form wire:submit="save" class="space-y-6">
                <!-- Nombre del Rol -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-gray-400 text-base">badge</span>
                        Nombre del Rol
                    </label>
                    <div class="relative">
                        <input type="text" wire:model="name"
                            class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm"
                            placeholder="Ej: administrador, técnico, reportes...">
                        <span
                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">edit_note</span>
                    </div>
                    @error('name') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Permisos -->
                <div class="border-t border-gray-200 pt-6">
                    <h2 class="text-md font-semibold text-gray-800 flex items-center gap-2 mb-4">
                        <span class="material-symbols-outlined text-gray-500">lock</span>
                        Permisos
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                        @foreach($permissions as $perm)
                            <label
                                class="flex items-center gap-2.5 p-2.5 bg-gray-50/80 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-100 transition">
                                <input type="checkbox" value="{{ $perm->name }}" wire:model="selectedPermissions"
                                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-2 focus:ring-blue-500/20 w-4 h-4">
                                <span class="text-sm text-gray-700">{{ $perm->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('selectedPermissions') <span class="text-xs text-red-500 mt-2 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Botones de acción -->
                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('admin.roles.index') }}"
                        class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-300 transition shadow-sm">
                        Cancelar
                    </a>
                    <button type="submit"
                        class="px-5 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 transition inline-flex items-center gap-2">
                        <span class="material-symbols-outlined text-base">save</span>
                        {{ $roleId ? 'Actualizar' : 'Guardar' }}
                    </button>
                </div>
            </form>

            <!-- Mensajes de sesión -->
            @if(session('message'))
                <div
                    class="mt-4 flex items-center gap-2 text-sm text-green-700 bg-green-50 px-4 py-3 rounded-lg border border-green-200">
                    <span class="material-symbols-outlined text-green-600">check_circle</span>
                    {{ session('message') }}
                </div>
            @endif
        </div>
    </div>
</div>