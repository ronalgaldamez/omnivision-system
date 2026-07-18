<div class="space-y-6">
    {{-- Nombre --}}
    <x-ui.input type="text" wire:model.blur="name" name="name" icon="edit_note" label="Nombre" required
        placeholder="Nombre completo del cliente" />

    {{-- Tipo y número de documento --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <x-ui.select wire:model="document_type" name="document_type" icon="description" label="Tipo de documento"
            placeholder="Seleccionar tipo">
            @foreach($documentTypesList as $dt)
                <option value="{{ $dt }}">{{ $dt }}</option>
            @endforeach
        </x-ui.select>
        <div x-data>
            <x-ui.input type="text" wire:model="document_number" name="document_number" icon="tag"
                label="Número de documento" placeholder="00000000-0" maxlength="10"
                x-on:input="if ($event.isTrusted) { let val = $el.value.replace(/[^0-9]/g, '').slice(0,9); if (val.length > 8) val = val.slice(0,8) + '-' + val.slice(8); $el.value = val; $el.dispatchEvent(new Event('input', { bubbles: true })); }" />
        </div>
    </div>

    {{-- Teléfonos --}}
    <div class="pb-5 border-b border-gray-100">
        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-3 flex items-center gap-2">
            <span class="material-symbols-outlined text-gray-500 text-sm">call</span>
            Teléfonos
        </label>
        <div class="space-y-3">
            <div x-data>
                <x-ui.input type="text" wire:model="phone" name="phone" icon="phone" placeholder="0000-0000"
                    maxlength="9"
                    x-on:input="if ($event.isTrusted) { let val = $el.value.replace(/[^0-9]/g, '').slice(0,8); if (val.length > 4) val = val.slice(0,4) + '-' + val.slice(4); $el.value = val; $el.dispatchEvent(new Event('input', { bubbles: true })); }" />
            </div>
            @foreach($phones as $index => $phone)
                <div class="flex items-start gap-2" x-data>
                    <div class="flex-1">
                        <x-ui.input type="text" wire:model="phones.{{ $index }}.number" icon="phone" placeholder="0000-0000"
                            maxlength="9"
                            x-on:input="if ($event.isTrusted) { let val = $el.value.replace(/[^0-9]/g, '').slice(0,8); if (val.length > 4) val = val.slice(0,4) + '-' + val.slice(4); $el.value = val; $el.dispatchEvent(new Event('input', { bubbles: true })); }" />
                    </div>
                    <div class="w-32">
                        <select wire:model="phones.{{ $index }}.type"
                            class="w-full px-3 py-2.5 rounded-lg border border-gray-200 bg-gray-50 text-gray-900 text-sm focus:border-gray-400 focus:bg-white focus:outline-none transition">
                            <option value="personal">Personal</option>
                            <option value="casa">Casa</option>
                            <option value="referencia">Referencia</option>
                            <option value="trabajo">Trabajo</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                    @if(count($phones) > 1)
                        <button type="button" wire:click="removePhone({{ $index }})"
                            class="p-2.5 text-red-600 hover:bg-red-50 rounded-lg transition flex-shrink-0"
                            title="Eliminar teléfono">
                            <span class="material-symbols-outlined text-lg">delete</span>
                        </button>
                    @endif
                </div>
            @endforeach
        </div>
        <button type="button" wire:click="addPhone"
            class="mt-3 inline-flex items-center gap-1.5 text-sm text-gray-700 hover:text-gray-900 font-medium transition">
            <span class="material-symbols-outlined text-base">add_circle_outline</span>
            Agregar otro teléfono
        </button>
    </div>

    {{-- Correo electrónico --}}
    <x-ui.input type="email" wire:model.live="email" name="email" icon="alternate_email" label="Correo electrónico"
        placeholder="correo@ejemplo.com" />

    {{-- Departamento, Municipio, Distrito --}}
    <div class="pb-5 border-b border-gray-100">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <x-ui.select wire:model.live="departamento_id" name="departamento_id" label="Departamento"
                placeholder="Seleccionar departamento">
                @foreach($availableDepartamentos as $dep)
                    <option value="{{ $dep['id'] }}">{{ $dep['name'] }}</option>
                @endforeach
            </x-ui.select>
            <x-ui.select wire:model.live="municipio_id" name="municipio_id" label="Municipio"
                placeholder="Seleccionar municipio" :disabled="empty($availableMunicipios)">
                @foreach($availableMunicipios as $mun)
                    <option value="{{ $mun['id'] }}">{{ $mun['name'] }}</option>
                @endforeach
            </x-ui.select>
            <x-ui.select wire:model.live="distrito_id" name="distrito_id" label="Distrito / Localidad"
                placeholder="Seleccionar distrito" :disabled="empty($availableDistritos)">
                @foreach($availableDistritos as $dis)
                    <option value="{{ $dis['id'] }}">{{ $dis['name'] }}</option>
                @endforeach
            </x-ui.select>
        </div>
    </div>

    {{-- Dirección --}}
    <x-ui.textarea wire:model="address" name="address" icon="edit_note" label="Dirección" rows="2"
        placeholder="Dirección del cliente" />

    {{-- Notas --}}
    <x-ui.textarea wire:model="notes" name="notes" icon="edit_note" label="Notas" rows="2"
        placeholder="Observaciones internas..." />

    {{-- Botones --}}
    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
        <x-ui.button type="button" variant="secondary" wire:click="$parent.closeClientModal()">Cancelar</x-ui.button>
        <x-ui.button type="button" variant="primary" icon="save" wire:click="save">Guardar cliente</x-ui.button>
    </div>
</div>