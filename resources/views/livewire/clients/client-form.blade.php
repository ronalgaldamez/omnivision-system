<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium">Nombre *</label>
        <input type="text" wire:model="name" class="mt-1 w-full rounded-md border-gray-300">
        @error('name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
    </div>
    <div>
        <label class="block text-sm font-medium">Teléfono</label>
        <input type="text" wire:model="phone" class="mt-1 w-full rounded-md border-gray-300">
    </div>
    <div>
        <label class="block text-sm font-medium">Dirección</label>
        <textarea wire:model="address" rows="2" class="mt-1 w-full rounded-md border-gray-300"></textarea>
    </div>
    <div>
        <label class="block text-sm font-medium">Servicio contratado</label>
        <input type="text" wire:model="service_contracted" class="mt-1 w-full rounded-md border-gray-300"
            placeholder="Ej: Internet, Cable, IPTV, etc.">
    </div>
    <div class="flex justify-end space-x-2 pt-2">
        <button type="button" @click="$dispatch('closeModal')"
            class="px-3 py-1.5 border border-gray-300 rounded-md text-sm hover:bg-gray-50">Cancelar</button>
        <button type="button" wire:click="save"
            class="px-3 py-1.5 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700">Guardar cliente</button>
    </div>
</div>