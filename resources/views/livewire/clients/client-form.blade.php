<div class="space-y-5">
    <!-- Nombre -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
            <span class="material-symbols-outlined text-gray-400 text-base">badge</span>
            Nombre *
        </label>
        <div class="relative">
            <input type="text" wire:model="name"
                class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm"
                placeholder="Nombre del cliente">
            <span
                class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">edit_note</span>
        </div>
        @error('name') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
    </div>

    <!-- Teléfono -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
            <span class="material-symbols-outlined text-gray-400 text-base">call</span>
            Teléfono
        </label>
        <div class="relative">
            <input type="text" wire:model="phone"
                class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm"
                placeholder="Número de teléfono">
            <span
                class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">phone</span>
        </div>
    </div>

    <!-- Dirección -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
            <span class="material-symbols-outlined text-gray-400 text-base">location_on</span>
            Dirección
        </label>
        <div class="relative">
            <textarea wire:model="address" rows="2"
                class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm resize-none"
                placeholder="Dirección del cliente"></textarea>
            <span class="material-symbols-outlined absolute left-3 top-2.5 text-gray-400 text-lg">edit_note</span>
        </div>
    </div>

    <!-- Servicio contratado -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
            <span class="material-symbols-outlined text-gray-400 text-base">tv</span>
            Servicio contratado
        </label>
        <div class="relative">
            <input type="text" wire:model="service_contracted"
                class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm"
                placeholder="Ej: Internet, Cable, IPTV, etc.">
            <span
                class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">devices</span>
        </div>
    </div>

    <!-- Botones de acción -->
    <div class="flex justify-end gap-3 pt-2">
        <button type="button" wire:click="$parent.closeClientModal()"
            class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-300 transition shadow-sm">
            Cancelar
        </button>
        <button type="button" wire:click="save"
            class="px-5 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 transition inline-flex items-center gap-2">
            <span class="material-symbols-outlined text-base">save</span>
            Guardar cliente
        </button>
    </div>
</div>