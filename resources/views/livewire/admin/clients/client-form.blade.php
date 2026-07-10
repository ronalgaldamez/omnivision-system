<div>
    <div class="max-w-3xl mx-auto">
        <x-ui.card :icon="$clientId ? 'edit' : 'add_circle'" :title="($clientId ? 'Editar' : 'Nuevo') . ' Cliente'"
            :subtitle="$clientId ? 'Modifica los datos del cliente' : 'Registra un nuevo cliente en el sistema'">

            <form wire:submit="promptSave" class="space-y-6">
                {{-- Tipo de servicio rápido --}}
                <div class="pb-5 border-b border-gray-100">
                    <x-ui.select wire:model.live="service_type_id" icon="handyman" label="Tipo de servicio">
                        @foreach(\App\Models\ServiceType::orderBy('name')->get() as $st)
                            <option value="{{ $st->id }}">{{ str_replace('_', ' ', $st->name) }}</option>
                        @endforeach
                    </x-ui.select>
                </div>

                {{-- Nombre --}}
                <div x-data>
                    <x-ui.input type="text" wire:model="name" icon="edit_note" label="Nombre" required
                        placeholder="Nombre completo del cliente"
                        x-on:input="if ($event.isTrusted) { $el.value = $el.value.replace(/[^a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s]/g, ''); $el.dispatchEvent(new Event('input', { bubbles: true })); }" />
                </div>

                {{-- Tipo y número de documento --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-ui.select wire:model="document_type" icon="description" label="Tipo de documento" placeholder="Seleccionar tipo">
                        <option value="DUI">DUI</option>
                        <option value="NIT">NIT</option>
                        <option value="Pasaporte">Pasaporte</option>
                        <option value="Otro">Otro</option>
                    </x-ui.select>
                    <div x-data>
                        <x-ui.input type="text" wire:model="document_number" icon="tag" label="Número de documento"
                            placeholder="00000000-0" maxlength="10"
                            x-on:input="if ($event.isTrusted) { let val = $el.value.replace(/[^0-9]/g, '').slice(0,9); if (val.length > 8) val = val.slice(0,8) + '-' + val.slice(8); $el.value = val; $el.dispatchEvent(new Event('input', { bubbles: true })); }" />
                    </div>
                </div>

                {{-- Teléfono principal --}}
                <div class="pb-5 border-b border-gray-100" x-data>
                    <x-ui.input type="text" wire:model="phone" icon="phone" label="Teléfono principal"
                        placeholder="0000-0000" maxlength="9"
                        x-on:input="if ($event.isTrusted) { let val = $el.value.replace(/[^0-9]/g, '').slice(0,8); if (val.length > 4) val = val.slice(0,4) + '-' + val.slice(4); $el.value = val; $el.dispatchEvent(new Event('input', { bubbles: true })); }" />
                </div>

                {{-- Teléfonos adicionales --}}
                @if(count($phones) > 0)
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-3 flex items-center gap-2">
                            <span class="material-symbols-outlined text-gray-500 text-sm">phonelink</span>
                            Teléfonos adicionales
                        </label>
                        <div class="space-y-3">
                            @foreach($phones as $index => $phone)
                                <div class="flex items-start gap-2" x-data>
                                    <div class="flex-1">
                                        <x-ui.input type="text" wire:model="phones.{{ $index }}.number" icon="phone"
                                            placeholder="0000-0000" maxlength="9"
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
                                    <button type="button" wire:click="removePhone({{ $index }})"
                                        class="p-2.5 text-red-600 hover:bg-red-50 rounded-lg transition flex-shrink-0"
                                        title="Eliminar teléfono">
                                        <span class="material-symbols-outlined text-lg">delete</span>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <button type="button" wire:click="addPhone"
                    class="inline-flex items-center gap-1.5 text-sm text-gray-700 hover:text-gray-900 font-medium transition">
                    <span class="material-symbols-outlined text-base">add_circle_outline</span>
                    Agregar otro teléfono
                </button>

                {{-- Correo electrónico --}}
                <x-ui.input type="email" wire:model="email" icon="alternate_email" label="Correo electrónico" placeholder="correo@ejemplo.com" />

                <x-ui.checkbox wire:model.live="accepts_promotions" label="Acepta recibir promociones o facturas electrónicas" for="accepts_promotions" />

                {{-- Departamento, Municipio, Distrito --}}
                <div class="pb-5 border-b border-gray-100">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <x-ui.select wire:model.live="departamento_id" label="Departamento" placeholder="Seleccionar departamento">
                            @foreach($availableDepartamentos as $dep)
                                <option value="{{ $dep['id'] }}">{{ $dep['name'] }}</option>
                            @endforeach
                        </x-ui.select>
                        <x-ui.select wire:model.live="municipio_id" label="Municipio" placeholder="Seleccionar municipio"
                            :disabled="empty($availableMunicipios)">
                            @foreach($availableMunicipios as $mun)
                                <option value="{{ $mun['id'] }}">{{ $mun['name'] }}</option>
                            @endforeach
                        </x-ui.select>
                        <x-ui.select wire:model.live="distrito_id" label="Distrito / Localidad" placeholder="Seleccionar distrito"
                            :disabled="empty($availableDistritos)">
                            @foreach($availableDistritos as $dis)
                                <option value="{{ $dis['id'] }}">{{ $dis['name'] }}</option>
                            @endforeach
                        </x-ui.select>
                    </div>
                </div>

                {{-- Dirección --}}
                <x-ui.textarea wire:model="address" icon="edit_note" label="Dirección" rows="2" placeholder="Dirección del cliente" />

                {{-- Dirección de instalación --}}
                <x-ui.textarea wire:model="installation_address" icon="edit_note" label="Dirección de instalación" rows="2"
                    placeholder="Dirección donde se instalará el servicio" />

                {{-- Coordenadas --}}
                @can('capture coordinates')
                    <div class="pb-5 border-b border-gray-100">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div x-data="{
                                formatCoordinate(value) {
                                    let clean = value.replace(/[^0-9]/g, '');
                                    if (clean.length > 2) { clean = clean.slice(0, 2) + '.' + clean.slice(2, 6); }
                                    return clean;
                                }
                            }">
                                <x-ui.input type="text" wire:model="latitude" icon="pin_drop" label="Latitud"
                                    placeholder="00.0000" maxlength="7" inputmode="decimal"
                                    x-on:input="$el.value = formatCoordinate($el.value); $el.dispatchEvent(new Event('input', { bubbles: true }));" />
                            </div>
                            <div x-data="{
                                formatCoordinate(value) {
                                    let hasMinus = value.startsWith('-');
                                    let clean = value.replace(/[^0-9]/g, '');
                                    if (clean.length > 2) { clean = clean.slice(0, 2) + '.' + clean.slice(2, 6); }
                                    return (hasMinus ? '-' : '') + clean;
                                }
                            }">
                                <x-ui.input type="text" wire:model="longitude" icon="pin_drop" label="Longitud"
                                    placeholder="-00.0000" maxlength="8" inputmode="decimal"
                                    x-on:input="$el.value = formatCoordinate($el.value); $el.dispatchEvent(new Event('input', { bubbles: true }));" />
                            </div>
                        </div>
                    </div>
                @endcan

                {{-- NC --}}
                <div x-data>
                    <x-ui.input type="text" wire:model="nro_luz" icon="electric_meter" label="NC"
                        placeholder="000000000000" maxlength="12"
                        x-on:input="if ($event.isTrusted) { $el.value = $el.value.replace(/[^0-9]/g, '').slice(0,12); $el.dispatchEvent(new Event('input', { bubbles: true })); }" />
                </div>

                <x-ui.checkbox wire:model.live="no_price" label="Sin precio" for="no_price" />
                @if($no_price)
                    <p class="text-xs text-gray-500 -mt-3 italic">Nota: más adelante se le puede asignar el paquete al cliente.</p>
                @endif

                @if(!$no_price)
                    <div class="bg-gray-50 rounded-lg border border-gray-200 p-4 space-y-4">
                        <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wide flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm">assignment</span>
                            Servicio contratado
                            <span class="text-xs font-normal text-gray-500 normal-case tracking-normal">(opcional)</span>
                        </h3>

                        <x-ui.select wire:model.live="branch_id" icon="business" label="Sucursal" placeholder="Seleccionar sucursal">
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </x-ui.select>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2 flex items-center gap-2">
                                <span class="material-symbols-outlined text-gray-500 text-sm">map</span>
                                Zona
                            </label>
                            <div class="space-y-2">
                                <x-ui.select wire:model.live="svc_departamento" placeholder="Departamento">
                                    @foreach($svcAvailableDepartamentos as $d)
                                        <option value="{{ $d['id'] }}">{{ $d['name'] }}</option>
                                    @endforeach
                                </x-ui.select>
                                @if($svcAvailableMunicipios)
                                    <x-ui.select wire:model.live="svc_municipio" placeholder="Municipio">
                                        @foreach($svcAvailableMunicipios as $m)
                                            <option value="{{ $m['id'] }}">{{ $m['name'] }}</option>
                                        @endforeach
                                    </x-ui.select>
                                @endif
                                @if($svcAvailableDistritos)
                                    <x-ui.select wire:model.live="svc_distrito" placeholder="Distrito / Localidad">
                                        @foreach($svcAvailableDistritos as $d)
                                            <option value="{{ $d['id'] }}">{{ $d['name'] }}</option>
                                        @endforeach
                                    </x-ui.select>
                                @endif
                                @if($svcAvailableSubzonas)
                                    <x-ui.select wire:model.live="svc_subzona" placeholder="Cantón / Barrio / Colonia">
                                        @foreach($svcAvailableSubzonas as $s)
                                            <option value="{{ $s['id'] }}">{{ $s['name'] }}</option>
                                        @endforeach
                                    </x-ui.select>
                                @endif
                            </div>
                        </div>

                        <x-ui.select wire:model.live="plan_id" label="Plan" placeholder="Seleccionar plan">
                            @foreach($availablePlans as $p)
                                <option value="{{ $p['id'] }}">
                                    {{ $p['name'] }} @if($p['speed'])({{ $p['speed'] }})@endif — ${{ number_format($p['price'], 2) }}
                                </option>
                            @endforeach
                        </x-ui.select>

                        @php $selPlan = collect($availablePlans)->firstWhere('id', $plan_id); @endphp
                        @if($selPlan)
                            <div class="bg-white rounded-lg border border-gray-200 p-4">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="text-sm font-bold text-gray-900">{{ $selPlan['name'] }}</p>
                                        <div class="flex flex-wrap gap-1.5 mt-2">
                                            @if(str_starts_with($selectedPlanOrigin, 'inherited:'))
                                                <x-ui.badge variant="neutral">Heredado de {{ substr($selectedPlanOrigin, 10) }}</x-ui.badge>
                                            @elseif($selectedPlanOrigin === 'base')
                                                <x-ui.badge variant="neutral">Precio base</x-ui.badge>
                                            @elseif($selectedPlanOrigin === 'override')
                                                <x-ui.badge variant="neutral">Precio de zona</x-ui.badge>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-lg font-bold text-gray-900">${{ number_format($selectedPlanPrice, 2) }}</p>
                                        @if($selPlan['base_price'] != $selPlan['price'])
                                            <p class="text-xs text-gray-500 mt-0.5">Base: ${{ number_format($selPlan['base_price'], 2) }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Historial de contratos --}}
                @if(!empty($contractHistory))
                    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/50">
                            <h4 class="text-xs font-bold text-gray-800 uppercase tracking-wide flex items-center gap-2">
                                <span class="material-symbols-outlined text-sm text-gray-500">history</span>
                                Historial de contratos
                            </h4>
                        </div>
                        <div class="divide-y divide-gray-100">
                            @foreach($contractHistory as $entry)
                                <div class="px-4 py-3 flex items-center justify-between text-sm">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <span class="material-symbols-outlined text-base text-gray-400 flex-shrink-0">assignment</span>
                                        <div class="min-w-0">
                                            <p class="font-medium text-gray-800 truncate">{{ $entry['plan_name'] }}</p>
                                            <p class="text-xs text-gray-500">{{ $entry['zone_name'] }} — {{ $entry['date'] }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3 flex-shrink-0">
                                        @if($entry['price'])
                                            <span class="text-sm font-semibold text-gray-900">${{ number_format($entry['price'], 2) }}</span>
                                        @endif
                                        <x-ui.badge :variant="match($entry['status']) { 'open' => 'warning', 'resolved' => 'success', 'cancelled' => 'danger', default => 'neutral' }">
                                            {{ ucfirst($entry['status']) }}
                                        </x-ui.badge>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <input type="hidden" wire:model="service">

                {{-- Notas --}}
                <x-ui.textarea wire:model="notes" icon="edit_note" label="Notas" rows="2" placeholder="Observaciones internas..." />

                {{-- Botones --}}
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                    <x-ui.button type="button" variant="secondary" wire:click="promptCancel">Cancelar</x-ui.button>
                    <x-ui.button type="button" variant="danger" icon="delete_sweep" wire:click="promptClear">Limpiar</x-ui.button>
                    <x-ui.button type="submit" variant="primary" icon="save">{{ $clientId ? 'Actualizar' : 'Guardar' }}</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>

    {{-- Modal confirmar guardar --}}
    @if($confirmingSave)
        <div x-data="{ open: true }" x-show="open" x-cloak
            class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
            style="display: none;">
            <div class="relative mx-auto p-5 w-full max-w-md">
                <x-ui.card overflow="visible">
                    <div class="text-center">
                        <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-blue-100 mb-4">
                            <span class="material-symbols-outlined text-blue-600 text-2xl">help</span>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Confirmar acción</h3>
                        <p class="text-sm text-gray-600 mt-2">{{ $clientId ? '¿Guardar los cambios del cliente?' : '¿Registrar este nuevo cliente?' }}</p>
                    </div>
                    <x-slot:footer>
                        <x-ui.button type="button" variant="primary" icon="check" wire:click="executeSave">Sí, continuar</x-ui.button>
                        <x-ui.button type="button" variant="secondary" @click="open = false" wire:click="cancelSave">Cancelar</x-ui.button>
                    </x-slot:footer>
                </x-ui.card>
            </div>
        </div>
    @endif

    {{-- Modal confirmar limpiar --}}
    @if($confirmingClear)
        <div x-data="{ open: true }" x-show="open" x-cloak
            class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
            style="display: none;">
            <div class="relative mx-auto p-5 w-full max-w-md">
                <x-ui.card overflow="visible">
                    <div class="text-center">
                        <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-red-100 mb-4">
                            <span class="material-symbols-outlined text-red-600 text-2xl">delete_sweep</span>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Limpiar campos</h3>
                        <p class="text-sm text-gray-600 mt-2">¿Estás seguro de limpiar todos los campos? Se perderán los datos ingresados.</p>
                    </div>
                    <x-slot:footer>
                        <x-ui.button type="button" variant="danger" icon="delete_sweep" wire:click="executeClear">Sí, limpiar</x-ui.button>
                        <x-ui.button type="button" variant="secondary" @click="open = false" wire:click="cancelClear">Cancelar</x-ui.button>
                    </x-slot:footer>
                </x-ui.card>
            </div>
        </div>
    @endif

    {{-- Modal confirmar cancelar (Alpine) --}}
    <div x-data="{ showCancelModal: false }" x-on:confirm-cancel.window="showCancelModal = true"
        x-show="showCancelModal" x-cloak
        class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
        style="display: none;">
        <div class="relative mx-auto p-5 w-full max-w-md">
            <x-ui.card overflow="visible">
                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-gray-100 mb-4">
                        <span class="material-symbols-outlined text-gray-600 text-2xl">arrow_back</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">¿Salir del formulario?</h3>
                    <p class="text-sm text-gray-600 mt-2">Los cambios no guardados se perderán.</p>
                </div>
                <x-slot:footer>
                    <x-ui.button type="button" variant="secondary" icon="logout" wire:click="executeCancel">Salir</x-ui.button>
                    <x-ui.button type="button" variant="secondary" @click="showCancelModal = false">Seguir editando</x-ui.button>
                </x-slot:footer>
            </x-ui.card>
        </div>
    </div>
</div>