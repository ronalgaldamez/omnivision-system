<div>
    <div class="max-w-3xl mx-auto">
    <!-- Tarjeta principal -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <!-- Encabezado con fondo sutil -->
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50">
            <h1 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                <span class="material-symbols-outlined text-gray-500">
                    {{ $clientId ? 'edit' : 'add_circle' }}
                </span>
                {{ $clientId ? 'Editar' : 'Nuevo' }} Cliente
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                {{ $clientId ? 'Modifica los datos del cliente' : 'Registra un nuevo cliente en el sistema' }}
            </p>
        </div>

        <!-- Contenido del formulario -->
        <div class="p-6">
            <form wire:submit="promptSave" class="space-y-6">
                <!-- Nombre -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-gray-400 text-base">badge</span>
                        Nombre *
                    </label>
                    <div class="relative" x-data>
                        <input type="text" wire:model="name"
                            x-on:input="if ($event.isTrusted) { $el.value = $el.value.replace(/[^a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s]/g, ''); $el.dispatchEvent(new Event('input', { bubbles: true })); }"
                            class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm"
                            placeholder="Nombre completo del cliente">
                        <span
                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">edit_note</span>
                    </div>
                    @error('name') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Tipo y número de documento -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-gray-400 text-base">fingerprint</span>
                            Tipo de documento
                        </label>
                        <div class="relative">
                            <select wire:model="document_type"
                                class="w-full pl-9 pr-8 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm appearance-none">
                                <option value="">Seleccionar tipo</option>
                                <option value="dui">DUI</option>
                            </select>
                            <span
                                class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">description</span>
                            <span
                                class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-gray-400 text-base">pin</span>
                            Número de documento
                        </label>
                        <div class="relative" x-data>
                            <input type="text" wire:model="document_number"
                                x-on:input="
                                    if ($event.isTrusted) {
                                        let val = $el.value.replace(/[^0-9]/g, '').slice(0,9);
                                        if (val.length > 8) val = val.slice(0,8) + '-' + val.slice(8);
                                        $el.value = val;
                                        $el.dispatchEvent(new Event('input', { bubbles: true }));
                                    }
                                "
                                class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm"
                                placeholder="00000000-0" maxlength="10">
                            <span
                                class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">tag</span>
                        </div>
                        @error('document_number') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Teléfono principal -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-gray-400 text-base">call</span>
                        Teléfono principal
                    </label>
                    <div class="relative" x-data>
                        <input type="text" wire:model="phone"
                            x-on:input="
                                if ($event.isTrusted) {
                                    let val = $el.value.replace(/[^0-9]/g, '').slice(0,8);
                                    if (val.length > 4) val = val.slice(0,4) + '-' + val.slice(4);
                                    $el.value = val;
                                    $el.dispatchEvent(new Event('input', { bubbles: true }));
                                }
                            "
                            class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm"
                            placeholder="0000-0000" maxlength="9">
                        <span
                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">phone</span>
                    </div>
                    @error('phone') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Teléfonos adicionales (dinámicos) -->
                @if(count($phones) > 0)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-gray-400 text-base">phonelink</span>
                            Teléfonos adicionales
                        </label>
                        <div class="space-y-2">
                            @foreach($phones as $index => $phone)
                                <div class="flex items-start gap-2" x-data>
                                    <div class="flex-1 relative">
                                        <input type="text" wire:model="phones.{{ $index }}.number"
                                            x-on:input="
                                                if ($event.isTrusted) {
                                                    let val = $el.value.replace(/[^0-9]/g, '').slice(0,8);
                                                    if (val.length > 4) val = val.slice(0,4) + '-' + val.slice(4);
                                                    $el.value = val;
                                                    $el.dispatchEvent(new Event('input', { bubbles: true }));
                                                }
                                            "
                                            class="w-full pl-9 pr-3 py-2 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm"
                                            placeholder="0000-0000" maxlength="9">
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
                                    <button type="button" wire:click="removePhone({{ $index }})"
                                        class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition flex-shrink-0"
                                        title="Eliminar teléfono">
                                        <span class="material-symbols-outlined text-lg">delete</span>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <button type="button" wire:click="addPhone"
                    class="inline-flex items-center gap-1 text-sm text-blue-600 hover:text-blue-800 transition">
                    <span class="material-symbols-outlined text-base">add_circle</span>
                    Agregar otro teléfono
                </button>

                <!-- Correo electrónico -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-gray-400 text-base">mail</span>
                        Correo electrónico
                    </label>
                    <div class="relative">
                        <input type="email" wire:model="email"
                            class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm"
                            placeholder="correo@ejemplo.com">
                        <span
                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">alternate_email</span>
                    </div>
                    @error('email') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
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
                        <span
                            class="material-symbols-outlined absolute left-3 top-2.5 text-gray-400 text-lg">edit_note</span>
                    </div>
                </div>

                <!-- Dirección de instalación -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-gray-400 text-base">home_pin</span>
                        Dirección de instalación
                    </label>
                    <div class="relative">
                        <textarea wire:model="installation_address" rows="2"
                            class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm resize-none"
                            placeholder="Dirección donde se instalará el servicio"></textarea>
                        <span
                            class="material-symbols-outlined absolute left-3 top-2.5 text-gray-400 text-lg">edit_note</span>
                    </div>
                </div>

                <!-- Coordenadas (solo visibles si el usuario tiene el permiso) -->
                @can('capture coordinates')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-gray-400 text-base">explore</span>
                            Latitud
                        </label>
                        <div class="relative" x-data="{
                            formatCoordinate(value) {
                                let clean = value.replace(/[^0-9]/g, '');
                                if (clean.length > 2) {
                                    clean = clean.slice(0, 2) + '.' + clean.slice(2, 6);
                                }
                                return clean;
                            }
                        }">
                            <input type="text" wire:model="latitude"
                                x-on:input="$el.value = formatCoordinate($el.value); $el.dispatchEvent(new Event('input', { bubbles: true }));"
                                class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm"
                                placeholder="00.0000" maxlength="7" inputmode="decimal">
                            <span
                                class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">pin_drop</span>
                        </div>
                        @error('latitude') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-gray-400 text-base">explore</span>
                            Longitud
                        </label>
                        <div class="relative" x-data="{
                            formatCoordinate(value) {
                                let hasMinus = value.startsWith('-');
                                let clean = value.replace(/[^0-9]/g, '');
                                if (clean.length > 2) {
                                    clean = clean.slice(0, 2) + '.' + clean.slice(2, 6);
                                }
                                return (hasMinus ? '-' : '') + clean;
                            }
                        }">
                            <input type="text" wire:model="longitude"
                                x-on:input="$el.value = formatCoordinate($el.value); $el.dispatchEvent(new Event('input', { bubbles: true }));"
                                class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm"
                                placeholder="-00.0000" maxlength="8" inputmode="decimal">
                            <span
                                class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">pin_drop</span>
                        </div>
                        @error('longitude') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                @endcan

                <!-- NC (Número de Contrato) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-gray-400 text-base">bolt</span>
                        NC
                    </label>
                    <div class="relative" x-data>
                        <input type="text" wire:model="nro_luz"
                            x-on:input="if ($event.isTrusted) { $el.value = $el.value.replace(/[^0-9]/g, '').slice(0,12); $el.dispatchEvent(new Event('input', { bubbles: true })); }"
                            class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm"
                            placeholder="000000000000" maxlength="12">
                        <span
                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">electric_meter</span>
                    </div>
                    @error('nro_luz') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Servicio contratado -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-gray-400 text-base">tv</span>
                        Servicio contratado
                    </label>
                    <div class="relative">
                        <input type="text" wire:model="service"
                            class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm"
                            placeholder="Ej: Internet, Cable, IPTV, etc.">
                        <span
                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">devices</span>
                    </div>
                </div>

                <!-- Notas -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-gray-400 text-base">sticky_note_2</span>
                        Notas
                    </label>
                    <div class="relative">
                        <textarea wire:model="notes" rows="2"
                            class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm resize-none"
                            placeholder="Observaciones internas..."></textarea>
                        <span
                            class="material-symbols-outlined absolute left-3 top-2.5 text-gray-400 text-lg">edit_note</span>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" wire:click="promptCancel"
                        class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-300 transition shadow-sm">
                        Cancelar
                    </button>
                    <button type="button" wire:click="promptClear"
                        class="px-5 py-2.5 border border-red-300 rounded-lg text-sm font-medium text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-300 transition shadow-sm inline-flex items-center gap-2">
                        <span class="material-symbols-outlined text-base">delete_sweep</span>
                        Limpiar
                    </button>
                    <button type="submit"
                        class="px-5 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 transition inline-flex items-center gap-2">
                        <span class="material-symbols-outlined text-base">save</span>
                        {{ $clientId ? 'Actualizar' : 'Guardar' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal confirmar guardar --}}
@if($confirmingSave)
    <div x-data="{ open: true }" x-show="open" x-cloak
        class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
        style="display: none;">
        <div class="relative mx-auto p-5 w-full max-w-md">
            <div class="bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden">
                <div class="p-6 text-center">
                    <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-blue-100 mb-4">
                        <span class="material-symbols-outlined text-blue-600 text-2xl">help</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Confirmar acción</h3>
                    <p class="text-sm text-gray-600 mt-2">{{ $clientId ? '¿Guardar los cambios del cliente?' : '¿Registrar este nuevo cliente?' }}</p>
                </div>
                <div class="bg-gray-50 px-6 py-4 flex flex-col gap-3 sm:flex-row-reverse">
                    <button type="button" wire:click="executeSave"
                        class="w-full sm:w-auto px-5 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 transition inline-flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-base">check</span>
                        Sí, continuar
                    </button>
                    <button type="button" @click="open = false" wire:click="cancelSave"
                        class="w-full sm:w-auto px-5 py-2.5 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-300 transition shadow-sm">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif

{{-- Modal confirmar limpiar --}}
@if($confirmingClear)
    <div x-data="{ open: true }" x-show="open" x-cloak
        class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
        style="display: none;">
        <div class="relative mx-auto p-5 w-full max-w-md">
            <div class="bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden">
                <div class="p-6 text-center">
                    <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-red-100 mb-4">
                        <span class="material-symbols-outlined text-red-600 text-2xl">delete_sweep</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Limpiar campos</h3>
                    <p class="text-sm text-gray-600 mt-2">¿Estás seguro de limpiar todos los campos? Se perderán los datos ingresados.</p>
                </div>
                <div class="bg-gray-50 px-6 py-4 flex flex-col gap-3 sm:flex-row-reverse">
                    <button type="button" wire:click="executeClear"
                        class="w-full sm:w-auto px-5 py-2.5 bg-red-600 text-white rounded-lg text-sm font-medium shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1 transition inline-flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-base">delete_sweep</span>
                        Sí, limpiar
                    </button>
                    <button type="button" @click="open = false" wire:click="cancelClear"
                        class="w-full sm:w-auto px-5 py-2.5 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-300 transition shadow-sm">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif

{{-- Modal confirmar cancelar (Alpine) --}}
<div x-data="{ showCancelModal: false }"
    x-on:confirm-cancel.window="showCancelModal = true"
    x-show="showCancelModal" x-cloak
    class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
    style="display: none;">
    <div class="relative mx-auto p-5 w-full max-w-md">
        <div class="bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden">
            <div class="p-6 text-center">
                <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-gray-100 mb-4">
                    <span class="material-symbols-outlined text-gray-600 text-2xl">arrow_back</span>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">¿Salir del formulario?</h3>
                <p class="text-sm text-gray-600 mt-2">Los cambios no guardados se perderán.</p>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex flex-col gap-3 sm:flex-row-reverse">
                <button type="button" wire:click="executeCancel"
                    class="w-full sm:w-auto px-5 py-2.5 bg-gray-600 text-white rounded-lg text-sm font-medium shadow-sm hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-1 transition inline-flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-base">logout</span>
                    Salir
                </button>
                <button type="button" @click="showCancelModal = false"
                    class="w-full sm:w-auto px-5 py-2.5 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-300 transition shadow-sm">
                    Seguir editando
                </button>
            </div>
        </div>
    </div>
</div>
</div>