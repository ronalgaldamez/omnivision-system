<div class="max-w-3xl mx-auto">
    <x-ui.card icon="confirmation_number" title="{{ $ticketId ? 'Editar Ticket' : 'Nuevo Ticket' }}" subtitle="{{ $ticketId ? 'Modificar solicitud de servicio' : 'Generar una nueva solicitud de servicio' }}">
        <x-slot:headerActions>
            @if ($ticketOpened)
                <div class="flex items-center gap-2 bg-green-50 border border-green-200 rounded-lg px-3 py-1.5">
                    <span class="material-symbols-outlined text-green-600 text-lg">timer</span>
                    <span class="text-sm font-mono font-medium text-green-800" wire:poll.1s="updateElapsedSeconds">
                        {{ gmdate('H:i:s', $elapsedSeconds) }}
                    </span>
                </div>
            @endif
            @if ($isDraft)
                <x-ui.badge variant="warning">Borrador</x-ui.badge>
            @endif
            <a href="{{ route('tickets.index') }}"
                class="inline-flex items-center gap-1.5 text-sm text-gray-700 hover:text-gray-900 font-medium transition">
                <span class="material-symbols-outlined text-base">arrow_back</span>
                Volver al listado
            </a>
        </x-slot:headerActions>

        {{-- Botón Abrir Ticket --}}
        @if (!$ticketId && !$ticketOpened)
            <div class="mb-6 flex justify-center">
                <x-ui.button type="button" variant="success" icon="play_arrow" wire:click="openTicket" size="lg">
                    Abrir Ticket
                </x-ui.button>
            </div>
        @endif

        <form wire:submit.prevent="promptSave" class="space-y-6">
            {{-- Cliente --}}
            <div class="pb-5 border-b border-gray-100">
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2 flex items-center gap-2">
                    <span class="material-symbols-outlined text-gray-500 text-sm">person</span>
                    Cliente <span class="text-red-500">*</span>
                </label>
                <div class="flex gap-2">
                    <div class="relative flex-1">
                        <x-ui.input type="text" wire:model.live.debounce.300ms="clientSearch"
                            placeholder="Buscar por nombre o teléfono..." icon="search"
                            :disabled="!$editingEnabled" />
                        @if (count($clientSearchResults) > 0)
                            <ul class="absolute z-10 mt-1 w-full bg-white rounded-lg border border-gray-200 shadow-lg max-h-56 overflow-auto divide-y divide-gray-100">
                                @foreach ($clientSearchResults as $client)
                                    <li wire:click="selectClient({{ $client->id }}, '{{ $client->name }}', '{{ $client->phone }}')"
                                        class="px-4 py-2.5 hover:bg-gray-50 cursor-pointer transition text-sm flex items-center justify-between">
                                        <span class="font-medium text-gray-800">{{ $client->name }}</span>
                                        <span class="text-xs text-gray-500">{{ $client->phone ?? 'Sin teléfono' }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                    @if ($editingEnabled)
                        <x-ui.button type="button" variant="success" icon="person_add" wire:click="openClientModal">
                            Nuevo
                        </x-ui.button>
                    @endif
                </div>
                @error('client_id')
                    <span class="text-xs text-red-600 mt-1.5 block font-medium">{{ $message }}</span>
                @enderror

                @if ($selectedClient)
                    <div class="mt-4 bg-gray-50 rounded-lg border border-gray-200 p-4">
                        <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wide flex items-center gap-2 mb-3">
                            <span class="material-symbols-outlined text-gray-500 text-sm">info</span>
                            Datos del cliente seleccionado
                        </h3>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                            <div class="flex items-start gap-2">
                                <span class="material-symbols-outlined text-gray-400 text-sm mt-0.5">person</span>
                                <div>
                                    <p class="text-xs text-gray-500">Nombre</p>
                                    <p class="text-gray-800 font-medium">{{ $selectedClient->name }}</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-2">
                                <span class="material-symbols-outlined text-gray-400 text-sm mt-0.5">call</span>
                                <div>
                                    <p class="text-xs text-gray-500">Teléfono</p>
                                    <p class="text-gray-800">{{ $selectedClient->phone ?? '—' }}</p>
                                </div>
                            </div>
                            @if ($selectedClient->document_type && $selectedClient->document_number)
                                <div class="flex items-start gap-2">
                                    <span class="material-symbols-outlined text-gray-400 text-sm mt-0.5">fingerprint</span>
                                    <div>
                                        <p class="text-xs text-gray-500">{{ strtoupper($selectedClient->document_type) }}</p>
                                        <p class="text-gray-800">{{ $selectedClient->document_number }}</p>
                                    </div>
                                </div>
                            @endif
                            @if ($selectedClient->email)
                                <div class="flex items-start gap-2">
                                    <span class="material-symbols-outlined text-gray-400 text-sm mt-0.5">mail</span>
                                    <div>
                                        <p class="text-xs text-gray-500">Correo</p>
                                        <p class="text-gray-800">{{ $selectedClient->email }}</p>
                                    </div>
                                </div>
                            @endif
                            @if ($selectedClient->address)
                                <div class="flex items-start gap-2 col-span-2">
                                    <span class="material-symbols-outlined text-gray-400 text-sm mt-0.5">location_on</span>
                                    <div>
                                        <p class="text-xs text-gray-500">Dirección</p>
                                        <p class="text-gray-800">{{ $selectedClient->address }}</p>
                                    </div>
                                </div>
                            @endif
                            @if ($selectedClient->installation_address)
                                <div class="flex items-start gap-2 col-span-2">
                                    <span class="material-symbols-outlined text-gray-400 text-sm mt-0.5">home_pin</span>
                                    <div>
                                        <p class="text-xs text-gray-500">Instalación</p>
                                        <p class="text-gray-800">{{ $selectedClient->installation_address }}</p>
                                    </div>
                                </div>
                            @endif
                            @if ($selectedClient->service)
                                <div class="flex items-start gap-2">
                                    <span class="material-symbols-outlined text-gray-400 text-sm mt-0.5">tv</span>
                                    <div>
                                        <p class="text-xs text-gray-500">Servicio</p>
                                        <p class="text-gray-800">{{ $selectedClient->service }}</p>
                                    </div>
                                </div>
                            @endif
                            @if ($selectedClient->nro_luz)
                                <div class="flex items-start gap-2">
                                    <span class="material-symbols-outlined text-gray-400 text-sm mt-0.5">bolt</span>
                                    <div>
                                        <p class="text-xs text-gray-500">N.° de luz</p>
                                        <p class="text-gray-800">{{ $selectedClient->nro_luz }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            {{-- Descripción --}}
            <x-ui.textarea wire:model="description" icon="edit_note" rows="3" placeholder="Describe el problema o servicio..."
                :disabled="!$editingEnabled" required />

            {{-- Tipo de servicio --}}
            <div class="pb-5 border-b border-gray-100">
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2 flex items-center gap-2">
                    <span class="material-symbols-outlined text-gray-500 text-sm">handyman</span>
                    Tipo de servicio <span class="text-red-500">*</span>
                </label>
                <x-ui.select wire:model.live="service_type_id" icon="build" :disabled="!$editingEnabled">
                    @foreach ($serviceTypes as $type)
                        <option value="{{ $type->id }}">{{ str_replace('_', ' ', $type->name) }}</option>
                    @endforeach
                </x-ui.select>
                @error('service_type_id')
                    <span class="text-xs text-red-600 mt-1.5 block font-medium">{{ $message }}</span>
                @enderror
            </div>

            {{-- Base de Conocimiento --}}
            @if (count($knowledgeArticles) > 0)
                <div x-data="{ openArticle: null, filter: 'all' }" wire:key="kb-{{ $service_type_id }}"
                    class="bg-gray-50 rounded-lg border border-gray-200 p-4 space-y-3">
                    <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wide flex items-center gap-2">
                        <span class="material-symbols-outlined text-gray-500 text-sm">menu_book</span>
                        Información Técnica
                    </h3>

                    @php $categories = $knowledgeArticles->pluck('category')->filter()->unique(); @endphp
                    @if ($categories->count() > 1)
                        <div class="flex flex-wrap gap-1">
                            <button type="button" @click="filter = 'all'"
                                :class="filter === 'all' ? 'bg-gray-800 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-100'"
                                class="px-2.5 py-1 rounded-full text-xs font-medium transition">Todas</button>
                            @foreach ($categories as $cat)
                                <button type="button" @click="filter = '{{ $cat }}'"
                                    :class="filter === '{{ $cat }}' ? 'bg-gray-800 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-100'"
                                    class="px-2.5 py-1 rounded-full text-xs font-medium transition">{{ $cat }}</button>
                            @endforeach
                        </div>
                    @endif

                    <div class="space-y-2 max-h-60 overflow-y-auto">
                        @foreach ($knowledgeArticles as $article)
                            <div x-show="filter === 'all' || filter === '{{ $article->category }}'"
                                class="bg-white rounded-lg border border-gray-200">
                                <button type="button"
                                    @click="openArticle = (openArticle === {{ $article->id }} ? null : {{ $article->id }})"
                                    class="w-full flex items-center justify-between px-3 py-2 text-left text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span>{{ $article->title }}</span>
                                        @if ($article->priority)
                                            <x-ui.badge :variant="match($article->priority) { 'P1' => 'danger', 'P2' => 'warning', 'P3' => 'info', default => 'neutral' }">
                                                {{ $article->priority }} - @php $priorityLabels = ['P1' => 'Crítico', 'P2' => 'Alta', 'P3' => 'Media', 'P4' => 'Baja']; @endphp
                                                {{ $priorityLabels[$article->priority] ?? $article->priority }}
                                            </x-ui.badge>
                                        @endif
                                    </div>
                                    <span class="material-symbols-outlined text-base transition-transform flex-shrink-0 ml-2"
                                        :class="openArticle === {{ $article->id }} ? 'rotate-180' : ''">expand_more</span>
                                </button>
                                <div x-show="openArticle === {{ $article->id }}" x-collapse
                                    class="px-3 pb-3 text-xs text-gray-600 whitespace-pre-line">
                                    {{ $article->content }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Switches: Crear OT / Requiere NOC --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pb-5 border-b border-gray-100">
                <div class="bg-gray-50 rounded-lg border border-gray-200 p-4">
                    <x-ui.toggle label="Crear OT" description="Generar orden de trabajo directamente desde el ticket."
                        wire:model.live="create_ot" on-color="amber" :disabled="!$editingEnabled" />
                    @if ($create_ot)
                        <x-ui.badge variant="warning" class="mt-2">OT: Sí</x-ui.badge>
                    @else
                        <x-ui.badge variant="neutral" class="mt-2">OT: No</x-ui.badge>
                    @endif
                </div>
                <div class="bg-gray-50 rounded-lg border border-gray-200 p-4">
                    <x-ui.toggle label="¿Requiere intervención del NOC?" description="Se enviará al panel NOC para su resolución."
                        wire:model.live="requires_noc" :disabled="!$editingEnabled" />
                    @if ($requires_noc)
                        <x-ui.badge variant="info" class="mt-2">NOC: Sí</x-ui.badge>
                    @else
                        <x-ui.badge variant="neutral" class="mt-2">NOC: No</x-ui.badge>
                    @endif
                </div>
            </div>

            {{-- Info: cuándo usar OT vs NOC --}}
            <x-ui.alert variant="warning" title="¿Cuándo usar cada opción?">
                <ul class="list-disc list-inside space-y-1">
                    <li><strong>Crear OT</strong> — El problema requiere visita técnica en campo. Se genera una orden de trabajo para que el supervisor asigne a un técnico.</li>
                    <li><strong>Requiere NOC</strong> — El problema puede resolverse de forma remota (configuración, señal, plataforma). El NOC lo evaluará y, si no puede resolverlo, generará una OT.</li>
                    <li><strong>Ninguno</strong> — El ticket se soluciona directamente desde la mesa de ayuda (L1), sin necesidad de escalar ni enviar técnico.</li>
                </ul>
            </x-ui.alert>

            {{-- Origen --}}
            <div class="pb-5 border-b border-gray-100">
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2 flex items-center gap-2">
                    <span class="material-symbols-outlined text-gray-500 text-sm">source</span>
                    Origen del ticket
                </label>
                <x-ui.select wire:model.live="origin" icon="source" :disabled="!$editingEnabled">
                    <option value="Facebook Messenger">Facebook Messenger</option>
                    <option value="SMS WhatsApp">SMS WhatsApp</option>
                    <option value="Llamada de WhatsApp">Llamada de WhatsApp</option>
                    <option value="Llamada Telefónica">Llamada Telefónica</option>
                    <option value="SMS">SMS</option>
                    <option value="Presencial">Presencial</option>
                    <option value="Otros">Otros</option>
                </x-ui.select>
            </div>

            {{-- Botones --}}
            <div class="flex justify-end gap-3 pt-2">
                <x-ui.button type="button" variant="secondary" wire:click="goBack">
                    {{ $ticketOpened ? 'Salir' : 'Cancelar' }}
                </x-ui.button>
                @if ($ticketOpened)
                    <x-ui.button type="button" variant="danger" icon="block" wire:click="confirmCancel">
                        Cancelar Ticket
                    </x-ui.button>
                    @if ($requires_noc)
                        <x-ui.button type="button" variant="primary" icon="send" wire:click="confirmGenerate">
                            Generar Ticket
                        </x-ui.button>
                    @elseif($create_ot)
                        <x-ui.button type="button" variant="warning" icon="engineering" wire:click="confirmSolve">
                            Crear OT y Finalizar
                        </x-ui.button>
                    @else
                        <x-ui.button type="button" variant="success" icon="check_circle" wire:click="confirmSolve">
                            Solucionar Ticket
                        </x-ui.button>
                    @endif
                @else
                    <x-ui.button type="submit" variant="primary" icon="save">
                        {{ $ticketId ? 'Actualizar Ticket' : 'Crear Ticket' }}
                    </x-ui.button>
                @endif
            </div>
        </form>
    </x-ui.card>

    {{-- Modal para crear cliente --}}
    @if ($showClientModal)
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center"
            x-data="{ open: true }" x-show="open" x-cloak>
            <div class="relative mx-auto p-5 w-full max-w-3xl max-h-[85vh] overflow-y-auto">
                <x-ui.card overflow="visible">
                    <x-slot:headerActions>
                        <button type="button" @click="$wire.closeClientModal()" class="text-gray-400 hover:text-gray-600 transition">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </x-slot:headerActions>
                    <livewire:clients.client-form key="client-form-modal" />
                </x-ui.card>
            </div>
        </div>
    @endif

    {{-- Confirmación: Nuevo cliente --}}
    @if ($confirmingNewClient)
        <div x-data="{ open: true }" x-show="open" x-cloak
            class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center"
            style="display: none;">
            <div class="relative mx-auto p-5 w-full max-w-md">
                <x-ui.card overflow="visible">
                    <div class="text-center">
                        <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-amber-100 mb-4">
                            <span class="material-symbols-outlined text-amber-600 text-2xl">warning</span>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">¿Registrar nuevo cliente?</h3>
                        <p class="text-sm text-gray-600 mt-2">
                            Al crear un nuevo cliente, los datos del ticket actual se reiniciarán y podrías perder la información que ya habías ingresado.
                        </p>
                    </div>
                    <x-slot:footer>
                        <x-ui.button type="button" variant="warning" wire:click="proceedToNewClient">Sí, continuar</x-ui.button>
                        <x-ui.button type="button" variant="secondary" @click="open = false" wire:click="cancelNewClient">Cancelar</x-ui.button>
                    </x-slot:footer>
                </x-ui.card>
            </div>
        </div>
    @endif

    {{-- Confirmación de guardado --}}
    @if ($confirmingSave)
        <div x-data="{ open: true }" x-show="open" x-cloak
            class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center"
            style="display: none;">
            <div class="relative mx-auto p-5 w-full max-w-md">
                <x-ui.card overflow="visible">
                    <div class="text-center">
                        <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-blue-100 mb-4">
                            <span class="material-symbols-outlined text-blue-600 text-2xl">help</span>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Confirmar acción</h3>
                        <p class="text-sm text-gray-600 mt-2">
                            ¿Estás seguro de guardar este ticket?
                            @if (!$ticketId && $create_ot)
                                <span class="block text-xs text-amber-600 mt-1">Se creará una orden de trabajo a partir del ticket.</span>
                            @elseif(!$ticketId && $requires_noc)
                                <span class="block text-xs text-blue-600 mt-1">El ticket será escalado al panel NOC.</span>
                            @elseif(!$ticketId)
                                <span class="block text-xs text-gray-500 mt-1">El ticket quedará registrado sin generar OT.</span>
                            @endif
                        </p>
                    </div>
                    <x-slot:footer>
                        <x-ui.button type="button" variant="primary" wire:click="executeSave">Sí, continuar</x-ui.button>
                        <x-ui.button type="button" variant="secondary" @click="open = false" wire:click="cancelSave">Cancelar</x-ui.button>
                    </x-slot:footer>
                </x-ui.card>
            </div>
        </div>
    @endif

    {{-- Confirmación para Solucionar Ticket --}}
    @if ($confirmingSolve)
        <div x-data="{ open: true }" x-show="open" x-cloak
            class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center"
            style="display: none;">
            <div class="relative mx-auto p-5 w-full max-w-md">
                <x-ui.card overflow="visible">
                    <div class="text-center">
                        <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-green-100 mb-4">
                            <span class="material-symbols-outlined text-green-600 text-2xl">check_circle</span>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">
                            {{ $create_ot ? '¿Crear OT?' : '¿Solucionar ticket?' }}
                        </h3>
                        <p class="text-sm text-gray-600 mt-2">
                            @if ($create_ot)
                                Se creará una orden de trabajo vinculada al ticket. El ticket quedará <strong>en seguimiento</strong> hasta que la OT se complete.
                            @else
                                Se guardarán todos los datos y se marcará el ticket como resuelto.
                            @endif
                            El cronómetro se detendrá.
                        </p>
                        @if ($elapsedSeconds > 0)
                            <p class="text-sm text-gray-500 mt-1">
                                Tiempo transcurrido: <strong>{{ gmdate('H:i:s', $elapsedSeconds) }}</strong>
                            </p>
                        @endif
                    </div>
                    <x-slot:footer>
                        <x-ui.button type="button" :variant="$create_ot ? 'warning' : 'success'" wire:click="executeSolve">
                            {{ $create_ot ? 'Sí, crear OT' : 'Sí, solucionar' }}
                        </x-ui.button>
                        <x-ui.button type="button" variant="secondary" @click="open = false" wire:click="cancelSolve">Cancelar</x-ui.button>
                    </x-slot:footer>
                </x-ui.card>
            </div>
        </div>
    @endif

    {{-- Confirmación para Generar Ticket (NOC) --}}
    @if ($confirmingGenerate)
        <div x-data="{ open: true }" x-show="open" x-cloak
            class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center"
            style="display: none;">
            <div class="relative mx-auto p-5 w-full max-w-md">
                <x-ui.card overflow="visible">
                    <div class="text-center">
                        <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-blue-100 mb-4">
                            <span class="material-symbols-outlined text-blue-600 text-2xl">send</span>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">¿Generar ticket para NOC?</h3>
                        <p class="text-sm text-gray-600 mt-2">El ticket se enviará al panel del NOC para su revisión y derivación.</p>
                    </div>
                    <x-slot:footer>
                        <x-ui.button type="button" variant="primary" wire:click="executeGenerate">Sí, generar</x-ui.button>
                        <x-ui.button type="button" variant="secondary" @click="open = false" wire:click="cancelGenerate">Cancelar</x-ui.button>
                    </x-slot:footer>
                </x-ui.card>
            </div>
        </div>
    @endif

    {{-- Modal de cancelación con motivo obligatorio --}}
    @if ($confirmingCancel)
        <div x-data="{ open: true }" x-show="open" x-cloak
            class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center"
            style="display: none;">
            <div class="relative mx-auto p-5 w-full max-w-md">
                <x-ui.card overflow="visible">
                    <div class="text-center">
                        <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-red-100 mb-4">
                            <span class="material-symbols-outlined text-red-600 text-2xl">block</span>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Cancelar Ticket</h3>
                        <p class="text-sm text-gray-600 mt-2">
                            Se detendrá el cronómetro y el ticket quedará como <strong>cancelado</strong>.
                        </p>
                        @if ($elapsedSeconds > 0)
                            <p class="text-sm text-gray-500 mt-1">
                                Tiempo transcurrido: <strong>{{ gmdate('H:i:s', $elapsedSeconds) }}</strong>
                            </p>
                        @endif
                        <div class="mt-4 text-left">
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                                Motivo de cancelación <span class="text-red-500">*</span>
                            </label>
                            <x-ui.textarea wire:model="cancelReason" rows="3" placeholder="Ej: El cliente ya no quiere el servicio..." />
                            @error('cancelReason')
                                <span class="text-xs text-red-600 mt-1.5 block font-medium">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <x-slot:footer>
                        <x-ui.button type="button" variant="danger" wire:click="executeCancel">Sí, cancelar ticket</x-ui.button>
                        <x-ui.button type="button" variant="secondary" @click="open = false" wire:click="cancelCancel">Volver</x-ui.button>
                    </x-slot:footer>
                </x-ui.card>
            </div>
        </div>
    @endif

    <div x-data="{ toast: null, toastType: null, toastMessage: '' }"
         x-on:show-toast.window="toast = true; toastType = $event.detail.type; toastMessage = $event.detail.message; setTimeout(() => toast = false, 3500)"
         x-show="toast" x-cloak class="fixed bottom-5 right-5 z-50 transition-all duration-300"
         x-transition:enter="transform ease-out duration-300" x-transition:enter-start="translate-y-2 opacity-0"
         x-transition:enter-end="translate-y-0 opacity-100" x-transition:leave="transform ease-in duration-200"
         x-transition:leave-start="translate-y-0 opacity-100" x-transition:leave-end="translate-y-2 opacity-0"
         style="display: none;">
        <div x-show="toastType === 'success'"
             class="bg-green-600 text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-3">
            <span class="material-symbols-outlined">check_circle</span> <span x-text="toastMessage" class="text-sm font-medium"></span>
        </div>
        <div x-show="toastType === 'error'"
             class="bg-red-600 text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-3">
            <span class="material-symbols-outlined">error</span> <span x-text="toastMessage" class="text-sm font-medium"></span>
        </div>
        <div x-show="toastType === 'info'"
             class="bg-blue-600 text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-3">
            <span class="material-symbols-outlined">info</span> <span x-text="toastMessage" class="text-sm font-medium"></span>
        </div>
    </div>

    <style>[x-cloak] { display: none !important; }</style>
</div>