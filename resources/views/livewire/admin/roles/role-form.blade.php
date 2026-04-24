<div class="max-w-3xl mx-auto bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <h1 class="text-lg font-semibold mb-4">{{ $roleId ? 'Editar' : 'Nuevo' }} Rol</h1>
    <form wire:submit="save" class="space-y-4">
        <div>
            <label class="block text-sm font-medium">Nombre del Rol</label>
            <input type="text" wire:model="name" class="mt-1 w-full rounded-md border-gray-300">
            @error('name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
        </div>

        <div class="border-t pt-4">
            <h2 class="font-medium mb-2">Permisos</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                @foreach($permissions as $perm)
                    <label class="flex items-center space-x-2 text-sm">
                        <input type="checkbox" value="{{ $perm->name }}" wire:model="selectedPermissions"
                            class="rounded border-gray-300 text-blue-600">
                        <span>{{ $perm->name }}</span>
                    </label>
                @endforeach
            </div>
            @error('selectedPermissions') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
        </div>

        <div class="flex justify-end space-x-2 pt-2">
            <a href="{{ route('admin.roles.index') }}"
                class="px-3 py-1.5 border border-gray-300 rounded-md text-sm hover:bg-gray-50">Cancelar</a>
            <button type="submit"
                class="px-3 py-1.5 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700">Guardar</button>
        </div>
    </form>
    @if(session('message'))
    <div class="mt-2 text-sm text-green-600">{{ session('message') }}</div> @endif
</div>