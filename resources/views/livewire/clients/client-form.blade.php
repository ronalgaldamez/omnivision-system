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

    <!-- Teléfonos dinámicos -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
            <span class="material-symbols-outlined text-gray-400 text-base">call</span>
            Teléfonos *
        </label>
        <div class="space-y-2">
            @foreach($phones as $index => $phone)
                <div class="flex items-start gap-2">
                    <div class="flex-1 relative">
                        <input type="text" wire:model="phones.{{ $index }}.number"
                            class="w-full pl-9 pr-3 py-2 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm"
                            placeholder="Número de teléfono">
                        <span
                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">phone</span>
                        @error('phones.' . $index . '.number') <span
                        class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div class="w-32">
                        <select wire:model="phones.{{ $index }}.type"
                            class="w-full py-2 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                            <option value="personal">Personal</option>
                            <option value="casa">Casa</option>
                            <option value="referencia">Referencia</option>
                            <option value="trabajo">Trabajo</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                    @if(count($phones) > 1)
                        <button type="button" wire:click="removePhone({{ $index }})"
                            class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition flex-shrink-0"
                            title="Eliminar teléfono">
                            <span class="material-symbols-outlined text-lg">delete</span>
                        </button>
                    @endif
                </div>
            @endforeach
        </div>
        <button type="button" wire:click="addPhone"
            class="mt-2 inline-flex items-center gap-1 text-sm text-blue-600 hover:text-blue-800 transition">
            <span class="material-symbols-outlined text-base">add_circle</span>
            Agregar otro teléfono
        </button>
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