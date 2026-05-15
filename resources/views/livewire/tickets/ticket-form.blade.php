<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                        <span class="material-symbols-outlined text-gray-500">confirmation_number</span>
                        Nuevo Ticket
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">Generar una nueva solicitud de servicio</p>
                </div>
                <a href="{{ route('tickets.index') }}"
                    class="inline-flex items-center gap-1.5 text-sm text-blue-600 hover:text-blue-800 transition">
                    <span class="material-symbols-outlined text-base">arrow_back</span>
                    Volver al listado
                </a>
            </div>
        </div>

        <div class="p-6">
            <form wire:submit.prevent="promptSave" class="space-y-6">
                <!-- Cliente -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-gray-400 text-base">person</span>
                        Cliente *
                    </label>
                    <div class="flex gap-2">
                        <div class="relative flex-1">
                            <input type="text" wire:model.live.debounce.300ms="clientSearch"
                                placeholder="Buscar por nombre o teléfono..."
                                class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                            <span
                                class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                            @if(count($clientSearchResults) > 0)
                                <ul
                                    class="absolute z-10 mt-1 w-full bg-white rounded-lg border border-gray-200 shadow-lg max-h-56 overflow-auto divide-y divide-gray-100">
                                    @foreach($clientSearchResults as $client)
                                        <li wire:click="selectClient({{ $client->id }}, '{{ $client->name }}')"
                                            class="px-4 py-2.5 hover:bg-blue-50 cursor-pointer transition text-sm flex items-center justify-between">
                                            <span class="font-medium text-gray-800">{{ $client->name }}</span>
                                            <span class="text-xs text-gray-500">{{ $client->phone ?? 'Sin teléfono' }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                        <button type="button" wire:click="openClientModal"
                            class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-base">person_add</span>
                            Nuevo
                        </button>
                    </div>
                    @error('client_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Descripción -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-gray-400 text-base">description</span>
                        Descripción *
                    </label>
                    <div class="relative">
                        <textarea wire:model="description" rows="3"
                            class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm resize-none"
                            placeholder="Describe el problema o servicio..."></textarea>
                        <span
                            class="material-symbols-outlined absolute left-3 top-2.5 text-gray-400 text-lg">edit_note</span>
                    </div>
                    @error('description') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Tipo de servicio -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-gray-400 text-base">handyman</span>
                        Tipo de servicio *
                    </label>
                    <div class="relative">
                        <select wire:change="selectServiceType($event.target.value)"
                            class="w-full pl-9 pr-8 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm appearance-none">
                            <option value="">Seleccione</option>
                            @foreach($serviceTypes as $type)
                                <option value="{{ $type->id }}">{{ str_replace('_', ' ', $type->name) }}</option>
                            @endforeach
                        </select>
                        <span
                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">build</span>
                        <span
                            class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
                    </div>
                    @error('service_type_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Sección de Base de Conocimiento -->
                @if(count($knowledgeArticles) > 0)
                    <div x-data="{ openArticle: null, filter: 'all' }" wire:key="kb-{{ $service_type_id }}" class="bg-blue-50/50 rounded-xl border border-blue-200 p-4 space-y-3">
                        <h3 class="text-sm font-semibold text-blue-800 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-blue-600 text-base">menu_book</span>
                            Información Técnica
                        </h3>
                        
                        @php
                            $categories = $knowledgeArticles->pluck('category')->filter()->unique();
                        @endphp
                        @if($categories->count() > 1)
                            <div class="flex flex-wrap gap-1">
                                <button type="button" @click="filter = 'all'"
                                    :class="filter === 'all' ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 border border-gray-300'"
                                    class="px-2 py-0.5 rounded-full text-xs font-medium transition">
                                    Todas
                                </button>
                                @foreach($categories as $cat)
                                    <button type="button" @click="filter = '{{ $cat }}'"
                                        :class="filter === '{{ $cat }}' ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 border border-gray-300'"
                                        class="px-2 py-0.5 rounded-full text-xs font-medium transition">
                                        {{ $cat }}
                                    </button>
                                @endforeach
                            </div>
                        @endif

                        <div class="space-y-2 max-h-60 overflow-y-auto">
                            @foreach($knowledgeArticles as $article)
                                <div x-show="filter === 'all' || filter === '{{ $article->category }}'"
                                     class="bg-white rounded-lg border border-gray-200">
                                    <button type="button" @click="openArticle = (openArticle === {{ $article->id }} ? null : {{ $article->id }})"
                                        class="w-full flex items-center justify-between px-3 py-2 text-left text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                                        <div class="flex items-center gap-2">
                                            <span>{{ $article->title }}</span>
                                            @if($article->priority)
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                                    @switch($article->priority)
                                                        @case('P1') bg-red-100 text-red-700 @break
                                                        @case('P2') bg-orange-100 text-orange-700 @break
                                                        @case('P3') bg-blue-100 text-blue-700 @break
                                                        @case('P4') bg-gray-100 text-gray-600 @break
                                                    @endswitch">
                                                    {{ $article->priority }} - 
                                                    @php
                                                        $priorityLabels = ['P1' => 'Crítico', 'P2' => 'Alta', 'P3' => 'Media', 'P4' => 'Baja'];
                                                    @endphp
                                                    {{ $priorityLabels[$article->priority] ?? $article->priority }}
                                                </span>
                                            @endif
                                        </div>
                                        <span class="material-symbols-outlined text-base transition-transform"
                                            :class="openArticle === {{ $article->id }} ? 'rotate-180' : ''">
                                            expand_more
                                        </span>
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

                <!-- Switch Requiere NOC (manual, con badge) -->
                <div class="bg-gray-50/80 rounded-xl border border-gray-200 p-4">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-700">¿Requiere intervención del NOC?</label>
                            <p class="text-xs text-gray-500 mt-0.5">Si se activa, el ticket se enviará al panel NOC y no se creará OT automáticamente.</p>
                        </div>
                        <div class="flex items-center gap-3">
                            @if($requires_noc)
                                <span class="px-2.5 py-0.5 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">
                                    NOC: Sí
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 bg-gray-100 text-gray-600 rounded-full text-xs font-medium">
                                    NOC: No
                                </span>
                            @endif
                            <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                                <input type="checkbox" wire:model="requires_noc" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Origen -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-gray-400 text-base">source</span>
                        Origen del ticket
                    </label>
                    <div class="relative">
                        <select wire:model="origin"
                            class="w-full pl-9 pr-8 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm appearance-none">
                            <option value="">Seleccione</option>
                            <option value="Facebook Messenger">Facebook Messenger</option>
                            <option value="SMS WhatsApp">SMS WhatsApp</option>
                            <option value="Llamada de WhatsApp">Llamada de WhatsApp</option>
                            <option value="Llamada Telefónica">Llamada Telefónica</option>
                            <option value="SMS">SMS</option>
                            <option value="Presencial">Presencial</option>
                            <option value="Otros">Otros</option>
                        </select>
                        <span
                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">source</span>
                        <span
                            class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
                    </div>
                </div>

                <!-- Botones -->
                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('tickets.index') }}"
                        class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-300 transition shadow-sm">
                        Cancelar
                    </a>
                    <button type="submit"
                        class="px-5 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 transition inline-flex items-center gap-2">
                        <span class="material-symbols-outlined text-base">save</span>
                        Crear Ticket
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para crear cliente -->
    @if($showClientModal)
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center"
            x-data="{ open: true }" x-show="open" x-cloak>
            <div class="relative mx-auto p-5 w-full max-w-md">
                <div class="bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                        <h3 class="text-lg font-semibold flex items-center gap-2">
                            <span class="material-symbols-outlined text-gray-500">person_add</span>
                            Nuevo Cliente
                        </h3>
                        <button type="button" @click="$wire.closeClientModal()" class="text-gray-400 hover:text-gray-600 transition">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>
                    <div class="p-5">
                        <livewire:clients.client-form />
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('livewire:init', () => {
                Livewire.on('clientCreated', (clientId, clientName) => {
                    @this.call('selectClient', clientId, clientName);
                    @this.call('closeClientModal');
                });
            });
        </script>
    @endif

    <!-- Modal de confirmación de guardado -->
    @if($confirmingSave)
        <div x-data="{ open: true }" x-show="open" x-cloak
            class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center"
            style="display: none;">
            <div class="relative mx-auto p-5 w-full max-w-md">
                <div class="bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden">
                    <div class="p-6 text-center">
                        <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-blue-100 mb-4">
                            <span class="material-symbols-outlined text-blue-600 text-2xl">help</span>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Confirmar acción</h3>
                        <p class="text-sm text-gray-600 mt-2">
                            ¿Estás seguro de guardar este ticket?
                            @if(!$ticketId && !$requires_noc)
                                <span class="block text-xs text-gray-500 mt-1">Se creará automáticamente la orden de trabajo correspondiente.</span>
                            @endif
                        </p>
                    </div>
                    <div class="bg-gray-50 px-6 py-4 flex flex-col gap-3 sm:flex-row-reverse">
                        <button type="button" wire:click="executeSave"
                            class="w-full sm:w-auto px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-blue-700 transition">
                            Sí, continuar
                        </button>
                        <button type="button" @click="open = false" wire:click="cancelSave"
                            class="w-full sm:w-auto px-5 py-2.5 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Toast -->
    <div x-data="{ toast: null, toastType: null, toastMessage: '' }"
        x-on:show-toast.window="toast = true; toastType = $event.detail.type; toastMessage = $event.detail.message; setTimeout(() => toast = false, 5000)"
        x-show="toast" x-cloak class="fixed bottom-5 right-5 z-50 transition-all duration-300"
        x-transition:enter="transform ease-out duration-300" x-transition:enter-start="translate-y-2 opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100" x-transition:leave="transform ease-in duration-200"
        x-transition:leave-start="translate-y-0 opacity-100" x-transition:leave-end="translate-y-2 opacity-0"
        style="display: none;">
        <div x-show="toastType === 'success'"
            class="bg-green-600 text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-3">
            <span class="material-symbols-outlined">check_circle</span>
            <span x-text="toastMessage" class="text-sm font-medium"></span>
        </div>
        <div x-show="toastType === 'error'"
            class="bg-red-600 text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-3">
            <span class="material-symbols-outlined">error</span>
            <span x-text="toastMessage" class="text-sm font-medium"></span>
        </div>
    </div>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</div>