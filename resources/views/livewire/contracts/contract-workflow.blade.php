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
            @foreach ($steps as $num => $s)
                <div class="flex flex-col items-center relative flex-1">
                    {{-- Connector line --}}
                    @if ($num > 1)
                        <div
                            class="absolute top-5 right-1/2 w-full h-0.5 -z-10
                            {{ $num <= $step ? 'bg-indigo-600' : 'bg-gray-200' }}">
                        </div>
                    @endif

                    {{-- Circle --}}
                    <button wire:click="goToStep({{ $num }})"
                        class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-200
                        {{ $num === $step ? 'bg-indigo-600 text-white ring-4 ring-indigo-100' : ($num < $step ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-400') }}
                        hover:ring-2 hover:ring-indigo-300">
                        @if ($num < $step)
                            <span class="material-symbols-outlined text-base">check</span>
                        @else
                            <span class="material-symbols-outlined text-base">{{ $s['icon'] }}</span>
                        @endif
                    </button>

                    {{-- Label --}}
                    <span
                        class="text-xs font-medium mt-1.5
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
        @if ($step === 1)
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
                @if ($ticket_description)
                    <div class="bg-blue-50 rounded-lg border border-blue-200 p-4">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="material-symbols-outlined text-blue-600 text-sm">confirmation_number</span>
                            <span class="text-xs font-semibold text-blue-800 uppercase tracking-wide">Información del
                                Ticket</span>
                            @if ($ticket_priority)
                                <x-ui.badge :variant="match ($ticket_priority) {
                                    'P1' => 'danger',
                                    'P2' => 'warning',
                                    'P3' => 'info',
                                    default => 'neutral',
                                }">
                                    {{ $ticket_priority }}
                                </x-ui.badge>
                            @endif
                        </div>
                        <p class="text-sm text-blue-900 whitespace-pre-line">{{ $ticket_description }}</p>
                        @if ($ticket_origin)
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
                            <p class="font-medium text-gray-800">{{ $client_document_type }}
                                {{ $client_document_number }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Teléfono</p>
                            <div class="flex items-center gap-2">
                                <p class="font-medium text-gray-800">{{ $client_phone ?? '—' }}</p>
                                @if ($client_phone)
                                    <button type="button" wire:click="sendGpsViaWhatsApp"
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-green-100 text-green-700 hover:bg-green-200 transition-colors"
                                        title="Enviar enlace de coordenadas por WhatsApp">
                                        <span class="material-symbols-outlined text-xs">chat</span>
                                        WhatsApp
                                    </button>
                                @endif
                            </div>
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
                @if ($client_notes)
                    <div class="bg-yellow-50 rounded-lg border border-yellow-200 p-4">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="material-symbols-outlined text-yellow-600 text-sm">notes</span>
                            <span class="text-xs font-semibold text-yellow-800 uppercase tracking-wide">Notas del
                                Cliente</span>
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

                    {{-- Tipo de servicio / tipo de cliente --}}
                    <x-ui.select wire:model.live="customer_type" label="Tipo de servicio" icon="business">
                        <option value="">Seleccionar tipo de servicio</option>
                        <option value="residencial">Residencial</option>
                        <option value="pyme">Pyme</option>
                        <option value="corporativo">Corporativo</option>
                    </x-ui.select>

                    {{-- Datos legales del contrato --}}
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="material-symbols-outlined text-amber-600 text-sm">gavel</span>
                            <span class="text-xs font-semibold text-amber-800 uppercase tracking-wide">Datos legales del
                                contrato</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <x-ui.input type="text" wire:model="client_nit" icon="badge" label="NIT"
                                maxlength="20" />
                            <x-ui.input type="text" wire:model="client_nrc" icon="badge" label="NRC"
                                maxlength="20" />
                            <x-ui.input type="date" wire:model="dui_expedition_date" icon="calendar_month"
                                label="Fecha de expedición DUI" />
                            <x-ui.input type="text" wire:model="dui_expedition_place" icon="location_on"
                                label="Lugar de expedición DUI" />
                            <x-ui.input type="text" wire:model="client_nationality" icon="flag"
                                label="Nacionalidad" />
                            <x-ui.select wire:model="client_marital_status" icon="diversity_2" label="Estado civil">
                                <option value="">Seleccionar</option>
                                <option value="Soltero/a">Soltero/a</option>
                                <option value="Casado/a">Casado/a</option>
                                <option value="Divorciado/a">Divorciado/a</option>
                                <option value="Viudo/a">Viudo/a</option>
                                <option value="Acompañado/a">Acompañado/a</option>
                            </x-ui.select>
                            <x-ui.input type="text" wire:model="client_spouse_name" icon="diversity_2"
                                label="Nombre del cónyuge" />
                            <x-ui.input type="text" wire:model="client_occupation" icon="work"
                                label="Ocupación" />
                            <x-ui.input type="text" wire:model="client_workplace" icon="business"
                                label="Lugar de trabajo" />
                            <x-ui.input type="text" wire:model="client_position" icon="badge" label="Cargo" />
                            <x-ui.input type="number" wire:model="client_monthly_income" icon="attach_money"
                                label="Ingreso mensual" step="0.01" />
                            <x-ui.input type="text" wire:model="client_boss_name" icon="supervisor_account"
                                label="Jefe inmediato" />
                            <x-ui.input type="text" wire:model="client_work_phone" icon="call"
                                label="Tel. trabajo" />
                            <x-ui.input type="text" wire:model="client_work_address" icon="business"
                                label="Dirección de trabajo" />
                            <x-ui.textarea wire:model="client_billing_address" icon="receipt"
                                label="Dirección de cobro" rows="2"
                                placeholder="Dirección donde recibirá las facturas" class="sm:col-span-2" />
                        </div>
                    </div>

                    {{-- Portal del cliente: docs + coordenadas + firma en un solo enlace --}}
                    <div class="bg-white border border-indigo-200 rounded-xl shadow-sm overflow-hidden">
                        {{-- Header del panel --}}
                        <div class="bg-gradient-to-r from-indigo-600 to-indigo-500 px-5 py-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-white/15 flex items-center justify-center">
                                        <span class="material-symbols-outlined text-white">globe</span>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-bold text-white">Portal del cliente</h4>
                                        <p class="text-xs text-indigo-100">Un solo enlace: documentos, ubicación y firma digital</p>
                                    </div>
                                </div>
                                @if ($portal_link)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-medium bg-white/15 text-white">
                                        <span class="material-symbols-outlined text-xs">link</span>
                                        Enlace activo
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="p-5 space-y-4" x-data="{
                            confirm: '',
                            confirmTitle: '',
                            confirmMessage: '',
                            confirmVariant: 'danger',
                            docType: '',
                            requestConfirm(action, title, message, docType = '') {
                                this.confirm = action;
                                this.confirmTitle = title;
                                this.confirmMessage = message;
                                this.docType = docType;
                                this.confirmVariant = action.startsWith('approve') ? 'success' : 'danger';
                            },
                            execConfirm() {
                                const c = this.confirm;
                                this.confirm = '';
                                if (c === 'approve_docs') $wire.approveClientDocs();
                                else if (c === 'reject_docs') $wire.rejectClientDocs();
                                else if (c === 'reject_all') $wire.rejectAllClientDocs();
                                else if (c === 'reject_doc') $wire.rejectClientDoc(this.docType);
                                else if (c === 'approve_coords') $wire.approveClientCoordinates();
                                else if (c === 'reject_coords') $wire.rejectClientCoordinates();
                                else if (c === 'approve_sig') $wire.approveClientSignature();
                                else if (c === 'reject_sig') $wire.rejectClientSignature();
                            }
                        }" @request-confirm.window="requestConfirm($event.detail.action, $event.detail.title, $event.detail.message, $event.detail.docType ?? '')">
                            @if ($portal_link)
                                {{-- Sección 1: Enlace del portal --}}
                                <div class="bg-indigo-50/60 border border-indigo-100 rounded-xl p-4">
                                    <label class="block text-xs font-semibold text-indigo-800 uppercase tracking-wide mb-2">Enlace del portal</label>
                                    <div class="flex items-center gap-2">
                                        <input type="text" value="{{ $portal_link }}" readonly
                                            class="flex-1 text-xs px-3 py-2 border border-indigo-200 rounded-lg bg-white font-mono focus:outline-none"
                                            onclick="this.select();" />
                                        <button type="button"
                                            onclick="copyToClipboard('{{ $portal_link }}', this)"
                                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition-colors text-xs font-medium">
                                            <span class="material-symbols-outlined text-sm">content_copy</span>
                                            Copiar
                                        </button>
                                    </div>
                                    <div class="flex flex-wrap items-center justify-between gap-2 mt-3">
                                        <span class="inline-flex items-center gap-1 text-[10px] text-indigo-500">
                                            <span class="material-symbols-outlined text-xs">schedule</span>
                                            El enlace expira en 24 horas
                                        </span>
                                        <button wire:click="sendPortalViaWhatsApp"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-green-100 text-green-700 hover:bg-green-200 transition-colors">
                                            <span class="material-symbols-outlined text-sm">chat</span>
                                            Enviar por WhatsApp
                                        </button>
                                    </div>
                                    <div class="flex gap-2 pt-3 mt-3 border-t border-indigo-100">
                                        <button wire:click="refreshUploadedDocs"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium bg-white border border-indigo-200 text-indigo-700 hover:bg-indigo-50 transition-colors">
                                            <span class="material-symbols-outlined text-sm">refresh</span>
                                            Actualizar documentos
                                        </button>
                                        <button wire:click="$set('portal_link', null)"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors">
                                            <span class="material-symbols-outlined text-sm">close</span>
                                            Cerrar enlace
                                        </button>
                                    </div>
                                </div>

                                @if (count($clientUploadedDocs) > 0)
                                    {{-- Sección 2: Documentos recibidos --}}
                                    @php
                                        $_client = \App\Models\Client::find($client_id);
                                        $_docsApproved = $_client?->portal_docs_approved ?? false;
                                    @endphp
                                    <div class="border border-gray-100 rounded-xl overflow-hidden">
                                        <div class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 bg-gray-50 border-b border-gray-100">
                                            <div class="flex items-center gap-2">
                                                <span class="material-symbols-outlined text-gray-500 text-sm">description</span>
                                                <p class="text-xs font-semibold text-gray-700">Documentos recibidos</p>
                                                <span class="inline-flex items-center justify-center min-w-5 h-5 px-1.5 rounded-full bg-indigo-100 text-[10px] font-bold text-indigo-700">
                                                    {{ count($clientUploadedDocs) }}
                                                </span>
                                            </div>
                                            <div class="flex items-center gap-1.5">
                                                @if ($_docsApproved)
                                                    <x-ui.badge variant="success" icon="check_circle">Aprobados</x-ui.badge>
                                                @else
                                                    <button @click="requestConfirm('approve_docs', '¿Aprobar los documentos?', 'El cliente podrá continuar con el proceso de firma.')"
                                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-medium bg-green-100 text-green-700 hover:bg-green-200 transition-colors">
                                                        <span class="material-symbols-outlined text-sm">verified</span>
                                                        Aprobar documentos
                                                    </button>
                                                @endif
                                                <button @click="requestConfirm('reject_docs', '¿Rechazar todos los documentos?', 'Se eliminarán los documentos y el cliente deberá volver a subirlos con el mismo enlace.')"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-medium bg-red-100 text-red-700 hover:bg-red-200 transition-colors">
                                                    <span class="material-symbols-outlined text-sm">delete_sweep</span>
                                                    Rechazar documentos
                                                </button>
                                                <button @click="requestConfirm('reject_all', '¿Rechazar todo el proceso?', 'Se eliminarán documentos, coordenadas y firma. Se generará un enlace nuevo para el cliente.')"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-medium bg-red-50 border border-red-200 text-red-700 hover:bg-red-100 transition-colors">
                                                    <span class="material-symbols-outlined text-sm">block</span>
                                                    Rechazar todo
                                                </button>
                                            </div>
                                        </div>
                                        <div class="p-4 grid grid-cols-2 sm:grid-cols-4 gap-3">
                                            @foreach ($clientUploadedDocs as $doc)
                                                @php
                                                    $label = match ($doc['type']) {
                                                        'dui_front' => 'DUI (Frente)',
                                                        'dui_back' => 'DUI (Reverso)',
                                                        'receipt' => 'Recibo de luz',
                                                        'fachada' => 'Foto de Fachada',
                                                        default => $doc['type'],
                                                    };
                                                    $url = $this->getDocPreviewUrl($doc['path']);
                                                @endphp
                                                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden hover:border-indigo-300 hover:shadow-md transition-all">
                                                    <div class="h-20 bg-gray-50 flex items-center justify-center overflow-hidden">
                                                        @if ($url && isset($doc['mime_type']) && str_starts_with($doc['mime_type'], 'image/'))
                                                            <img src="{{ $url }}" alt="{{ $label }}"
                                                                class="w-full h-full object-cover cursor-pointer hover:opacity-85 transition-opacity"
                                                                onclick="openPreview(this.src, '{{ $contract_id ?? ($ticket_id ?? 'new') }}')" />
                                                        @elseif($url)
                                                            <a href="{{ $url }}" target="_blank"
                                                                class="inline-flex flex-col items-center gap-1 text-xs text-indigo-600 hover:text-indigo-700">
                                                                <span class="material-symbols-outlined text-xl">picture_as_pdf</span>
                                                                Ver PDF
                                                            </a>
                                                        @else
                                                            <span class="material-symbols-outlined text-xl text-green-500">check_circle</span>
                                                        @endif
                                                    </div>
                                                    <div class="p-2 text-center">
                                                        <p class="text-[10px] text-gray-600 font-medium truncate">{{ $label }}</p>
                                                        <button @click="requestConfirm('reject_doc', '¿Rechazar este documento?', 'El documento «{{ $label }}» será eliminado y el cliente deberá volver a subirlo.', '{{ $doc['type'] }}')"
                                                            class="text-[10px] text-red-500 hover:text-red-700 mt-0.5">
                                                            Rechazar
                                                        </button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @php $_clientCoords = $_client ?? \App\Models\Client::find($client_id); @endphp
                                @if($_clientCoords && $_clientCoords->latitude && $_clientCoords->longitude)
                                    {{-- Sección 3: Coordenadas --}}
                                    @php $_coordsApproved = $_clientCoords->coordinates_approved ?? false; @endphp
                                    <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-gray-100 bg-gray-50 px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                                                <span class="material-symbols-outlined text-blue-600 text-base">near_me</span>
                                            </div>
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <p class="text-xs font-semibold text-gray-700">Coordenadas de instalación</p>
                                                    @if($_coordsApproved)
                                                        <x-ui.badge variant="success" icon="verified">Aprobadas</x-ui.badge>
                                                    @else
                                                        <x-ui.badge variant="warning" icon="hourglass_top">Pendientes</x-ui.badge>
                                                    @endif
                                                </div>
                                                <p class="text-[11px] text-gray-500 font-mono">
                                                    {{ $_clientCoords->latitude }}, {{ $_clientCoords->longitude }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <a href="https://www.google.com/maps?q={{ $_clientCoords->latitude }},{{ $_clientCoords->longitude }}"
                                                target="_blank"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-medium bg-blue-100 text-blue-700 hover:bg-blue-200 transition-colors">
                                                <span class="material-symbols-outlined text-sm">map</span>
                                                Ver en mapa
                                            </a>
                                            @if(!$_coordsApproved)
                                                <button @click="requestConfirm('approve_coords', '¿Aprobar las coordenadas?', 'Confirmá que la ubicación capturada es correcta para la instalación.')"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-medium bg-green-100 text-green-700 hover:bg-green-200 transition-colors">
                                                    <span class="material-symbols-outlined text-sm">verified</span>
                                                    Aprobar
                                                </button>
                                            @endif
                                            <button @click="requestConfirm('reject_coords', '¿Rechazar las coordenadas?', 'Se eliminarán las coordenadas y el cliente deberá volver a compartir su ubicación con el mismo enlace.')"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-medium bg-red-100 text-red-700 hover:bg-red-200 transition-colors">
                                                <span class="material-symbols-outlined text-sm">block</span>
                                                Rechazar
                                            </button>
                                        </div>
                                    </div>
                                @endif

                                @if($_clientCoords && $_clientCoords->client_signature_data)
                                    {{-- Sección 4: Firma del cliente --}}
                                    @php $_sig = $_clientCoords->client_signature_data; $_sigApproved = $_clientCoords->signature_approved ?? false; @endphp
                                    <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-gray-100 bg-gray-50 px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                                                <span class="material-symbols-outlined text-green-600 text-base">edit_note</span>
                                            </div>
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <p class="text-xs font-semibold text-gray-700">Firma del cliente</p>
                                                    @if($_sigApproved)
                                                        <x-ui.badge variant="success" icon="verified">Aprobada</x-ui.badge>
                                                    @else
                                                        <x-ui.badge variant="warning" icon="hourglass_top">Pendiente</x-ui.badge>
                                                    @endif
                                                </div>
                                                <img src="{{ $_sig }}" alt="Firma del cliente" onclick="openSignaturePreview()"
                                                    class="h-10 mt-1.5 bg-white rounded border border-gray-200 p-0.5 cursor-pointer hover:border-green-400 transition-colors" />
                                            </div>
                                        </div>
                                        @if(!$_sigApproved)
                                            <div class="flex items-center gap-1.5">
                                                <button @click="requestConfirm('approve_sig', '¿Aprobar la firma?', 'Confirmá que la firma coincide con el documento de identidad.')"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-medium bg-green-100 text-green-700 hover:bg-green-200 transition-colors">
                                                    <span class="material-symbols-outlined text-sm">verified</span>
                                                    Aprobar
                                                </button>
                                                <button @click="requestConfirm('reject_sig', '¿Rechazar la firma?', 'La firma se eliminará y el cliente deberá firmar nuevamente.')"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-medium bg-red-100 text-red-700 hover:bg-red-200 transition-colors">
                                                    <span class="material-symbols-outlined text-sm">block</span>
                                                    Rechazar
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            @else
                                {{-- Estado vacío: sin enlace generado --}}
                                <div class="text-center py-8">
                                    <div class="w-16 h-16 rounded-full bg-indigo-100 flex items-center justify-center mx-auto mb-4">
                                        <span class="material-symbols-outlined text-indigo-600 text-3xl">link</span>
                                    </div>
                                    <h5 class="text-sm font-bold text-gray-800">Generá el enlace del portal</h5>
                                    <p class="text-xs text-gray-500 mt-1 max-w-xs mx-auto">
                                        El cliente podrá subir sus documentos, compartir su ubicación y firmar digitalmente desde un solo enlace.
                                    </p>
                                    <button wire:click="generatePortalLink"
                                        class="mt-4 inline-flex items-center gap-1.5 px-4 py-2.5 bg-indigo-600 text-white text-xs font-semibold rounded-lg hover:bg-indigo-700 transition-colors shadow-sm">
                                        <span class="material-symbols-outlined text-sm">share</span>
                                        Generar enlace del portal
                                    </button>
                                </div>
                            @endif

                            {{-- Modal de confirmación de aprobar/rechazar --}}
                            <div x-show="confirm" x-cloak @click="confirm = ''"
                                class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
                                <div class="bg-white rounded-xl p-6 max-w-sm w-full shadow-xl" @click.stop>
                                    <div class="flex items-start gap-3 mb-4">
                                        <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0"
                                            :class="confirmVariant === 'success' ? 'bg-green-100' : 'bg-red-100'">
                                            <span class="material-symbols-outlined text-xl"
                                                :class="confirmVariant === 'success' ? 'text-green-600' : 'text-red-600'">warning</span>
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-bold text-gray-900" x-text="confirmTitle"></h4>
                                            <p class="text-xs text-gray-500 mt-1 leading-relaxed" x-text="confirmMessage"></p>
                                        </div>
                                    </div>
                                    <div class="flex justify-end gap-3 mt-2">
                                        <button @click="confirm = ''"
                                            class="px-4 py-2 text-xs font-medium rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors">
                                            Cancelar
                                        </button>
                                        <button @click="execConfirm()"
                                            class="inline-flex items-center gap-1 px-4 py-2 text-xs font-medium rounded-lg text-white transition-colors"
                                            :class="confirmVariant === 'success' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700'">
                                            <span class="material-symbols-outlined text-sm">check</span>
                                            Confirmar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Modal para previsualizar firma (JS puro, igual patrón que el preview de imágenes) --}}
                @php
                    $_duiFront = collect($clientUploadedDocs)->firstWhere('type', 'dui_front');
                    $_duiFrontUrl = $_duiFront && isset($_duiFront['path'])
                        ? $this->getDocPreviewUrl($_duiFront['path'])
                        : null;
                @endphp
                @if(isset($_sig))
                    <div id="signature-preview-modal"
                        class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4 hidden"
                        style="display:none;">
                        <div class="bg-white rounded-xl p-6 max-w-3xl w-full shadow-xl max-h-[90vh] overflow-y-auto">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-sm font-bold text-gray-800">Firma del cliente</h3>
                                <div class="flex items-center gap-2">
                                    @if($_sigApproved ?? false)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-green-100 text-green-700">
                                            <span class="material-symbols-outlined text-xs">verified</span>
                                            Aprobada
                                        </span>
                                    @endif
                                    <button type="button" onclick="closeSignaturePreview()" class="text-gray-400 hover:text-gray-600">
                                        <span class="material-symbols-outlined">close</span>
                                    </button>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Firma capturada</p>
                                    <img src="{{ $_sig }}" alt="Firma del cliente" class="w-full bg-white rounded border border-gray-200 p-2" />
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Documento (DUI frente)</p>
                                    @if($_duiFrontUrl)
                                    <img src="{{ $_duiFrontUrl }}" alt="DUI frente" class="w-full bg-white rounded border border-gray-200 p-2" />
                                    @else
                                    <div class="w-full h-40 flex items-center justify-center bg-gray-50 rounded border border-gray-200 text-xs text-gray-400">
                                        No hay DUI disponible para comparar
                                    </div>
                                    @endif
                                </div>
                            </div>
                            <p class="text-xs text-gray-400 text-center mt-3">Compará la firma capturada con la firma estampada en el documento del cliente.</p>
                            <div class="flex justify-between items-center mt-4 pt-4 border-t border-gray-100">
                                @if(!($_sigApproved ?? false))
                                    <div class="flex items-center gap-2">
                                        <button type="button" @click="closeSignaturePreview(); $dispatch('request-confirm', { action: 'approve_sig', title: '¿Aprobar la firma?', message: 'Confirmá que la firma coincide con el documento de identidad.' })"
                                            class="inline-flex items-center gap-1 px-4 py-2 text-xs font-medium rounded-lg bg-green-600 text-white hover:bg-green-700 transition-colors">
                                            <span class="material-symbols-outlined text-sm">verified</span>
                                            Aprobar firma
                                        </button>
                                        <button type="button" @click="closeSignaturePreview(); $dispatch('request-confirm', { action: 'reject_sig', title: '¿Rechazar la firma?', message: 'La firma se eliminará y el cliente deberá firmar nuevamente.' })"
                                            class="inline-flex items-center gap-1 px-4 py-2 text-xs font-medium rounded-lg bg-red-600 text-white hover:bg-red-700 transition-colors">
                                            <span class="material-symbols-outlined text-sm">block</span>
                                            Rechazar firma
                                        </button>
                                    </div>
                                @else
                                    <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                        <span class="material-symbols-outlined text-sm">verified</span>
                                        Firma aprobada
                                    </span>
                                @endif
                                <button type="button" onclick="closeSignaturePreview()"
                                    class="px-4 py-2 text-xs font-medium rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors">
                                    Cerrar
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="flex justify-end pt-4 border-t border-gray-100">
                    <x-ui.button variant="primary" icon="arrow_forward" wire:click="nextStep">
                        Continuar
                    </x-ui.button>
                </div>
            </div>
        @endif

        {{-- Step 2: Plan y Precio --}}
        @if ($step === 2)
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
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Tipo de
                            OT</label>
                        <div
                            class="bg-gray-50 rounded-lg border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-800 capitalize">
                            {{ str_replace('_', ' ', $service_type ?: '—') }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Servicio
                            contratado</label>
                        <div
                            class="bg-gray-50 rounded-lg border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-800 capitalize">
                            @php
                                $planSvc = $this->plan_id ? (\App\Models\Plan::where('id', $this->plan_id)->value('service_type') ?? null) : null;
                                $servCont = match ($planSvc) {
                                    'internet' => 'Internet',
                                    'cable' => 'Cable TV',
                                    'internet_cable' => 'Cable TV + Internet',
                                    default => '—',
                                };
                            @endphp
                            {{ $servCont }}
                        </div>
                    </div>

                    <x-ui.select wire:model.live="zone_id" label="Zona" icon="map">
                        <option value="">Sin zona</option>
                        @foreach ($availableZones as $z)
                            <option value="{{ $z['id'] }}">{{ $z['name'] }}</option>
                        @endforeach
                    </x-ui.select>

                </div>

                {{-- Reglas de instalación (informativo) --}}
                @php
                    $installFeeInfo = null;
                    if ($this->ticket_id) {
                        $tk = \App\Models\Ticket::find($this->ticket_id);
                        if ($tk) {
                            $installFeeInfo = app(\App\Services\VerificationPricingService::class)->installFeeFor($tk);
                        }
                    }
                @endphp
                @if ($installFeeInfo && ((float) $installFeeInfo['fee'] > 0 || (float) $installFeeInfo['excess_per_50m'] > 0))
                    <div class="mt-4 bg-sky-50 border border-sky-200 rounded-lg p-4">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="material-symbols-outlined text-sky-600 text-sm">straighten</span>
                            <span class="text-xs font-semibold text-sky-700 uppercase tracking-wide">Reglas de
                                instalación</span>
                        </div>
                        <p class="text-xs text-sky-800">
                            Instalación: <strong>${{ number_format($installFeeInfo['fee'], 2) }}</strong> por los
                            primeros {{ $installFeeInfo['covered_meters'] }} m. Recargo de
                            <strong>${{ number_format($installFeeInfo['excess_per_50m'], 2) }}</strong> por cada 50 m
                            adicionales. Se verificará en campo al instalar.
                        </p>
                    </div>
                @endif

                {{-- Promoción de instalación gratis vigente --}}
                @php
                    $_freeListWf = $this->ticket_id ? app(\App\Services\VerificationPricingService::class)->freeInstallationInfo(\App\Models\Ticket::find($this->ticket_id)) : [];
                    $_servsWf = ['internet' => 'Internet', 'cable' => 'Cable', 'internet_cable' => 'Internet + Cable', 'all' => 'todos los servicios'];
                @endphp
                @if(count($_freeListWf) > 0)
                    <div class="mt-4 bg-purple-50 border border-purple-200 rounded-lg p-4">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="material-symbols-outlined text-purple-600 text-sm">local_activity</span>
                            <span class="text-xs font-semibold text-purple-700 uppercase tracking-wide">Promoción: Instalación gratis</span>
                        </div>
                        @foreach($_freeListWf as $_freeWf)
                        <p class="text-xs text-purple-700 mt-1">
                            <strong>{{ $_freeWf['name'] }}</strong>
                            @if ($_freeWf['month'])
                            <span class="text-purple-600">· mes de {{ $_freeWf['month'] }}</span>
                            @endif
                            — Aplica para <strong>{{ $_servsWf[$_freeWf['service']] ?? $_freeWf['service'] }}</strong>. Instalación base $0; si excede los {{ $installFeeInfo['covered_meters'] ?? 150 }} m se cobra solo el recargo.
                        </p>
                        @endforeach
                    </div>
                @endif

                {{-- Catálogo de planes agrupados por tipo --}}
                @php
                    $internetPlans = $availablePlans->filter(fn($p) => $p->service_type === 'internet');
                    $cablePlans = $availablePlans->filter(fn($p) => $p->service_type === 'cable');
                    $comboPlans = $availablePlans->filter(fn($p) => $p->service_type === 'internet_cable');
                    $zoneModel = $this->getZoneModel();
                @endphp

                {{-- Internet --}}
                @if ($internetPlans->count() > 0)
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <span class="material-symbols-outlined text-blue-600 text-lg">wifi</span>
                            <span class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Internet</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach ($internetPlans as $plan)
                                @php
                                    $effPrice = $zoneModel
                                        ? (float) $zoneModel->getEffectivePriceForPlan($plan)
                                        : (float) $plan->base_price;
                                    $isSelected = $plan_id == $plan->id;
                                @endphp
                                @include('livewire.contracts._plan_card', [
                                    'plan' => $plan,
                                    'effPrice' => $effPrice,
                                    'isSelected' => $isSelected,
                                ])
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Cable --}}
                @if ($cablePlans->count() > 0)
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <span class="material-symbols-outlined text-gray-500 text-lg">live_tv</span>
                            <span class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Cable</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach ($cablePlans as $plan)
                                @php
                                    $effPrice = $zoneModel
                                        ? (float) $zoneModel->getEffectivePriceForPlan($plan)
                                        : (float) $plan->base_price;
                                    $isSelected = $plan_id == $plan->id;
                                @endphp
                                @include('livewire.contracts._plan_card', [
                                    'plan' => $plan,
                                    'effPrice' => $effPrice,
                                    'isSelected' => $isSelected,
                                ])
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Internet + Cable --}}
                @if ($comboPlans->count() > 0)
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <span class="material-symbols-outlined text-orange-600 text-lg">live_tv</span>
                            <span class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Internet +
                                Cable</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach ($comboPlans as $plan)
                                @php
                                    $effPrice = $zoneModel
                                        ? (float) $zoneModel->getEffectivePriceForPlan($plan)
                                        : (float) $plan->base_price;
                                    $isSelected = $plan_id == $plan->id;
                                @endphp
                                @include('livewire.contracts._plan_card', [
                                    'plan' => $plan,
                                    'effPrice' => $effPrice,
                                    'isSelected' => $isSelected,
                                ])
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Precio personalizado + Beneficios --}}
                @if ($plan_id)
                    {{-- Datos comerciales del contrato --}}
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="material-symbols-outlined text-gray-600 text-sm">description</span>
                            <span class="text-xs font-semibold text-gray-700 uppercase tracking-wide">Datos del
                                contrato</span>
                        </div>
                        <x-ui.toggle wire:model.live="apply_plazo" onColor="green"
                            label="¿El cliente paga por plazo?"
                            description="ON = aplica beneficios de permanencia (meses gratis, doble velocidad, descuento por 12/24 meses). OFF = paga mes a mes, tarifa normal sin beneficios." />
                        @if (!$apply_plazo)
                            <p class="text-[11px] text-amber-600 mt-2 flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px]">info</span>
                                Pago mes a mes: no se aplican beneficios ni promociones de permanencia.
                            </p>
                        @endif
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-4 pt-4 border-t-2 border-gray-300">
                            <x-ui.select wire:model="contract_type" icon="assignment" label="Tipo de contrato">
                                <option value="nuevo">Nuevo</option>
                                <option value="reconexion">Reconexión</option>
                                <option value="renovacion">Renovación</option>
                            </x-ui.select>
                            <x-ui.input type="number" wire:model.live="term_months" icon="calendar_month"
                                label="Plazo (meses)" min="1" max="60" />
                            <x-ui.input type="text" wire:model.live="benefit" icon="card_giftcard"
                                label="Beneficio / Promoción" placeholder="Opcional" />
                        </div>
                    </div>
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <p class="text-sm font-medium text-gray-700 flex items-center gap-2">
                            <span class="material-symbols-outlined text-base">info</span>
                            Precio del plan
                        </p>
                        <div class="mt-2">
                            @php $detail = $this->planPriceDetail; @endphp
                            @if ($detail)
                                <p class="text-xs text-gray-600">
                                    Precio base: <strong>${{ number_format($detail['base_price'], 2) }}</strong>
                                    @if ($detail['has_override'])
                                        → Precio efectivo:
                                        <strong>${{ number_format($detail['effective_price'], 2) }}</strong>
                                    @endif
                                </p>
                            @endif
                        </div>
                        <div class="mt-2">
                            <x-ui.input type="number" wire:model="price" icon="attach_money"
                                label="Precio a facturar" step="0.01" min="0" placeholder="0.00" />
                        </div>

                        {{-- TV extra: equipo adicional para otra pantalla --}}
                        <div class="mt-3 border-t border-gray-100 pt-3">
                            <label class="block text-xs font-semibold text-gray-700 mb-2 flex items-center gap-2">
                                <span class="material-symbols-outlined text-gray-500 text-sm">live_tv</span>
                                TVs extra (otras pantallas)
                            </label>
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <x-ui.input type="number" wire:model.live="extra_tvs" icon="tv"
                                        label="Cantidad de TVs extra" min="0" max="10" placeholder="0" />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-2 flex items-center gap-2">
                                        <span class="material-symbols-outlined text-gray-500 text-sm">handyman</span>
                                        Cargo instalación (único)
                                    </label>
                                    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                                        <div class="space-y-2 font-mono text-[13px]">
                                            @php $vbd = $this->verificationBreakdown; @endphp
                                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Instalación</p>
                                            @if($vbd)
                                            <div class="flex items-baseline gap-2">
                                                <span class="text-gray-600">Base ({{ $vbd['covered'] }} m)</span>
                                                <span class="flex-1 border-b border-dotted border-gray-300"></span>
                                                <span class="font-medium text-gray-800">${{ number_format($vbd['base'], 2) }}</span>
                                            </div>
                                            @if($vbd['blocks'] > 0)
                                            <div class="flex items-baseline gap-2">
                                                <span class="text-gray-600">{{ $vbd['distance'] }} m (+{{ $vbd['blocks'] }} × ${{ number_format($vbd['excess_per_50m'], 2) }})</span>
                                                <span class="flex-1 border-b border-dotted border-gray-300"></span>
                                                <span class="font-medium text-gray-800">${{ number_format($vbd['excess_total'], 2) }}</span>
                                            </div>
                                            @endif
                                            <div class="flex items-baseline justify-between gap-2 mt-1 px-2 py-1.5 rounded bg-green-100 border border-green-200">
                                                <span class="text-[11px] font-bold text-green-800">Subtotal instalación</span>
                                                <span class="font-extrabold text-green-800">${{ number_format(($vbd['subtotal'] ?? 0), 2) }}</span>
                                            </div>
                                            @elseif((float)($this->installation_cost ?? 0) > 0)
                                            <div class="flex items-baseline gap-2">
                                                <span class="text-gray-600">Instalación</span>
                                                <span class="flex-1 border-b border-dotted border-gray-300"></span>
                                                <span class="font-medium text-gray-800">${{ number_format((float)($this->installation_cost ?? 0), 2) }}</span>
                                            </div>
                                            @endif
                                            @if((float)($this->tv_install_fee ?? 0) > 0)
                                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide pt-1">TVs extra</p>
                                            <div class="flex items-baseline gap-2">
                                                <span class="text-gray-600">Instalación TV ({{ $this->extra_tvs }} × $6)</span>
                                                <span class="flex-1 border-b border-dotted border-gray-300"></span>
                                                <span class="font-medium text-gray-800">${{ number_format((float)$this->tv_install_fee, 2) }}</span>
                                            </div>
                                            @endif
                                        </div>
                                        <div class="mt-3 rounded-lg bg-green-50 border border-green-200 px-4 py-2.5 flex items-center justify-between gap-3">
                                            <span class="text-[12px] font-bold text-green-700 tracking-wide">TOTAL INSTALACIÓN</span>
                                            <span class="text-xl font-mono font-extrabold text-green-700 leading-none">${{ number_format($this->getInstallTotal(), 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-2 flex items-center gap-2">
                                        <span class="material-symbols-outlined text-gray-500 text-sm">receipt_long</span>
                                        Cuota mensual total
                                    </label>
                                    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                                        <div class="space-y-2 font-mono text-[13px]">
                                            <div class="flex items-baseline gap-2">
                                                <span class="text-gray-600">Plan base</span>
                                                <span class="flex-1 border-b border-dotted border-gray-300"></span>
                                                <span class="font-medium text-gray-800">${{ number_format((float)($this->price ?? 0), 2) }}</span>
                                            </div>
                                            @if((float)($this->monthly_extra_fee ?? 0) > 0)
                                            <div class="flex items-baseline gap-2">
                                                <span class="text-gray-600">TVs extra ({{ $this->extra_tvs }} × $1)</span>
                                                <span class="flex-1 border-b border-dotted border-gray-300"></span>
                                                <span class="font-medium text-gray-800">+${{ number_format((float)$this->monthly_extra_fee, 2) }}</span>
                                            </div>
                                            @endif
                                        </div>
                                        <div class="mt-3 rounded-lg bg-green-600 px-4 py-3 flex items-center justify-between gap-3">
                                            <span class="text-[13px] font-bold text-white tracking-wide">TOTAL A PAGAR / MES</span>
                                            <span class="text-2xl font-mono font-extrabold text-white leading-none">${{ number_format($this->getMonthlyTotal(), 2) }}</span>
                                        </div>
                                        @if ($apply_plazo && (int)($this->term_months ?? 0) > 0)
                                        <div class="mt-2 rounded-lg bg-green-700 px-4 py-2.5 flex items-center justify-between gap-3">
                                            <span class="text-[12px] font-bold text-white tracking-wide">TOTAL POR {{ (int)$this->term_months }} MESES</span>
                                            <span class="text-lg font-mono font-extrabold text-white leading-none">${{ number_format($this->getMonthlyTotal() * (int)$this->term_months, 2) }}</span>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                </div>
                            </div>
                            @if($extra_tvs > 0)
                                <p class="text-[11px] text-gray-400 mt-2">
                                    +$1 mensual por cada TV extra y +$6 de instalación por cada TV. Se registrará para la facturación recurrente.
                                </p>
                            @endif

                    {{-- Fecha de pago del cliente (independiente del plan) --}}
                    <div class="mt-3 border-t border-gray-100 pt-3">
                        <label class="block text-xs font-semibold text-gray-700 mb-2 flex items-center gap-2">
                            <span class="material-symbols-outlined text-gray-500 text-sm">event_repeat</span>
                            Fecha de pago del cliente
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <x-ui.input type="date" wire:model.live="payment_date" icon="calendar_today"
                                    label="Próxima fecha de pago" />
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Día de pago del cliente</label>
                                <p class="px-3 py-2.5 bg-blue-50 rounded-lg border border-blue-200 text-sm font-bold text-blue-800">
                                    {{ $payment_day ? $payment_day . ' de cada mes' : '—' }}
                                </p>
                            </div>
                        </div>
                        <p class="text-[11px] text-gray-400 mt-2">Seleccioná la fecha de pago. El día (ej. 15) se usa para calcular el abono proporcional al instalar, y se mostrará como "15 de cada mes" en el contrato.</p>

                        @php $abono = $this->abonoPreview; @endphp
                        @if ($abono)
                        <div class="mt-3 rounded-xl border border-green-200 bg-white overflow-hidden">
                            <div class="flex items-center justify-between gap-2 px-4 py-2.5 bg-green-50 border-b border-green-200">
                                <span class="text-[11px] font-bold text-green-700 uppercase tracking-wide">Abono a cobrar al instalar</span>
                                <span class="text-xl font-mono font-extrabold text-green-700">${{ number_format($abono['charge'], 2) }}</span>
                            </div>
                            <div class="px-4 py-2.5 font-mono text-[12px] text-gray-600">
                                Cuota ${{ number_format($abono['base'], 2) }} ÷ {{ $abono['days_in_month'] }} días del mes × {{ $abono['days'] }} días (hasta el {{ $abono['payment_day'] }}) = <strong>${{ number_format($abono['charge'], 2) }}</strong>
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- Promociones automáticas (meses gratis / doble velocidad) --}}
                    @if($promo_free_months > 0 || $promo_double_speed)
                        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="material-symbols-outlined text-purple-600 text-sm">local_activity</span>
                                <span class="text-xs font-semibold text-purple-800 uppercase tracking-wide">Promociones aplicadas</span>
                            </div>
                            <ul class="space-y-1 text-sm text-purple-800">
                                @if($promo_free_months > 0)
                                    <li class="flex items-center gap-2">
                                        <span class="material-symbols-outlined text-purple-600 text-base">calendar_month</span>
                                        Pagando {{ $promo_pay_months }} meses se te regalan {{ $promo_free_months }}.
                                    </li>
                                @endif
                                @if($promo_double_speed)
                                    <li class="flex items-center gap-2">
                                        <span class="material-symbols-outlined text-purple-600 text-base">speed</span>
                                        Doble velocidad: {{ $promo_original_speed }} → {{ $promo_display_speed }}
                                    </li>
                                @endif
                            </ul>
                        </div>
                    @endif

                    {{-- Beneficios interactivos --}}
                    @if (count($availableBenefits) > 0)
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-green-600 text-sm">card_giftcard</span>
                                    <span
                                        class="text-xs font-semibold text-green-800 uppercase tracking-wide">Beneficios
                                        disponibles</span>
                                </div>
                                <button wire:click="resetBenefits"
                                    class="text-xs text-green-700 hover:text-green-800 flex items-center gap-1"
                                    title="Restablecer todos">
                                    <span class="material-symbols-outlined text-sm">refresh</span>
                                    Restablecer
                                </button>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($availableBenefits as $ruleKey => $benefitData)
                                    @php $isActive = in_array($ruleKey, $selectedBenefits); @endphp
                                    <button wire:click="toggleBenefit('{{ $ruleKey }}')" type="button"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium transition-all duration-150
                                {{ $isActive
                                    ? 'bg-green-600 text-white shadow-sm hover:bg-green-700'
                                    : 'bg-white text-gray-500 border border-gray-300 hover:border-gray-400 hover:text-gray-700' }}">
                                        <span class="material-symbols-outlined text-sm">
                                            {{ $isActive ? 'check_circle' : 'radio_button_unchecked' }}
                                        </span>
                                        {{ $benefitData['label'] }}
                                    </button>
                                @endforeach
                            </div>
                            @if ($selectedBenefits)
                                <div class="mt-3 pt-3 border-t border-green-200">
                                    <p class="text-xs text-green-700">
                                        <span class="font-semibold">Beneficios seleccionados:</span>
                                        {{ $this->getAppliedBenefits() }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    @endif
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
        @if ($step === 3)
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
                            $pct =
                                $docProgress['total_required'] > 0
                                    ? ($docProgress['completed_required'] / $docProgress['total_required']) * 100
                                    : 0;
                        @endphp
                        <div class="h-full bg-indigo-600 rounded-full transition-all duration-500"
                            style="width: {{ $pct }}%"></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">
                        {{ $docProgress['completed_required'] }}/{{ $docProgress['total_required'] }} obligatorios
                        @if ($docProgress['completed_optional'] > 0)
                            · {{ $docProgress['completed_optional'] }} opcionales
                        @endif
                    </p>
                </div>

                {{-- Document uploaders --}}
                @php
                    $preview = fn($path, $mime) => $path ? $this->getDocPreviewUrl($path) : null;
                    $portalDoc = fn($type) => collect($clientUploadedDocs)->firstWhere('type', $type);
                @endphp
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- DUI Frente (obligatorio) --}}
                    @php $_pDoc = $portalDoc('dui_front'); @endphp
                    <div
                        class="border-2 border-dashed rounded-xl p-4 text-center relative
                        {{ $_pDoc || isset($uploadedDocuments['dui_front']) ? 'border-green-300 bg-green-50' : 'border-gray-300 hover:border-indigo-300' }}">
                        <span
                            class="material-symbols-outlined text-3xl
                            {{ $_pDoc || isset($uploadedDocuments['dui_front']) ? 'text-green-500' : 'text-gray-300' }}">badge</span>
                        <p class="text-sm font-medium text-gray-700 mt-1">DUI (Frente) *</p>
                        @if ($_pDoc)
                            @php $_url = $preview($_pDoc['path'], $_pDoc['mime_type'] ?? ''); @endphp
                            @if ($_url && str_starts_with($_pDoc['mime_type'] ?? '', 'image/'))
                                <img src="{{ $_url }}"
                                    class="mt-2 max-h-28 mx-auto rounded-lg border border-green-200 cursor-pointer preview-img"
                                    onclick="openPreview(this.src, '{{ $contract_id ?? ($ticket_id ?? 'new') }}')" />
                            @elseif($_url)
                                <a href="{{ $_url }}" target="_blank"
                                    class="mt-2 inline-flex items-center gap-1 text-xs text-indigo-600 hover:text-indigo-700">
                                    <span class="material-symbols-outlined text-sm">picture_as_pdf</span> Ver PDF
                                </a>
                            @endif
                            <span
                                class="inline-flex items-center gap-1 mt-2 px-2 py-0.5 rounded-full text-[10px] font-medium bg-blue-100 text-blue-700">
                                <span class="material-symbols-outlined text-xs">cloud_done</span>
                                Recibido del portal
                            </span>
                            @if ($_url)
                                <a href="{{ $_url }}" target="_blank"
                                    class="block text-xs text-indigo-600 hover:text-indigo-700 mt-1">Descargar</a>
                            @endif
                        @elseif (isset($uploadedDocuments['dui_front']))
                            @php $url = $preview($uploadedDocuments['dui_front']['path'], $uploadedDocuments['dui_front']['mime_type'] ?? ''); @endphp
                            @if ($url && str_starts_with($uploadedDocuments['dui_front']['mime_type'] ?? '', 'image/'))
                                <img src="{{ $url }}"
                                    class="mt-2 max-h-28 mx-auto rounded-lg border border-green-200 cursor-pointer preview-img"
                                    onclick="openPreview(this.src, '{{ $contract_id ?? ($ticket_id ?? 'new') }}')" />
                            @elseif($url)
                                <a href="{{ $url }}" target="_blank"
                                    class="mt-2 inline-flex items-center gap-1 text-xs text-indigo-600 hover:text-indigo-700">
                                    <span class="material-symbols-outlined text-sm">visibility</span> Ver PDF
                                </a>
                            @endif
                            <div x-data="{ confirmDelete: false }" class="mt-1">
                                <button @click="confirmDelete = true"
                                    class="text-xs text-red-600 hover:text-red-700 block mx-auto">Eliminar</button>

                                {{-- Modal de confirmación --}}
                                <x-ui.modal title="Eliminar documento" icon="warning" maxWidth="sm"
                                    :show="false" x-show="confirmDelete" @click.away="confirmDelete = false">
                                    <div class="text-center">
                                        <div
                                            class="w-14 h-14 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-3">
                                            <span
                                                class="material-symbols-outlined text-2xl text-red-600">delete_forever</span>
                                        </div>
                                        <p class="text-sm text-gray-600">¿Estás seguro de eliminar el documento
                                            <strong>DUI (Frente)</strong>?
                                        </p>
                                        <p class="text-xs text-gray-400 mt-1">Esta acción no se puede deshacer.</p>
                                    </div>
                                    <x-slot:footer>
                                        <x-ui.button variant="danger" icon="delete"
                                            @click="confirmDelete = false; $wire.removeDocument('dui_front')">
                                            Sí, eliminar
                                        </x-ui.button>
                                        <x-ui.button variant="secondary" @click="confirmDelete = false">
                                            Cancelar
                                        </x-ui.button>
                                    </x-slot:footer>
                                </x-ui.modal>
                            </div>
                        @else
                            <input type="file" wire:model="dui_front" accept="image/*,.pdf"
                                class="mt-2 text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                            <div wire:loading wire:target="dui_front" class="mt-2 flex items-center justify-center gap-2">
                                <div class="w-4 h-4 border-2 border-indigo-400 border-t-transparent rounded-full animate-spin"></div>
                                <span class="text-xs text-indigo-600">Subiendo...</span>
                            </div>
                            @error('dui_front')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        @endif
                    </div>

                    {{-- DUI Reverso (obligatorio) --}}
                    @php $_pDoc = $portalDoc('dui_back'); @endphp
                    <div
                        class="border-2 border-dashed rounded-xl p-4 text-center relative
                        {{ $_pDoc || isset($uploadedDocuments['dui_back']) ? 'border-green-300 bg-green-50' : 'border-gray-300 hover:border-indigo-300' }}">
                        <span
                            class="material-symbols-outlined text-3xl
                            {{ $_pDoc || isset($uploadedDocuments['dui_back']) ? 'text-green-500' : 'text-gray-300' }}">badge</span>
                        <p class="text-sm font-medium text-gray-700 mt-1">DUI (Reverso) *</p>
                        @if ($_pDoc)
                            @php $_url = $preview($_pDoc['path'], $_pDoc['mime_type'] ?? ''); @endphp
                            @if ($_url && str_starts_with($_pDoc['mime_type'] ?? '', 'image/'))
                                <img src="{{ $_url }}"
                                    class="mt-2 max-h-28 mx-auto rounded-lg border border-green-200 cursor-pointer preview-img"
                                    onclick="openPreview(this.src, '{{ $contract_id ?? ($ticket_id ?? 'new') }}')" />
                            @elseif($_url)
                                <a href="{{ $_url }}" target="_blank"
                                    class="mt-2 inline-flex items-center gap-1 text-xs text-indigo-600 hover:text-indigo-700">
                                    <span class="material-symbols-outlined text-sm">picture_as_pdf</span> Ver PDF
                                </a>
                            @endif
                            <span
                                class="inline-flex items-center gap-1 mt-2 px-2 py-0.5 rounded-full text-[10px] font-medium bg-blue-100 text-blue-700">
                                <span class="material-symbols-outlined text-xs">cloud_done</span>
                                Recibido del portal
                            </span>
                            @if ($_url)
                                <a href="{{ $_url }}" target="_blank"
                                    class="block text-xs text-indigo-600 hover:text-indigo-700 mt-1">Descargar</a>
                            @endif
                        @elseif (isset($uploadedDocuments['dui_back']))
                            @php $url = $preview($uploadedDocuments['dui_back']['path'], $uploadedDocuments['dui_back']['mime_type'] ?? ''); @endphp
                            @if ($url && str_starts_with($uploadedDocuments['dui_back']['mime_type'] ?? '', 'image/'))
                                <img src="{{ $url }}"
                                    class="mt-2 max-h-28 mx-auto rounded-lg border border-green-200 cursor-pointer preview-img"
                                    onclick="openPreview(this.src, '{{ $contract_id ?? ($ticket_id ?? 'new') }}')" />
                            @elseif($url)
                                <a href="{{ $url }}" target="_blank"
                                    class="mt-2 inline-flex items-center gap-1 text-xs text-indigo-600 hover:text-indigo-700">
                                    <span class="material-symbols-outlined text-sm">visibility</span> Ver PDF
                                </a>
                            @endif
                            <div x-data="{ confirmDelete: false }" class="mt-1">
                                <button @click="confirmDelete = true"
                                    class="text-xs text-red-600 hover:text-red-700 block mx-auto">Eliminar</button>

                                {{-- Modal de confirmación --}}
                                <x-ui.modal title="Eliminar documento" icon="warning" maxWidth="sm"
                                    :show="false" x-show="confirmDelete" @click.away="confirmDelete = false">
                                    <div class="text-center">
                                        <div
                                            class="w-14 h-14 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-3">
                                            <span
                                                class="material-symbols-outlined text-2xl text-red-600">delete_forever</span>
                                        </div>
                                        <p class="text-sm text-gray-600">¿Estás seguro de eliminar el documento
                                            <strong>DUI (Reverso)</strong>?
                                        </p>
                                        <p class="text-xs text-gray-400 mt-1">Esta acción no se puede deshacer.</p>
                                    </div>
                                    <x-slot:footer>
                                        <x-ui.button variant="danger" icon="delete"
                                            @click="confirmDelete = false; $wire.removeDocument('dui_back')">
                                            Sí, eliminar
                                        </x-ui.button>
                                        <x-ui.button variant="secondary" @click="confirmDelete = false">
                                            Cancelar
                                        </x-ui.button>
                                    </x-slot:footer>
                                </x-ui.modal>
                            </div>
                        @else
                            <input type="file" wire:model="dui_back" accept="image/*,.pdf"
                                class="mt-2 text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                            <div wire:loading wire:target="dui_back" class="mt-2 flex items-center justify-center gap-2">
                                <div class="w-4 h-4 border-2 border-indigo-400 border-t-transparent rounded-full animate-spin"></div>
                                <span class="text-xs text-indigo-600">Subiendo...</span>
                            </div>
                            @error('dui_back')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        @endif
                    </div>

                    {{-- Recibo de luz --}}
                    @php $_pDoc = $portalDoc('receipt'); @endphp
                    <div
                        class="border-2 border-dashed rounded-xl p-4 text-center relative
                        {{ $_pDoc || isset($uploadedDocuments['receipt']) ? 'border-green-300 bg-green-50' : 'border-gray-300 hover:border-gray-400' }}">
                        <span
                            class="material-symbols-outlined text-3xl
                            {{ $_pDoc || isset($uploadedDocuments['receipt']) ? 'text-green-500' : 'text-gray-300' }}">receipt</span>
                        <p class="text-sm font-medium text-gray-700 mt-1">Recibo de luz *</p>
                        @if ($_pDoc)
                            @php $_url = $preview($_pDoc['path'], $_pDoc['mime_type'] ?? ''); @endphp
                            @if ($_url && str_starts_with($_pDoc['mime_type'] ?? '', 'image/'))
                                <img src="{{ $_url }}"
                                    class="mt-2 max-h-28 mx-auto rounded-lg border border-green-200 cursor-pointer preview-img"
                                    onclick="openPreview(this.src, '{{ $contract_id ?? ($ticket_id ?? 'new') }}')" />
                            @elseif($_url)
                                <a href="{{ $_url }}" target="_blank"
                                    class="mt-2 inline-flex items-center gap-1 text-xs text-indigo-600 hover:text-indigo-700">
                                    <span class="material-symbols-outlined text-sm">picture_as_pdf</span> Ver PDF
                                </a>
                            @endif
                            <span
                                class="inline-flex items-center gap-1 mt-2 px-2 py-0.5 rounded-full text-[10px] font-medium bg-blue-100 text-blue-700">
                                <span class="material-symbols-outlined text-xs">cloud_done</span>
                                Recibido del portal
                            </span>
                            @if ($_url)
                                <a href="{{ $_url }}" target="_blank"
                                    class="block text-xs text-indigo-600 hover:text-indigo-700 mt-1">Descargar</a>
                            @endif
                        @elseif (isset($uploadedDocuments['receipt']))
                            @php $url = $preview($uploadedDocuments['receipt']['path'], $uploadedDocuments['receipt']['mime_type'] ?? ''); @endphp
                            @if ($url && str_starts_with($uploadedDocuments['receipt']['mime_type'] ?? '', 'image/'))
                                <img src="{{ $url }}"
                                    class="mt-2 max-h-28 mx-auto rounded-lg border border-green-200 cursor-pointer preview-img"
                                    onclick="openPreview(this.src, '{{ $contract_id ?? ($ticket_id ?? 'new') }}')" />
                            @elseif($url)
                                <a href="{{ $url }}" target="_blank"
                                    class="mt-2 inline-flex items-center gap-1 text-xs text-indigo-600 hover:text-indigo-700">
                                    <span class="material-symbols-outlined text-sm">visibility</span> Ver PDF
                                </a>
                            @endif
                            <div x-data="{ confirmDelete: false }" class="mt-1">
                                <button @click="confirmDelete = true"
                                    class="text-xs text-red-600 hover:text-red-700 block mx-auto">Eliminar</button>

                                {{-- Modal de confirmación --}}
                                <x-ui.modal title="Eliminar documento" icon="warning" maxWidth="sm"
                                    :show="false" x-show="confirmDelete" @click.away="confirmDelete = false">
                                    <div class="text-center">
                                        <div
                                            class="w-14 h-14 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-3">
                                            <span
                                                class="material-symbols-outlined text-2xl text-red-600">delete_forever</span>
                                        </div>
                                        <p class="text-sm text-gray-600">¿Estás seguro de eliminar el documento
                                            <strong>Recibo de luz</strong>?
                                        </p>
                                        <p class="text-xs text-gray-400 mt-1">Esta acción no se puede deshacer.</p>
                                    </div>
                                    <x-slot:footer>
                                        <x-ui.button variant="danger" icon="delete"
                                            @click="confirmDelete = false; $wire.removeDocument('receipt')">
                                            Sí, eliminar
                                        </x-ui.button>
                                        <x-ui.button variant="secondary" @click="confirmDelete = false">
                                            Cancelar
                                        </x-ui.button>
                                    </x-slot:footer>
                                </x-ui.modal>
                            </div>
                        @else
                            <input type="file" wire:model="receipt" accept="image/*,.pdf"
                                class="mt-2 text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200" />
                            <div wire:loading wire:target="receipt" class="mt-2 flex items-center justify-center gap-2">
                                <div class="w-4 h-4 border-2 border-gray-400 border-t-transparent rounded-full animate-spin"></div>
                                <span class="text-xs text-gray-600">Subiendo...</span>
                            </div>
                        @endif
                    </div>

                    {{-- Foto de Fachada --}}
                    @php $_pDoc = $portalDoc('fachada'); @endphp
                    <div
                        class="border-2 border-dashed rounded-xl p-4 text-center relative
                        {{ $_pDoc || isset($uploadedDocuments['fachada']) ? 'border-green-300 bg-green-50' : 'border-gray-300 hover:border-gray-400' }}">
                        <span
                            class="material-symbols-outlined text-3xl
                            {{ $_pDoc || isset($uploadedDocuments['fachada']) ? 'text-green-500' : 'text-gray-300' }}">home</span>
                        <p class="text-sm font-medium text-gray-700 mt-1">Foto de Fachada *</p>
                        <p class="text-xs text-gray-400 mt-0.5">Para que los técnicos identifiquen la casa</p>
                        @if ($_pDoc)
                            @php $_url = $preview($_pDoc['path'], $_pDoc['mime_type'] ?? ''); @endphp
                            @if ($_url && str_starts_with($_pDoc['mime_type'] ?? '', 'image/'))
                                <img src="{{ $_url }}"
                                    class="mt-2 max-h-28 mx-auto rounded-lg border border-green-200 cursor-pointer preview-img"
                                    onclick="openPreview(this.src, '{{ $contract_id ?? ($ticket_id ?? 'new') }}')" />
                            @elseif($_url)
                                <a href="{{ $_url }}" target="_blank"
                                    class="mt-2 inline-flex items-center gap-1 text-xs text-indigo-600 hover:text-indigo-700">
                                    <span class="material-symbols-outlined text-sm">picture_as_pdf</span> Ver PDF
                                </a>
                            @endif
                            <span
                                class="inline-flex items-center gap-1 mt-2 px-2 py-0.5 rounded-full text-[10px] font-medium bg-blue-100 text-blue-700">
                                <span class="material-symbols-outlined text-xs">cloud_done</span>
                                Recibido del portal
                            </span>
                            @if ($_url)
                                <a href="{{ $_url }}" target="_blank"
                                    class="block text-xs text-indigo-600 hover:text-indigo-700 mt-1">Descargar</a>
                            @endif
                        @elseif (isset($uploadedDocuments['fachada']))
                            @php $url = $preview($uploadedDocuments['fachada']['path'], $uploadedDocuments['fachada']['mime_type'] ?? ''); @endphp
                            @if ($url && str_starts_with($uploadedDocuments['fachada']['mime_type'] ?? '', 'image/'))
                                <img src="{{ $url }}"
                                    class="mt-2 max-h-28 mx-auto rounded-lg border border-green-200 cursor-pointer preview-img"
                                    onclick="openPreview(this.src, '{{ $contract_id ?? ($ticket_id ?? 'new') }}')" />
                            @endif
                            <div x-data="{ confirmDelete: false }" class="mt-1">
                                <button @click="confirmDelete = true"
                                    class="text-xs text-red-600 hover:text-red-700 block mx-auto">Eliminar</button>

                                {{-- Modal de confirmación --}}
                                <x-ui.modal title="Eliminar documento" icon="warning" maxWidth="sm"
                                    :show="false" x-show="confirmDelete" @click.away="confirmDelete = false">
                                    <div class="text-center">
                                        <div
                                            class="w-14 h-14 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-3">
                                            <span
                                                class="material-symbols-outlined text-2xl text-red-600">delete_forever</span>
                                        </div>
                                        <p class="text-sm text-gray-600">¿Estás seguro de eliminar la <strong>Foto de
                                                Fachada</strong>?</p>
                                        <p class="text-xs text-gray-400 mt-1">Esta acción no se puede deshacer.</p>
                                    </div>
                                    <x-slot:footer>
                                        <x-ui.button variant="danger" icon="delete"
                                            @click="confirmDelete = false; $wire.removeDocument('fachada')">
                                            Sí, eliminar
                                        </x-ui.button>
                                        <x-ui.button variant="secondary" @click="confirmDelete = false">
                                            Cancelar
                                        </x-ui.button>
                                    </x-slot:footer>
                                </x-ui.modal>
                            </div>
                        @else
                            <input type="file" wire:model="fachada" accept="image/*"
                                class="mt-2 text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200" />
                            <div wire:loading wire:target="fachada" class="mt-2 flex items-center justify-center gap-2">
                                <div class="w-4 h-4 border-2 border-gray-400 border-t-transparent rounded-full animate-spin"></div>
                                <span class="text-xs text-gray-600">Subiendo...</span>
                            </div>
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
        @if ($step === 4)
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
                            @if ($showClientSignature)
                                <span
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                    ✓ Firmado
                                </span>
                            @endif
                        </div>

                        @if ($showClientSignature && $client_signature_data)
                            <div class="bg-white rounded-lg border border-gray-200 p-3 text-center">
                                <img src="{{ $client_signature_data }}" alt="Firma del Cliente"
                                    class="max-h-20 mx-auto" />
                                <button
                                    @click="$wire.call('resetClientSignature')"
                                    class="text-xs text-red-600 hover:text-red-700 mt-2">Volver a firmar</button>
                            </div>
                        @else
                            {{-- Canvas para firma --}}
                            <div x-data="{ canvas: null, ctx: null, drawing: false, sigData: null }" x-init="Alpine.nextTick(() => {
                            canvas = $refs.canvas;
                            if (!canvas) return;
                            ctx = canvas.getContext('2d');
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
                        })" class="space-y-2">
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

                            {{-- Botón simple para generar enlace (el detalle va abajo) --}}
                            <div class="mt-3 pt-3 border-t border-gray-200">
                                <button wire:click="generateSignatureLink"
                                    class="text-xs text-indigo-600 hover:text-indigo-700 font-medium flex items-center gap-1">
                                    <span class="material-symbols-outlined text-sm">link</span>
                                    Generar enlace de firma para el cliente
                                </button>
                                @if ($signature_link && !$showClientSignature)
                                    <p class="text-[10px] text-green-600 mt-1">✓ Enlace generado. Revisá la sección de
                                        abajo.</p>
                                @endif
                            </div>
                        @endif
                    </div>

                    {{-- Firma Agente de Ventas --}}
                    <div class="bg-gray-50 rounded-xl border border-gray-200 p-5">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="material-symbols-outlined text-indigo-600">badge</span>
                            <p class="font-semibold text-gray-800">Tu Firma</p>
                            @if ($showSalesRepSignature)
                                <span
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                    ✓ Firmado
                                </span>
                            @endif
                        </div>

                        @if ($showSalesRepSignature && $sales_rep_signature_data)
                            <div class="bg-white rounded-lg border border-gray-200 p-3 text-center">
                                <img src="{{ $sales_rep_signature_data }}" alt="Firma del Agente"
                                    class="max-h-20 mx-auto" />
                                <button
                                    @click="$wire.call('resetSalesRepSignature')"
                                    class="text-xs text-red-600 hover:text-red-700 mt-2">Volver a firmar</button>
                            </div>
                        @else
                            {{-- Canvas para firma del agente --}}
                            <div x-data="{ canvas2: null, ctx2: null, drawing2: false, sigData2: null }" x-init="Alpine.nextTick(() => {
                            canvas2 = $refs.canvas2;
                            if (!canvas2) return;
                            ctx2 = canvas2.getContext('2d');
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
                        })" class="space-y-2">
                                <canvas x-ref="canvas2"
                                    class="w-full h-[120px] bg-white border-2 border-dashed border-gray-300 rounded-lg cursor-crosshair"></canvas>
                                <div class="flex gap-2">
                                    <button type="button"
                                        @click="ctx2.clearRect(0, 0, canvas2.width, canvas2.height)"
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

                {{-- Enlace público de firma (como GPS y Documentos) --}}
                <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="material-symbols-outlined text-indigo-600 text-sm">edit_note</span>
                        <span class="text-xs font-semibold text-indigo-800 uppercase tracking-wide">Firma del
                            cliente</span>
                    </div>
                    <p class="text-xs text-indigo-700 mb-3">
                        Enviale un enlace al cliente para que firme digitalmente desde su celular.
                    </p>

                    @if ($signature_link)
                        <div class="bg-white rounded-lg border border-indigo-200 p-3 space-y-2">
                            <div class="flex items-center gap-2">
                                <input type="text" value="{{ $signature_link }}" readonly
                                    class="flex-1 text-xs px-2 py-1.5 border border-gray-200 rounded bg-gray-50 font-mono"
                                    onclick="this.select();" />
                                <button type="button"
                                    onclick="copyToClipboard('{{ $signature_link }}', this)"
                                    class="text-xs px-2 py-1.5 rounded bg-indigo-600 text-white hover:bg-indigo-700 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-sm">content_copy</span>
                                    Copiar
                                </button>
                            </div>
                            <div class="flex items-center justify-between">
                                <p class="text-[10px] text-indigo-500">Compartí este enlace con el cliente por WhatsApp
                                </p>
                                <button wire:click="sendSignatureViaWhatsApp"
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-green-100 text-green-700 hover:bg-green-200 transition-colors"
                                    title="Enviar por WhatsApp">
                                    <span class="material-symbols-outlined text-xs">chat</span>
                                    WhatsApp
                                </button>
                            </div>
                            <div class="flex gap-2 pt-1">
                                <button wire:click="refreshClientSignature"
                                    class="text-xs px-3 py-1.5 rounded-lg bg-green-100 text-green-700 hover:bg-green-200 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-sm">refresh</span>
                                    Actualizar firma
                                </button>
                                <button wire:click="$set('signature_link', null)"
                                    class="text-xs px-3 py-1.5 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200">
                                    Cerrar
                                </button>
                            </div>
                            @if ($showClientSignature && $client_signature_data)
                                <div class="mt-2 pt-2 border-t border-indigo-100 text-center">
                                    <p class="text-xs text-green-700 mb-1">✓ Firma recibida</p>
                                    <img src="{{ $client_signature_data }}" class="max-h-12 mx-auto" />
                                </div>
                            @endif
                        </div>
                    @else
                        <button wire:click="generateSignatureLink"
                            class="px-3 py-2 bg-indigo-600 text-white text-xs font-medium rounded-lg hover:bg-indigo-700 transition-colors flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-sm">share</span>
                            Generar enlace para el cliente
                        </button>
                    @endif
                </div>

                {{-- Términos (modal) --}}
                <div x-data="{ showTerms: false }">
                    <button @click="showTerms = true"
                        class="w-full text-left px-4 py-3 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 transition-colors flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-amber-600 text-sm">gavel</span>
                            <span class="text-sm font-medium text-gray-700">Ver Términos y Condiciones del
                                contrato</span>
                        </div>
                        <span class="material-symbols-outlined text-gray-400">open_in_new</span>
                    </button>

                    {{-- Modal --}}
                    <div x-show="showTerms"
                        class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" x-cloak>
                        <div class="bg-white rounded-xl max-w-2xl w-full max-h-[85vh] flex flex-col shadow-xl"
                            @click.stop>
                            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 shrink-0">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-amber-600">gavel</span>
                                    <h3 class="text-lg font-bold text-gray-900">Términos y Condiciones</h3>
                                </div>
                                <button @click="showTerms = false" class="text-gray-400 hover:text-gray-600">
                                    <span class="material-symbols-outlined">close</span>
                                </button>
                            </div>
                            <div class="px-6 py-4 overflow-y-auto text-sm text-gray-700 leading-relaxed space-y-3">
                                {!! $contract_terms !!}
                            </div>
                            <div class="px-6 py-4 border-t border-gray-100 shrink-0 flex justify-end">
                                <button @click="showTerms = false"
                                    class="px-4 py-2 text-xs font-medium rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition-colors">
                                    Cerrar
                                </button>
                            </div>
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 mt-2">
                        Al firmar, tanto el cliente como el agente de ventas aceptan los términos y condiciones del
                        contrato.
                    </p>
                </div>

                <div class="flex justify-between pt-4 border-t border-gray-100">
                    <x-ui.button variant="secondary" icon="arrow_back" wire:click="previousStep">
                        Atrás
                    </x-ui.button>
                    <x-ui.button variant="success" icon="save" wire:click="createContract"
                        wire:loading.attr="disabled">
                        <span wire:loading.remove>Generar Contrato y PDF</span>
                        <span wire:loading>Generando...</span>
                    </x-ui.button>
                </div>
            </div>
        @endif

        {{-- Step 5: Vista Previa PDF - Finalización --}}
        @if ($step === 5)
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
                            <p class="font-medium text-gray-800">
                                {{ $availablePlans->firstWhere('id', $plan_id)?->name ?? '—' }}</p>
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

                {{-- OT de instalación creada --}}
                @if ($createdWorkOrderCode)
                    <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-4">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0">
                                <span class="material-symbols-outlined text-indigo-600">assignment</span>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-indigo-900">OT de instalación creada</h4>
                                <p class="text-xs text-indigo-600">Un supervisor la asignará a un técnico</p>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center justify-between gap-2 bg-white rounded-lg border border-indigo-100 px-4 py-2.5">
                            <span class="text-sm font-bold text-indigo-800 font-mono">{{ $createdWorkOrderCode }}</span>
                            <a href="{{ route('work-orders.index') }}"
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium bg-indigo-600 text-white hover:bg-indigo-700 transition-colors">
                                <span class="material-symbols-outlined text-sm">assignment</span>
                                Ver órdenes de trabajo
                            </a>
                        </div>
                    </div>
                @endif

                {{-- Acciones --}}
                <div class="flex flex-col sm:flex-row gap-3 justify-center pt-4 border-t border-gray-100">
                    <x-ui.button variant="primary" icon="download" wire:click="downloadPdf">
                        Descargar PDF
                    </x-ui.button>
                </div>
            </div>
        @endif
    </x-ui.card>

    {{-- Modal slider para preview de imágenes --}}
    @php $previewId = 'preview-modal-' . ($contract_id ?? $ticket_id ?? 'new'); @endphp
    <div id="{{ $previewId }}" class="fixed inset-0 z-50 bg-black/90 hidden items-center justify-center"
        style="display:none;">
        <button onclick="closePreview('{{ $contract_id ?? ($ticket_id ?? 'new') }}')"
            class="absolute top-4 right-4 text-white/70 hover:text-white z-10">
            <span class="material-symbols-outlined text-3xl">close</span>
        </button>
        <img id="preview-image-{{ $contract_id ?? ($ticket_id ?? 'new') }}"
            class="max-w-[95vw] max-h-[95vh] object-contain" />
    </div>
</div>

@push('scripts')
    <script>
        function openPreview(src, id) {
            const modal = document.getElementById('preview-modal-' + id);
            const img = document.getElementById('preview-image-' + id);
            if (!modal || !img) return;
            img.src = src;
            modal.style.display = 'flex';
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closePreview(id) {
            const modal = document.getElementById('preview-modal-' + id);
            if (!modal) return;
            modal.style.display = 'none';
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }

        function openSignaturePreview() {
            const modal = document.getElementById('signature-preview-modal');
            if (!modal) return;
            modal.style.display = 'flex';
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeSignaturePreview() {
            const modal = document.getElementById('signature-preview-modal');
            if (!modal) return;
            modal.style.display = 'none';
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }

        function copyToClipboard(text, btn) {
            const fallback = () => {
                const ta = document.createElement('textarea');
                ta.value = text;
                ta.style.position = 'fixed';
                ta.style.opacity = '0';
                document.body.appendChild(ta);
                ta.select();
                try {
                    document.execCommand('copy');
                } catch (e) {}
                document.body.removeChild(ta);
                showCopiedFeedback(btn);
            };

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(() => {
                    showCopiedFeedback(btn);
                }).catch(() => {
                    fallback();
                });
            } else {
                fallback();
            }
        }

        function showCopiedFeedback(btn) {
            if (!btn) return;
            const original = btn.innerHTML;
            btn.innerHTML = '<span class="material-symbols-outlined text-sm">check</span> Copiado';
            btn.classList.add('!bg-green-600');
            setTimeout(() => {
                btn.innerHTML = original;
                btn.classList.remove('!bg-green-600');
            }, 2000);
        }

        document.addEventListener('keydown', (e) => {
            const previewId = '{{ $contract_id ?? ($ticket_id ?? 'new') }}';
            if (e.key === 'Escape') {
                closePreview(previewId);
                closeSignaturePreview();
            }
        });

        document.addEventListener('livewire:init', () => {
            Livewire.on('open-whatsapp', ({
                url
            }) => {
                window.open(url, '_blank');
            });
        });
    </script>
@endpush

