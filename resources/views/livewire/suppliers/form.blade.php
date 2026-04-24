<div class="max-w-2xl mx-auto bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <h1 class="text-lg font-semibold mb-4">{{ $supplierId ? 'Editar' : 'Nuevo' }} Proveedor</h1>
    <form wire:submit="save" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">Nombre *</label>
            <input type="text" wire:model="name" class="mt-1 w-full rounded-md border-gray-300 focus:border-blue-300">
            @error('name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Persona de contacto</label>
            <input type="text" wire:model="contact_name" class="mt-1 w-full rounded-md border-gray-300">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Teléfono</label>
            <input type="text" wire:model="phone" class="mt-1 w-full rounded-md border-gray-300">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" wire:model="email" class="mt-1 w-full rounded-md border-gray-300">
            @error('email') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Dirección</label>
            <textarea wire:model="address" rows="2" class="mt-1 w-full rounded-md border-gray-300"></textarea>
        </div>
        <div class="flex justify-end space-x-2 pt-2">
            <a href="{{ route('suppliers.index') }}"
                class="px-3 py-1.5 border border-gray-300 rounded-md text-sm hover:bg-gray-50">Cancelar</a>
            <button type="submit"
                class="px-3 py-1.5 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700">Guardar</button>
        </div>
    </form>
    @if(session('message'))
    <div class="mt-2 text-sm text-green-600">{{ session('message') }}</div> @endif
</div>