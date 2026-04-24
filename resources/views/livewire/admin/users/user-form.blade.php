<div class="max-w-2xl mx-auto bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <h1 class="text-lg font-semibold mb-4">Nuevo Usuario</h1>
    <form wire:submit="save" class="space-y-4">
        <div>
            <label>Nombre</label>
            <input type="text" wire:model="name" class="w-full border rounded">
            @error('name') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
        </div>
        <div>
            <label>Email</label>
            <input type="email" wire:model="email" class="w-full border rounded">
            @error('email') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
        </div>
        <div>
            <label>Contraseña</label>
            <input type="password" wire:model="password" class="w-full border rounded">
            @error('password') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
        </div>
        <div>
            <label>Confirmar contraseña</label>
            <input type="password" wire:model="password_confirmation" class="w-full border rounded">
        </div>
        <div>
            <label>Rol</label>
            <select wire:model="selectedRole" class="w-full border rounded">
                <option value="">Seleccione</option>
                @foreach($roles as $role)
                    <option value="{{ $role->name }}">{{ $role->name }}</option>
                @endforeach
            </select>
            @error('selectedRole') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Guardar</button>
    </form>
    @if(session('message'))
        <div class="mt-2 text-green-600">{{ session('message') }}</div>
    @endif
</div>