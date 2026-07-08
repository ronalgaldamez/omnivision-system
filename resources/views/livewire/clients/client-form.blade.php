<div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
    <div class="space-y-6">
        {{-- Tipo de servicio rápido --}}
        <div class="pb-5 border-b border-gray-100">
            <label
                class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2 flex items-center gap-2">
                <span class="material-symbols-outlined text-gray-500 text-sm">handyman</span>
                Tipo de servicio
            </label>
            <select wire:model.live="service_type_id"
                class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-gray-50 text-gray-900 text-sm focus:border-gray-400 focus:bg-white focus:outline-none transition">
                <option value="">Seleccionar</option>
                @foreach(\App\Models\ServiceType::orderBy('name')->get() as $st)
                    <option value="{{ $st->id }}">{{ str_replace('_', ' ', $st->name) }}</option>
                @endforeach
            </select>
        </div>

        {{-- Nombre --}}
        <div>
            <label
                class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2 flex items-center gap-2">
                <span class="material-symbols-outlined text-gray-500 text-sm">badge</span>
                Nombre <span class="text-red-500">*</span>
            </label>
            <div class="relative" x-data>
                <input type="text" wire:model="name"
                    x-on:input="if ($event.isTrusted) { $el.value = $el.value.replace(/[^a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s]/g, ''); $el.dispatchEvent(new Event('input', { bubbles: true })); }"
                    class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-200 bg-gray-50 text-gray-900 text-sm focus:border-gray-400 focus:bg-white focus:outline-none transition"
                    placeholder="Nombre completo del cliente">
                <span
                    class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">edit_note</span>
            </div>
            @error('name') <span class="text-xs text-red-600 mt-1.5 block font-medium">{{ $message }}</span> @enderror
        </div>

        {{-- Tipo y número de documento --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label
                    class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2 flex items-center gap-2">
                    <span class="material-symbols-outlined text-gray-500 text-sm">fingerprint</span>
                    Tipo de documento
                </label>
                <select wire:model="document_type"
                    class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-gray-50 text-gray-900 text-sm focus:border-gray-400 focus:bg-white focus:outline-none transition">
                    <option value="">Seleccionar tipo</option>
                    @foreach($documentTypesList as $dt)
                        <option value="{{ $dt }}">{{ $dt }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label
                    class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2 flex items-center gap-2">
                    <span class="material-symbols-outlined text-gray-500 text-sm">pin</span>
                    Número de documento
                </label>
                <div class="relative" x-data>
                    <input type="text" wire:model="document_number" x-on:input="
                        if ($event.isTrusted) {
                            let val = $el.value.replace(/[^0-9]/g, '').slice(0,9);
                            if (val.length > 8) val = val.slice(0,8) + '-' + val.slice(8);
                            $el.value = val;
                            $el.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                    " class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-200 bg-gray-50 text-gray-900 text-sm focus:border-gray-400 focus:bg-white focus:outline-none transition"
                        placeholder="00000000-0" maxlength="10">
                    <span
                        class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">tag</span>
                </div>
                @error('document_number') <span
                class="text-xs text-red-600 mt-1.5 block font-medium">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Teléfonos --}}
        <div class="pb-5 border-b border-gray-100">
            <label
                class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-3 flex items-center gap-2">
                <span class="material-symbols-outlined text-gray-500 text-sm">call</span>
                Teléfonos
            </label>
            <div class="space-y-3">
                <div class="flex items-start gap-2">
                    <div class="flex-1 relative" x-data>
                        <input type="text" wire:model="phone" x-on:input="
                                if ($event.isTrusted) {
                                    let val = $el.value.replace(/[^0-9]/g, '').slice(0,8);
                                    if (val.length > 4) val = val.slice(0,4) + '-' + val.slice(4);
                                    $el.value = val;
                                    $el.dispatchEvent(new Event('input', { bubbles: true }));
                                }
                            "
                            class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-200 bg-gray-50 text-gray-900 text-sm focus:border-gray-400 focus:bg-white focus:outline-none transition"
                            placeholder="0000-0000" maxlength="9">
                        <span
                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">phone</span>
                    </div>
                </div>
                @foreach($phones as $index => $phone)
                    <div class="flex items-start gap-2" x-data>
                        <div class="flex-1 relative">
                            <input type="text" wire:model="phones.{{ $index }}.number" x-on:input="
                                            if ($event.isTrusted) {
                                                let val = $el.value.replace(/[^0-9]/g, '').slice(0,8);
                                                if (val.length > 4) val = val.slice(0,4) + '-' + val.slice(4);
                                                $el.value = val;
                                                $el.dispatchEvent(new Event('input', { bubbles: true }));
                                            }
                                        "
                                class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-200 bg-gray-50 text-gray-900 text-sm focus:border-gray-400 focus:bg-white focus:outline-none transition"
                                placeholder="0000-0000" maxlength="9">
                            <span
                                class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">phone</span>
                            @error('phones.' . $index . '.number') <span
                            class="text-xs text-red-600 mt-1.5 block font-medium">{{ $message }}</span> @enderror
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
        <div>
            <label
                class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2 flex items-center gap-2">
                <span class="material-symbols-outlined text-gray-500 text-sm">mail</span>
                Correo electrónico
            </label>
            <div class="relative">
                <input type="email" wire:model="email"
                    class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-200 bg-gray-50 text-gray-900 text-sm focus:border-gray-400 focus:bg-white focus:outline-none transition"
                    placeholder="correo@ejemplo.com">
                <span
                    class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">alternate_email</span>
            </div>
            @error('email') <span class="text-xs text-red-600 mt-1.5 block font-medium">{{ $message }}</span> @enderror
        </div>
        <div class="flex items-center gap-2">
            <input type="checkbox" wire:model.live="accepts_promotions" id="accepts_promotions"
                class="w-4 h-4 rounded border-gray-300 text-gray-700 focus:ring-gray-500">
            <label for="accepts_promotions" class="text-sm text-gray-700 cursor-pointer">Acepta recibir promociones o
                facturas electrónicas</label>
        </div>

        {{-- Departamento, Municipio, Distrito (cascada) --}}
        <div class="pb-5 border-b border-gray-100">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label
                        class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">Departamento</label>
                    <select wire:model.live="departamento_id"
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-gray-50 text-gray-900 text-sm focus:border-gray-400 focus:bg-white focus:outline-none transition">
                        <option value="">Seleccionar departamento</option>
                        @foreach($availableDepartamentos as $dep)
                            <option value="{{ $dep['id'] }}">{{ $dep['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label
                        class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">Municipio</label>
                    <select wire:model.live="municipio_id" {{ empty($availableMunicipios) ? 'disabled' : '' }}
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-gray-50 text-gray-900 text-sm focus:border-gray-400 focus:bg-white focus:outline-none transition {{ empty($availableMunicipios) ? 'opacity-50 cursor-not-allowed' : '' }}">
                        <option value="">Seleccionar municipio</option>
                        @foreach($availableMunicipios as $mun)
                            <option value="{{ $mun['id'] }}">{{ $mun['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">Distrito /
                        Localidad</label>
                    <select wire:model.live="distrito_id" {{ empty($availableDistritos) ? 'disabled' : '' }}
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-gray-50 text-gray-900 text-sm focus:border-gray-400 focus:bg-white focus:outline-none transition {{ empty($availableDistritos) ? 'opacity-50 cursor-not-allowed' : '' }}">
                        <option value="">Seleccionar distrito</option>
                        @foreach($availableDistritos as $dis)
                            <option value="{{ $dis['id'] }}">{{ $dis['name'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Dirección --}}
        <div>
            <label
                class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2 flex items-center gap-2">
                <span class="material-symbols-outlined text-gray-500 text-sm">location_on</span>
                Dirección
            </label>
            <div class="relative">
                <textarea wire:model="address" rows="2"
                    class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-200 bg-gray-50 text-gray-900 text-sm focus:border-gray-400 focus:bg-white focus:outline-none transition resize-none"
                    placeholder="Dirección del cliente"></textarea>
                <span class="material-symbols-outlined absolute left-3 top-3 text-gray-400 text-lg">edit_note</span>
            </div>
        </div>

        {{-- Dirección de instalación --}}
        <div>
            <label
                class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2 flex items-center gap-2">
                <span class="material-symbols-outlined text-gray-500 text-sm">home_pin</span>
                Dirección de instalación
            </label>
            <div class="relative">
                <textarea wire:model="installation_address" rows="2"
                    class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-200 bg-gray-50 text-gray-900 text-sm focus:border-gray-400 focus:bg-white focus:outline-none transition resize-none"
                    placeholder="Dirección donde se instalará el servicio"></textarea>
                <span class="material-symbols-outlined absolute left-3 top-3 text-gray-400 text-lg">edit_note</span>
            </div>
        </div>

        {{-- Coordenadas (solo visibles si el usuario tiene el permiso) --}}
        @can('capture coordinates')
            <div class="pb-5 border-b border-gray-100">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label
                            class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2 flex items-center gap-2">
                            <span class="material-symbols-outlined text-gray-500 text-sm">explore</span>
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
                                x-on:input="if ($event.isTrusted) { $el.value = formatCoordinate($el.value); $el.dispatchEvent(new Event('input', { bubbles: true })); }"
                                class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-200 bg-gray-50 text-gray-900 text-sm focus:border-gray-400 focus:bg-white focus:outline-none transition"
                                placeholder="00.0000" maxlength="7" inputmode="decimal">
                            <span
                                class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">pin_drop</span>
                        </div>
                        @error('latitude') <span class="text-xs text-red-600 mt-1.5 block font-medium">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label
                            class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2 flex items-center gap-2">
                            <span class="material-symbols-outlined text-gray-500 text-sm">explore</span>
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
                                x-on:input="if ($event.isTrusted) { $el.value = formatCoordinate($el.value); $el.dispatchEvent(new Event('input', { bubbles: true })); }"
                                class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-200 bg-gray-50 text-gray-900 text-sm focus:border-gray-400 focus:bg-white focus:outline-none transition"
                                placeholder="-00.0000" maxlength="8" inputmode="decimal">
                            <span
                                class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">pin_drop</span>
                        </div>
                        @error('longitude') <span
                        class="text-xs text-red-600 mt-1.5 block font-medium">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
        @endcan

        {{-- NC --}}
        <div>
            <label
                class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2 flex items-center gap-2">
                <span class="material-symbols-outlined text-gray-500 text-sm">bolt</span>
                NC
            </label>
            <div class="relative" x-data>
                <input type="text" wire:model="nro_luz"
                    x-on:input="if ($event.isTrusted) { $el.value = $el.value.replace(/[^0-9]/g, '').slice(0,12); $el.dispatchEvent(new Event('input', { bubbles: true })); }"
                    class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-200 bg-gray-50 text-gray-900 text-sm focus:border-gray-400 focus:bg-white focus:outline-none transition"
                    placeholder="000000000000" maxlength="12">
                <span
                    class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">electric_meter</span>
            </div>
        </div>

        <div class="flex items-center gap-2 py-2">
            <input type="checkbox" wire:model.live="no_price" id="no_price"
                class="w-4 h-4 rounded border-gray-300 text-gray-700 focus:ring-gray-500">
            <label for="no_price" class="text-sm text-gray-700 cursor-pointer font-medium">Sin precio</label>
        </div>
        @if($no_price)
            <p class="text-xs text-gray-500 -mt-3 mb-4 italic">Nota: más adelante se le puede asignar el paquete al cliente.
            </p>
        @endif

        @if(!$no_price)
            <div class="bg-gray-50 rounded-lg border border-gray-200 p-4 space-y-4">
                <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wide flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm">assignment</span>
                    Servicio contratado
                    <span class="text-xs font-normal text-gray-500 normal-case tracking-normal">(opcional)</span>
                </h3>

                <div>
                    <label
                        class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2 flex items-center gap-2">
                        <span class="material-symbols-outlined text-gray-500 text-sm">business</span>
                        Sucursal
                    </label>
                    <select wire:model.live="branch_id"
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-white text-gray-900 text-sm focus:border-gray-400 focus:outline-none transition">
                        <option value="">Seleccionar sucursal</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">Zona</label>
                    <div class="space-y-2">
                        <select wire:model.live="svc_departamento"
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-white text-gray-900 text-sm focus:border-gray-400 focus:outline-none transition">
                            <option value="">Departamento</option>
                            @foreach($svcAvailableDepartamentos as $d)
                                <option value="{{ $d['id'] }}">{{ $d['name'] }}</option>
                            @endforeach
                        </select>
                        @if($svcAvailableMunicipios)
                            <select wire:model.live="svc_municipio"
                                class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-white text-gray-900 text-sm focus:border-gray-400 focus:outline-none transition">
                                <option value="">Municipio</option>
                                @foreach($svcAvailableMunicipios as $m)
                                    <option value="{{ $m['id'] }}">{{ $m['name'] }}</option>
                                @endforeach
                            </select>
                        @endif
                        @if($svcAvailableDistritos)
                            <select wire:model.live="svc_distrito"
                                class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-white text-gray-900 text-sm focus:border-gray-400 focus:outline-none transition">
                                <option value="">Distrito / Localidad</option>
                                @foreach($svcAvailableDistritos as $d)
                                    <option value="{{ $d['id'] }}">{{ $d['name'] }}</option>
                                @endforeach
                            </select>
                        @endif
                        @if($svcAvailableSubzonas)
                            <select wire:model.live="svc_subzona"
                                class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-white text-gray-900 text-sm focus:border-gray-400 focus:outline-none transition">
                                <option value="">Cantón / Barrio / Colonia</option>
                                @foreach($svcAvailableSubzonas as $s)
                                    <option value="{{ $s['id'] }}">{{ $s['name'] }}</option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">Plan</label>
                    <select wire:model.live="plan_id"
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-white text-gray-900 text-sm focus:border-gray-400 focus:outline-none transition">
                        <option value="">Seleccionar</option>
                        @foreach($availablePlans as $p)
                            <option value="{{ $p['id'] }}">
                                {{ $p['name'] }} @if($p['speed'])({{ $p['speed'] }})@endif
                                — ${{ number_format($p['price'], 2) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @php $selPlan = collect($availablePlans)->firstWhere('id', $plan_id); @endphp
                @if($selPlan)
                    <div class="bg-white rounded-lg border border-gray-200 p-4">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-bold text-gray-900">{{ $selPlan['name'] }}</p>
                                <div class="flex flex-wrap gap-1.5 mt-2">
                                    @if(str_starts_with($selectedPlanOrigin, 'inherited:'))
                                        <span class="text-xs text-gray-700 bg-gray-100 px-2.5 py-1 rounded font-medium">
                                            Heredado de {{ substr($selectedPlanOrigin, 10) }}
                                        </span>
                                    @elseif($selectedPlanOrigin === 'base')
                                        <span class="text-xs text-gray-600 bg-gray-100 px-2.5 py-1 rounded font-medium">
                                            Precio base
                                        </span>
                                    @elseif($selectedPlanOrigin === 'override')
                                        <span class="text-xs text-gray-700 bg-gray-100 px-2.5 py-1 rounded font-medium">
                                            Precio de zona
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-bold text-gray-900">${{ number_format($selectedPlanPrice, 2) }}</p>
                                @if($selPlan['base_price'] != $selPlan['price'])
                                    <p class="text-xs text-gray-500 mt-0.5">Base: ${{ number_format($selPlan['base_price'], 2) }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <input type="hidden" wire:model="service">

        {{-- Notas --}}
        <div>
            <label
                class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2 flex items-center gap-2">
                <span class="material-symbols-outlined text-gray-500 text-sm">sticky_note_2</span>
                Notas
            </label>
            <div class="relative">
                <textarea wire:model="notes" rows="2"
                    class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-200 bg-gray-50 text-gray-900 text-sm focus:border-gray-400 focus:bg-white focus:outline-none transition resize-none"
                    placeholder="Observaciones internas..."></textarea>
                <span class="material-symbols-outlined absolute left-3 top-3 text-gray-400 text-lg">edit_note</span>
            </div>
        </div>

        {{-- Botones de acción --}}
        <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
            <button type="button" wire:click="$parent.closeClientModal()"
                class="px-6 py-2.5 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 bg-white hover:bg-gray-50 focus:outline-none transition">
                Cancelar
            </button>
            <button type="button" wire:click="save"
                class="px-6 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 focus:outline-none transition inline-flex items-center gap-2">
                <span class="material-symbols-outlined text-base">save</span>
                Guardar cliente
            </button>
        </div>
    </div>
</div>