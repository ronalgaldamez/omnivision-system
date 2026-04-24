<div class="max-w-2xl mx-auto bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <h1 class="text-lg font-semibold mb-4">Nuevo Usuario</h1>
    <form wire:submit="save" class="space-y-4">
        <div>
            <label class="block text-sm font-medium">Nombre</label>
            <input type="text" wire:model="name" class="mt-1 w-full rounded-md border-gray-300">
            @error('name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium">Email</label>
            <input type="email" wire:model="email" class="mt-1 w-full rounded-md border-gray-300">
            @error('email') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium">Contraseña</label>
            <input type="password" wire:model="password" class="mt-1 w-full rounded-md border-gray-300">
            @error('password') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium">Confirmar contraseña</label>
            <input type="password" wire:model="password_confirmation" class="mt-1 w-full rounded-md border-gray-300">
        </div>
        <div>
            <label class="block text-sm font-medium">Rol</label>
            <select wire:model="selectedRole" class="mt-1 w-full rounded-md border-gray-300">
                <option value="">Seleccione</option>
                @foreach($roles as $role)
                    <option value="{{ $role->name }}">{{ $role->name }}</option>
                @endforeach
            </select>
            @error('selectedRole') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
        </div>
        <div class="flex justify-end space-x-2">
            <a href="{{ route('admin.users.index') }}"
                class="px-3 py-1.5 border border-gray-300 rounded-md text-sm hover:bg-gray-50">Cancelar</a>
            <button type="submit"
                class="px-3 py-1.5 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700">Guardar</button>
        </div>
    </form>
    @if(session('message'))
        <div class="mt-2 text-sm text-green-600">{{ session('message') }}</div>
    @endif
</div>