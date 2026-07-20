<div class="max-w-4xl mx-auto">
    {{-- Progress Steps --}}
    <div class="mb-8">
        <div class="flex items-center justify-between">
            @php
                $steps = [
                    1 => ['label' => 'Cliente', 'icon' => 'person', 'desc' => 'Datos e instalación'],
                    2 => ['label' => 'Plan', 'icon' => 'assignment', 'desc' => 'Selección y precio'],
                    3 => ['label' => 'Docs', 'icon' => 'description', 'desc' => 'Documentos'],
                    4 => ['label' => 'Firma', 'icon' => 'edit_note', 'desc' => 'Firma digital'],
                    5 => ['label' => 'PDF', 'icon' => 'picture_as_pdf', 'desc' => 'Vista previa'],
                ];
            @endphp
            @foreach($steps as $num => $s)
                <div class="flex flex-col items-center relative flex-1">
                    {{-- Connector line --}}
                    @if($num > 1)
                        <div class="absolute top-5 right-1/2 w-full h-0.5 -z-10
                            {{ $num <= $step ? 'bg-indigo-600' : 'bg-gray-200' }}"></div>
                    @endif

                    {{-- Circle --}}
                    <button wire:click="goToStep({{ $num }})"
                        class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-200
                        {{ $num === $step ? 'bg-indigo-600 text-white ring-4 ring-indigo-100' : ($num < $step ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-400') }}
                        hover:ring-2 hover:ring-indigo-300">
                        @if($num < $step)
                            <span class="material-symbols-outlined text-base">check</span>
                        @else
                            <span class="material-symbols-outlined text-base">{{ $s['icon'] }}</span>
                        @endif
                    </button>

                    {{-- Label --}}
                    <span class="text-xs font-medium mt-1.5
                        {{ $num === $step ? 'text-indigo-700' : ($num < $step ? 'text-indigo-600' : 'text-gray-400') }}">
                        {{ $s['label'] }}
                    </span>
                    <span class="text-[10px] text-gray-400 hidden sm:block">{{ $s['desc'] }}</span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Step Content --}}
    <x-ui.card>
        {{-- Step 1: Datos del Cliente --}}
        @if($step === 1)
            <div class="space-y-6">
                <div class="flex items-center gap-3 pb-4 border-b border-gray-100">
                    <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center">
                        <span class="material-symbols-outlined text-indigo-600">person</span>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Datos del Cliente</h3>
                        <p class="text-sm text-gray-500">Verifica y completa la información del cliente</p>
                    </div>
                </div>

                {{-- Info del Ticket (si existe) --}}
                @if($ticket_description)
                <div class="bg-blue-50 rounded-lg border border-blue-200 p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="material-symbols-outlined text-blue-600 text-sm">confirmation_number</span>
                        <span class="text-xs font-semibold text-blue-800 uppercase tracking-wide">Información del Ticket</span>
                        @if($ticket_priority)
                            <x-ui.badge :variant="match($ticket_priority) { 'P1' => 'danger', 'P2' => 'warning', 'P3' => 'info', default => 'neutral' }">
                                {{ $ticket_priority }}
                            </x-ui.badge>
                        @endif
                    </div>
                    <p class="text-sm text-blue-900 whitespace-pre-line">{{ $ticket_description }}</p>
                    @if($ticket_origin)
                        <p class="text-xs text-blue-600 mt-1">Origen: {{ $ticket_origin }}</p>
                    @endif
                </div>
                @endif

                {{-- Info cliente (readonly) --}}
                <div class="bg-gray-50 rounded-lg border border-gray-200 p-4 space-y-2">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <p class="text-xs text-gray-500">Nombre</p>
                            <p class="font-medium text-gray-800">{{ $client_name }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Documento</p>
                            <p class="font-medium text-gray-800">{{ $client_document_type }} {{ $client_document_number }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Teléfono</p>
                            <p class="font-medium text-gray-800">{{ $client_phone ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Correo</p>
                            <p class="font-medium text-gray-800">{{ $client_email ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Sucursal</p>
                            <p class="font-medium text-gray-800">{{ $client_branch_name }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Dirección registrada</p>
                            <p class="font-medium text-gray-800">{{ $client_address ?? '—' }}</p>
                        </div>
                    </div>
                </div>

                {{-- Notas del Cliente (si existen) --}}
                @if($client_notes)
                <div class="bg-yellow-50 rounded-lg border border-yellow-200 p-4">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="material-symbols-outlined text-yellow-600 text-sm">notes</span>
                        <span class="text-xs font-semibold text-yellow-800 uppercase tracking-wide">Notas del Cliente</span>
                    </div>
                    <p class="text-sm text-yellow-900 whitespace-pre-line">{{ $client_notes }}</p>
                </div>
                @endif

                {{-- Campos editables --}}
                <div class="space-y-4">
                    <x-ui.textarea wire:model="installation_address" label="Dirección de instalación" required
                        icon="edit_note" rows="2" placeholder="Dirección donde se instalará el servicio" />

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-ui.input type="text" wire:model="latitude" icon="pin_drop" label="Latitud"
                            placeholder="13.6929" />
                        <x-ui.input type="text" wire:model="longitude" icon="pin_drop" label="Longitud"
                            placeholder="-89.2182" />
                    </div>
                    <p class="text-xs text-gray-400 flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">info</span>
                        Las coordenadas se pueden capturar enviando un enlace al cliente.
                    </p>
                </div>

                <div class="flex justify-end pt-4 border-t border-gray-100">
                    <x-ui.button variant="primary" icon="arrow_forward" wire:click="nextStep">
                        Continuar
                    </x-ui.button>
                </div>
            </div>
        @endif

        {{-- Step 2: Plan y Precio --}}
        @if($step === 2)
            <div class="space-y-6">
                <div class="flex items-center gap-3 pb-4 border-b border-gray-100">
                    <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center">
                        <span class="material-symbols-outlined text-indigo-600">assignment</span>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Plan y Precio</h3>
                        <p class="text-sm text-gray-500">Seleccioná el plan y verificá el precio para la zona</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Tipo de servicio</label>
                        <div class="bg-gray-50 rounded-lg border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-800 capitalize">
                            {{ str_replace('_', ' ', $service_type) }}
                        </div>
                    </div>

                    <x-ui.select wire:model.live="zone_id" label="Zona" icon="map">
                        <option value="">Sin zona</option>
                        @foreach($availableZones as $z)
                            <option value="{{ $z['id'] }}">{{ $z['name'] }}</option>
                        @endforeach
                    </x-ui.select>
                </div>

                {{-- Catálogo de planes --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-3">Seleccionar Plan</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($availablePlans as $plan)
                            @php
                                $zoneModel = $zone_id ? \App\Models\Zone::find($zone_id) : null;
                                $planModel = \App\Models\Plan::find($plan['id']);
                                $effPrice = $zoneModel && $planModel ? $zoneModel->getEffectivePriceForPlan($planModel) : (float) $plan['base_price'];
                                $isSelected = $plan_id == $plan['id'];
                            @endphp
                            <button type="button" wire:click="$set('plan_id', {{ $plan['id'] }})"
                                class="relative text-left p-4 rounded-xl border-2 transition-all duration-200
                                {{ $isSelected ? 'border-indigo-500 bg-indigo-50 shadow-md' : 'border-gray-200 hover:border-gray-300 bg-white hover:shadow-sm' }}">
                                @if($isSelected)
                                    <div class="absolute top-2 right-2 w-6 h-6 rounded-full bg-indigo-600 flex items-center justify-center">
                                        <span class="material-symbols-outlined text-white text-sm">check</span>
                                    </div>
                                @endif
                                <p class="font-bold text-gray-900">{{ $plan['name'] }}</p>
                                <div class="mt-1 space-y-0.5">
                                    <p class="text-xs text-gray-500 capitalize">{{ str_replace('_', ' ', $plan['service_type']) }}</p>
                                    @if($plan['speed'])
                                        <p class="text-xs text-gray-500">Velocidad: {{ $plan['speed'] }}</p>
                                    @endif
                                    @if($plan['channels'])
                                        <p class="text-xs text-gray-500">Canales: {{ $plan['channels'] }}</p>
                                    @endif
                                </div>
                                <div class="mt-3 flex items-baseline gap-1">
                                    <span class="text-lg font-bold text-indigo-700">${{ number_format($effPrice, 2) }}</span>
                                    <span class="text-xs text-gray-400">/mes</span>
                                </div>
                                @if($effPrice != $plan['base_price'])
                                    <p class="text-[10px] text-amber-600 mt-1">Precio especial para esta zona</p>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Precio personalizado --}}
                @if($plan_id)
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                    <p class="text-sm font-medium text-amber-800 flex items-center gap-2">
                        <span class="material-symbols-outlined text-base">info</span>
                        Precio del plan
                    </p>
                    <div class="mt-2">
                        @php $detail = $this->planPriceDetail; @endphp
                        @if($detail)
                            <p class="text-xs text-amber-700">
                                Precio base: <strong>${{ number_format($detail['base_price'], 2) }}</strong>
                                @if($detail['has_override'])
                                    → Precio efectivo: <strong>${{ number_format($detail['effective_price'], 2) }}</strong>
                                @endif
                            </p>
                        @endif
                    </div>
                    <div class="mt-2">
                        <x-ui.input type="number" wire:model="price" icon="attach_money" label="Precio a facturar"
                            step="0.01" min="0" placeholder="0.00" />
                    </div>
                </div>
                @endif

                <div class="flex justify-between pt-4 border-t border-gray-100">
                    <x-ui.button variant="secondary" icon="arrow_back" wire:click="previousStep">
                        Atrás
                    </x-ui.button>
                    <x-ui.button variant="primary" icon="arrow_forward" wire:click="nextStep">
                        Continuar
                    </x-ui.button>
                </div>
            </div>
        @endif

        {{-- Step 3: Documentos --}}
        @if($step === 3)
            <div class="space-y-6">
                <div class="flex items-center gap-3 pb-4 border-b border-gray-100">
                    <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center">
                        <span class="material-symbols-outlined text-indigo-600">description</span>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Documentación</h3>
                        <p class="text-sm text-gray-500">Subí los documentos requeridos para el contrato</p>
                    </div>
                </div>

                {{-- Progress --}}
                @php $docProgress = $this->documentsProgress; @endphp
                <div class="bg-gray-50 rounded-lg p-3">
                    <div class="flex items-center justify-between text-sm">
                        <span class="font-medium text-gray-700">Progreso de documentos</span>
                        <span class="text-xs text-gray-500">{{ $docProgress['total'] }} subidos</span>
                    </div>
                    <div class="mt-2 h-2 bg-gray-200 rounded-full overflow-hidden">
                        @php
                            $pct = $docProgress['total_required'] > 0
                                ? ($docProgress['completed_required'] / $docProgress['total_required']) * 100
                                : 0;
                        @endphp
                        <div class="h-full bg-indigo-600 rounded-full transition-all duration-500"
                            style="width: {{ $pct }}%"></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">
                        {{ $docProgress['completed_required'] }}/{{ $docProgress['total_required'] }} obligatorios
                        @if($docProgress['completed_optional'] > 0)
                            · {{ $docProgress['completed_optional'] }} opcionales
                        @endif
                    </p>
                </div>

                {{-- Document uploaders --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- DUI Frente (obligatorio) --}}
                    <div class="border-2 border-dashed rounded-xl p-4 text-center
                        {{ isset($uploadedDocuments['dui_front']) ? 'border-green-300 bg-green-50' : 'border-gray-300 hover:border-indigo-300' }}">
                        <span class="material-symbols-outlined text-3xl
                            {{ isset($uploadedDocuments['dui_front']) ? 'text-green-500' : 'text-gray-300' }}">badge</span>
                        <p class="text-sm font-medium text-gray-700 mt-1">DUI (Frente) *</p>
                        @if(isset($uploadedDocuments['dui_front']))
                            <p class="text-xs text-green-600 mt-1">✓ Subido</p>
                            <button wire:click="removeDocument('dui_front')"
                                class="text-xs text-red-600 hover:text-red-700 mt-1">Eliminar</button>
                        @else
                            <input type="file" wire:model="dui_front" accept="image/*,.pdf"
                                class="mt-2 text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                            @error('dui_front') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        @endif
                    </div>

                    {{-- DUI Reverso (obligatorio) --}}
                    <div class="border-2 border-dashed rounded-xl p-4 text-center
                        {{ isset($uploadedDocuments['dui_back']) ? 'border-green-300 bg-green-50' : 'border-gray-300 hover:border-indigo-300' }}">
                        <span class="material-symbols-outlined text-3xl
                            {{ isset($uploadedDocuments['dui_back']) ? 'text-green-500' : 'text-gray-300' }}">badge</span>
                        <p class="text-sm font-medium text-gray-700 mt-1">DUI (Reverso) *</p>
                        @if(isset($uploadedDocuments['dui_back']))
                            <p class="text-xs text-green-600 mt-1">✓ Subido</p>
                            <button wire:click="removeDocument('dui_back')"
                                class="text-xs text-red-600 hover:text-red-700 mt-1">Eliminar</button>
                        @else
                            <input type="file" wire:model="dui_back" accept="image/*,.pdf"
                                class="mt-2 text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                            @error('dui_back') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        @endif
                    </div>

                    {{-- Selfie --}}
                    <div class="border-2 border-dashed rounded-xl p-4 text-center
                        {{ isset($uploadedDocuments['selfie']) ? 'border-green-300 bg-green-50' : 'border-gray-300 hover:border-gray-400' }}">
                        <span class="material-symbols-outlined text-3xl
                            {{ isset($uploadedDocuments['selfie']) ? 'text-green-500' : 'text-gray-300' }}">photo_camera</span>
                        <p class="text-sm font-medium text-gray-700 mt-1">Selfie con documento</p>
                        @if(isset($uploadedDocuments['selfie']))
                            <p class="text-xs text-green-600 mt-1">✓ Subido</p>
                            <button wire:click="removeDocument('selfie')"
                                class="text-xs text-red-600 hover:text-red-700 mt-1">Eliminar</button>
                        @else
                            <input type="file" wire:model="selfie" accept="image/*"
                                class="mt-2 text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200" />
                        @endif
                    </div>

                    {{-- Recibo de luz --}}
                    <div class="border-2 border-dashed rounded-xl p-4 text-center
                        {{ isset($uploadedDocuments['receipt']) ? 'border-green-300 bg-green-50' : 'border-gray-300 hover:border-gray-400' }}">
                        <span class="material-symbols-outlined text-3xl
                            {{ isset($uploadedDocuments['receipt']) ? 'text-green-500' : 'text-gray-300' }}">receipt</span>
                        <p class="text-sm font-medium text-gray-700 mt-1">Recibo de luz</p>
                        @if(isset($uploadedDocuments['receipt']))
                            <p class="text-xs text-green-600 mt-1">✓ Subido</p>
                            <button wire:click="removeDocument('receipt')"
                                class="text-xs text-red-600 hover:text-red-700 mt-1">Eliminar</button>
                        @else
                            <input type="file" wire:model="receipt" accept="image/*,.pdf"
                                class="mt-2 text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200" />
                        @endif
                    </div>
                </div>

                <div class="flex justify-between pt-4 border-t border-gray-100">
                    <x-ui.button variant="secondary" icon="arrow_back" wire:click="previousStep">
                        Atrás
                    </x-ui.button>
                    <x-ui.button variant="primary" icon="arrow_forward" wire:click="nextStep">
                        Continuar
                    </x-ui.button>
                </div>
            </div>
        @endif

        {{-- Step 4: Firma Digital --}}
        @if($step === 4)
            <div class="space-y-6">
                <div class="flex items-center gap-3 pb-4 border-b border-gray-100">
                    <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center">
                        <span class="material-symbols-outlined text-indigo-600">edit_note</span>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Firma Digital</h3>
                        <p class="text-sm text-gray-500">Capturá las firmas del cliente y del agente de ventas</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    {{-- Firma Cliente --}}
                    <div class="bg-gray-50 rounded-xl border border-gray-200 p-5">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="material-symbols-outlined text-indigo-600">person</span>
                            <p class="font-semibold text-gray-800">Firma del Cliente</p>
                            @if($showClientSignature)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                    ✓ Firmado
                                </span>
                            @endif
                        </div>

                        @if($showClientSignature && $client_signature_data)
                            <div class="bg-white rounded-lg border border-gray-200 p-3 text-center">
                                <img src="{{ $client_signature_data }}" alt="Firma del Cliente"
                                    class="max-h-20 mx-auto" />
                                <button wire:click="$set('client_signature_data', null); $set('showClientSignature', false)"
                                    class="text-xs text-red-600 hover:text-red-700 mt-2">Volver a firmar</button>
                            </div>
                        @else
                            {{-- Canvas para firma --}}
                            <div x-data="{ canvas: null, ctx: null, drawing: false, sigData: null }"
                                x-init="canvas = $refs.canvas; ctx = canvas.getContext('2d');
                                    canvas.width = canvas.offsetWidth;
                                    canvas.height = 120;
                                    ctx.strokeStyle = '#1e40af';
                                    ctx.lineWidth = 2;
                                    ctx.lineCap = 'round';

                                    const startDraw = (e) => {
                                        drawing = true;
                                        const rect = canvas.getBoundingClientRect();
                                        const x = (e.touches ? e.touches[0].clientX : e.clientX) - rect.left;
                                        const y = (e.touches ? e.touches[0].clientY : e.clientY) - rect.top;
                                        ctx.beginPath();
                                        ctx.moveTo(x, y);
                                    };
                                    const draw = (e) => {
                                        if (!drawing) return;
                                        e.preventDefault();
                                        const rect = canvas.getBoundingClientRect();
                                        const x = (e.touches ? e.touches[0].clientX : e.clientX) - rect.left;
                                        const y = (e.touches ? e.touches[0].clientY : e.clientY) - rect.top;
                                        ctx.lineTo(x, y);
                                        ctx.stroke();
                                    };
                                    const endDraw = () => { drawing = false; };

                                    canvas.addEventListener('mousedown', startDraw);
                                    canvas.addEventListener('mousemove', draw);
                                    canvas.addEventListener('mouseup', endDraw);
                                    canvas.addEventListener('mouseleave', endDraw);
                                    canvas.addEventListener('touchstart', startDraw, { passive: true });
                                    canvas.addEventListener('touchmove', draw, { passive: false });
                                    canvas.addEventListener('touchend', endDraw);

                                    window.addEventListener('saveClientSignature', () => {
                                        sigData = canvas.toDataURL('image/png');
                                        $wire.call('saveClientSignature', sigData);
                                    });

                                    window.addEventListener('clearClientSignature', () => {
                                        ctx.clearRect(0, 0, canvas.width, canvas.height);
                                    });
                                "
                                class="space-y-2">
                                <canvas x-ref="canvas"
                                    class="w-full h-[120px] bg-white border-2 border-dashed border-gray-300 rounded-lg cursor-crosshair"></canvas>
                                <div class="flex gap-2">
                                    <button type="button" @click="ctx.clearRect(0, 0, canvas.width, canvas.height)"
                                        class="text-xs px-3 py-1.5 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200">
                                        Limpiar
                                    </button>
                                    <button type="button"
                                        @click="(function(){
                                            sigData = canvas.toDataURL('image/png');
                                            $wire.call('saveClientSignature', sigData);
                                        })()"
                                        class="text-xs px-3 py-1.5 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">
                                        Guardar firma
                                    </button>
                                </div>
                            </div>

                            {{-- O enviar enlace --}}
                            <div class="mt-3 pt-3 border-t border-gray-200">
                                <button wire:click="generateSignatureLink"
                                    class="text-xs text-indigo-600 hover:text-indigo-700 font-medium flex items-center gap-1">
                                    <span class="material-symbols-outlined text-sm">link</span>
                                    Enviar enlace al cliente para que firme
                                </button>
                                @if($signature_link)
                                    <div class="mt-2 bg-indigo-50 rounded-lg p-2">
                                        <p class="text-xs text-indigo-700 mb-1">Enlace de firma:</p>
                                        <input type="text" value="{{ $signature_link }}" readonly
                                            class="w-full text-xs px-2 py-1 border border-indigo-200 rounded bg-white"
                                            onclick="this.select(); navigator.clipboard?.writeText(this.value);" />
                                        <p class="text-[10px] text-indigo-500 mt-1">Compartí este enlace con el cliente</p>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>

                    {{-- Firma Agente de Ventas --}}
                    <div class="bg-gray-50 rounded-xl border border-gray-200 p-5">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="material-symbols-outlined text-indigo-600">badge</span>
                            <p class="font-semibold text-gray-800">Tu Firma</p>
                            @if($showSalesRepSignature)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                    ✓ Firmado
                                </span>
                            @endif
                        </div>

                        @if($showSalesRepSignature && $sales_rep_signature_data)
                            <div class="bg-white rounded-lg border border-gray-200 p-3 text-center">
                                <img src="{{ $sales_rep_signature_data }}" alt="Firma del Agente"
                                    class="max-h-20 mx-auto" />
                                <button wire:click="$set('sales_rep_signature_data', null); $set('showSalesRepSignature', false)"
                                    class="text-xs text-red-600 hover:text-red-700 mt-2">Volver a firmar</button>
                            </div>
                        @else
                            {{-- Canvas para firma del agente --}}
                            <div x-data="{ canvas2: null, ctx2: null, drawing2: false, sigData2: null }"
                                x-init="canvas2 = $refs.canvas2; ctx2 = canvas2.getContext('2d');
                                    canvas2.width = canvas2.offsetWidth;
                                    canvas2.height = 120;
                                    ctx2.strokeStyle = '#1e40af';
                                    ctx2.lineWidth = 2;
                                    ctx2.lineCap = 'round';

                                    const startDraw2 = (e) => {
                                        drawing2 = true;
                                        const rect = canvas2.getBoundingClientRect();
                                        const x = (e.touches ? e.touches[0].clientX : e.clientX) - rect.left;
                                        const y = (e.touches ? e.touches[0].clientY : e.clientY) - rect.top;
                                        ctx2.beginPath();
                                        ctx2.moveTo(x, y);
                                    };
                                    const draw2 = (e) => {
                                        if (!drawing2) return;
                                        e.preventDefault();
                                        const rect = canvas2.getBoundingClientRect();
                                        const x = (e.touches ? e.touches[0].clientX : e.clientX) - rect.left;
                                        const y = (e.touches ? e.touches[0].clientY : e.clientY) - rect.top;
                                        ctx2.lineTo(x, y);
                                        ctx2.stroke();
                                    };
                                    const endDraw2 = () => { drawing2 = false; };

                                    canvas2.addEventListener('mousedown', startDraw2);
                                    canvas2.addEventListener('mousemove', draw2);
                                    canvas2.addEventListener('mouseup', endDraw2);
                                    canvas2.addEventListener('mouseleave', endDraw2);
                                    canvas2.addEventListener('touchstart', startDraw2, { passive: true });
                                    canvas2.addEventListener('touchmove', draw2, { passive: false });
                                    canvas2.addEventListener('touchend', endDraw2);
                                "
                                class="space-y-2">
                                <canvas x-ref="canvas2"
                                    class="w-full h-[120px] bg-white border-2 border-dashed border-gray-300 rounded-lg cursor-crosshair"></canvas>
                                <div class="flex gap-2">
                                    <button type="button" @click="ctx2.clearRect(0, 0, canvas2.width, canvas2.height)"
                                        class="text-xs px-3 py-1.5 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200">
                                        Limpiar
                                    </button>
                                    <button type="button"
                                        @click="(function(){
                                            sigData2 = canvas2.toDataURL('image/png');
                                            $wire.call('saveSalesRepSignature', sigData2);
                                        })()"
                                        class="text-xs px-3 py-1.5 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">
                                        Guardar firma
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Términos --}}
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                    <p class="text-sm font-semibold text-amber-800 flex items-center gap-2 mb-2">
                        <span class="material-symbols-outlined text-base">gavel</span>
                        Términos y Condiciones
                    </p>
                    <div class="text-xs text-amber-700 max-h-32 overflow-y-auto prose prose-sm">
                        {!! $contract_terms !!}
                    </div>
                    <p class="text-xs text-amber-600 mt-2">
                        Al firmar, tanto el cliente como el agente de ventas aceptan los términos y condiciones del contrato.
                    </p>
                </div>

                <div class="flex justify-between pt-4 border-t border-gray-100">
                    <x-ui.button variant="secondary" icon="arrow_back" wire:click="previousStep">
                        Atrás
                    </x-ui.button>
                    <x-ui.button variant="success" icon="save" wire:click="createContract" wire:loading.attr="disabled">
                        <span wire:loading.remove>Generar Contrato y PDF</span>
                        <span wire:loading>Generando...</span>
                    </x-ui.button>
                </div>
            </div>
        @endif

        {{-- Step 5: Vista Previa PDF - Finalización --}}
        @if($step === 5)
            <div class="space-y-6">
                <div class="flex items-center gap-3 pb-4 border-b border-gray-100">
                    <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                        <span class="material-symbols-outlined text-green-600">check_circle</span>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">¡Contrato Creado!</h3>
                        <p class="text-sm text-gray-500">El contrato se ha generado correctamente</p>
                    </div>
                </div>

                {{-- Resumen --}}
                <div class="bg-green-50 border border-green-200 rounded-xl p-6 text-center">
                    <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-4">
                        <span class="material-symbols-outlined text-3xl text-green-600">description</span>
                    </div>
                    <h4 class="text-xl font-bold text-green-800">{{ $contractDigitalCode }}</h4>
                    <p class="text-sm text-green-700 mt-1">Contrato generado exitosamente</p>
                </div>

                {{-- Detalles --}}
                <div class="bg-gray-50 rounded-lg border border-gray-200 p-4">
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <p class="text-xs text-gray-500">Cliente</p>
                            <p class="font-medium text-gray-800">{{ $client_name }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Plan</p>
                            <p class="font-medium text-gray-800">{{ $availablePlans[array_search($plan_id, array_column($availablePlans, 'id'))]['name'] ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Precio mensual</p>
                            <p class="font-medium text-gray-800">${{ number_format($price, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Dirección instalación</p>
                            <p class="font-medium text-gray-800">{{ $installation_address }}</p>
                        </div>
                    </div>
                </div>

                {{-- Acciones --}}
                <div class="flex flex-col sm:flex-row gap-3 justify-center pt-4 border-t border-gray-100">
                    <x-ui.button variant="primary" icon="download" wire:click="downloadPdf">
                        Descargar PDF
                    </x-ui.button>
                    <x-ui.button variant="success" icon="check" wire:click="finalize">
                        Finalizar
                    </x-ui.button>
                </div>
            </div>
        @endif
    </x-ui.card>
</div>
