<div x-data="{ activeTab: window.location.hash.replace('#', '') || 'general' }" class="max-w-3xl mx-auto"
     x-init="if (!window.location.hash) window.location.hash = '#' + activeTab; window.addEventListener('hashchange', () => activeTab = window.location.hash.replace('#', '') || 'general')">
    <x-ui.card icon="settings" title="Configuraciones del Sistema" subtitle="Ajustes generales, tipos de servicio, base de conocimiento y control de módulos">
        {{-- Pestañas Alpine --}}
        <div class="border-b border-gray-200">
            <nav class="flex gap-0 -mb-px px-6">
                <button @click="activeTab = 'general'; window.location.hash = '#general'"
                    :class="activeTab === 'general' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="px-4 py-3 text-sm font-medium border-b-2 transition">
                    <span class="material-symbols-outlined text-base align-middle mr-1">tune</span>
                    Ajustes Generales
                </button>
                <button @click="activeTab = 'services'; window.location.hash = '#services'"
                    :class="activeTab === 'services' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="px-4 py-3 text-sm font-medium border-b-2 transition">
                    <span class="material-symbols-outlined text-base align-middle mr-1">design_services</span>
                    Tipos de Servicio
                </button>
                <button @click="activeTab = 'knowledge'; window.location.hash = '#knowledge'"
                    :class="activeTab === 'knowledge' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="px-4 py-3 text-sm font-medium border-b-2 transition">
                    <span class="material-symbols-outlined text-base align-middle mr-1">menu_book</span>
                    Base de Conocimiento
                </button>
                <button @click="activeTab = 'modules'; window.location.hash = '#modules'"
                    :class="activeTab === 'modules' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="px-4 py-3 text-sm font-medium border-b-2 transition">
                    <span class="material-symbols-outlined text-base align-middle mr-1">extension</span>
                    Módulos
                </button>
            </nav>
        </div>

        <div class="p-6 space-y-6">
            {{-- General --}}
            <div x-show="activeTab === 'general'" x-cloak>
                <div class="mb-6">
                    <div class="flex items-center gap-2 px-1 mb-3">
                        <span class="material-symbols-outlined text-gray-500 text-lg">handyman</span>
                        <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Técnicos / Requisiciones</h3>
                    </div>
                    <div class="bg-gray-50/80 rounded-xl border border-gray-200 p-4 flex items-center justify-between gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Órdenes de Trabajo obligatorias</label>
                            <p class="text-xs text-gray-500 mt-0.5">Si está activo, los técnicos no podrán crear requisiciones sin una OT asignada.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                            <input type="checkbox" wire:model.live="otRequired" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                </div>

                <div class="mb-6">
                    <div class="flex items-center gap-2 px-1 mb-3">
                        <span class="material-symbols-outlined text-gray-500 text-lg">settings_ethernet</span>
                        <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Dispositivos / MAC</h3>
                    </div>
                    <div class="bg-gray-50/80 rounded-xl border border-gray-200 p-4 flex items-center justify-between gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Factura obligatoria para registro de dispositivos</label>
                            <p class="text-xs text-gray-500 mt-0.5">Si está activo, al registrar dispositivos será obligatorio seleccionar una factura de compra asociada.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                            <input type="checkbox" wire:model.live="invoiceRequiredForDevices" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                </div>

                <div class="mb-6">
                    <div class="flex items-center gap-2 px-1 mb-3">
                        <span class="material-symbols-outlined text-gray-500 text-lg">confirmation_number</span>
                        <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Tickets</h3>
                    </div>
                    <div class="bg-gray-50/80 rounded-xl border border-gray-200 p-4 flex items-center justify-between gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Documentos de identidad del cliente</label>
                            <p class="text-xs text-gray-500 mt-0.5">Si está activo, al crear un ticket se mostrará el campo de tipo y número de documento de identidad.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                            <input type="checkbox" wire:model.live="documentTypesEnabled" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                </div>

                <div class="mb-6">
                    <div class="flex items-center justify-between gap-2 px-1 mb-3">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-gray-500 text-lg">badge</span>
                            <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Tipos de Documento</h3>
                        </div>
                        <x-ui.button variant="primary" icon="add_circle" wire:click="openDocTypeModal" class="text-xs px-3 py-1.5">Nuevo tipo</x-ui.button>
                    </div>
                    <p class="text-xs text-gray-500 mb-3">Se mostrarán como opciones en el formulario de cliente.</p>

                    <div class="space-y-2">
                        @forelse($documentTypeList as $index => $dt)
                            <div class="bg-gray-50/80 rounded-xl border border-gray-200 p-3 flex items-center justify-between gap-4">
                            <span class="text-sm font-medium text-gray-700">{{ $dt }}</span>
                            <div class="flex items-center gap-1">
                                <button wire:click="editDocType({{ $index }})" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Editar">
                                    <span class="material-symbols-outlined text-lg">edit</span>
                                </button>
                                <button wire:click="deleteDocType({{ $index }})" onclick="return confirm('¿Eliminar este tipo de documento?')" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition" title="Eliminar">
                                    <span class="material-symbols-outlined text-lg">delete</span>
                                </button>
                            </div>
                        </div>
                        @empty
                            <div class="text-center py-6 bg-gray-50/50 rounded-xl border border-dashed border-gray-300">
                                <p class="text-gray-500 text-sm">No hay tipos de documento definidos.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="mb-6">
                    <div class="flex items-center gap-2 px-1 mb-3">
                        <span class="material-symbols-outlined text-gray-500 text-lg">settings_overscan</span>
                        <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">NOC</h3>
                    </div>
                    <div class="bg-gray-50/80 rounded-xl border border-gray-200 p-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-gray-400 text-base">timer</span>
                            Intervalo de polling (segundos)
                        </label>
                        <p class="text-xs text-gray-500 mb-3">Cada cuántos segundos se actualiza el contador de notificaciones del panel NOC (mín. 5, máx. 300).</p>
                        <div class="relative w-32">
                            <input type="number" wire:model.live="nocPollingInterval"
                                class="w-full pl-3 pr-8 py-2 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm"
                                min="5" max="300" step="5">
                            <span class="material-symbols-outlined absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 text-base">schedule</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Servicios --}}
            <div x-show="activeTab === 'services'" x-cloak>
                <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                    <h2 class="text-md font-semibold text-gray-800 flex items-center gap-2">
                        <span class="material-symbols-outlined text-gray-500">design_services</span>
                        Tipos de Servicio
                    </h2>
                    <x-ui.button variant="primary" icon="add_circle" wire:click="openServiceModal" class="text-xs px-3 py-1.5">Nuevo tipo</x-ui.button>
                </div>
                <p class="text-xs text-gray-500 mb-4">Administrá los tipos de servicio y definí sus requerimientos (NOC, OT, Contrato).</p>

                <div class="space-y-3">
                    @forelse($serviceTypes as $type)
                        <div class="bg-gray-50/80 rounded-xl border border-gray-200 p-4 flex items-center justify-between gap-4">
                            <span class="text-sm font-medium text-gray-700 capitalize">{{ str_replace('_', ' ', $type->name) }}</span>
                            <div class="flex flex-col gap-2">
                                <div class="grid grid-cols-2 gap-x-6 gap-y-1">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" wire:model.live="serviceRequiresNoc.{{ $type->id }}" class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                        <span class="ml-2 text-xs text-gray-500">Requiere NOC</span>
                                    </label>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" wire:model.live="serviceRequiresOt.{{ $type->id }}" class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                        <span class="ml-2 text-xs text-gray-500">Requiere OT</span>
                                    </label>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" wire:model.live="serviceRequiresContract.{{ $type->id }}" class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                        <span class="ml-2 text-xs text-gray-500">Requiere Contrato</span>
                                    </label>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" wire:model.live="serviceRequiresPotential.{{ $type->id }}" class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                        <span class="ml-2 text-xs text-gray-500">Cliente Potencial</span>
                                    </label>
                                </div>
                                <div class="flex items-center gap-1 pl-0.5">
                                    <button wire:click="editService({{ $type->id }})" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Editar">
                                        <span class="material-symbols-outlined text-lg">edit</span>
                                    </button>
                                    <button wire:click="deleteService({{ $type->id }})" onclick="return confirm('¿Eliminar este tipo de servicio?')" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition" title="Eliminar">
                                        <span class="material-symbols-outlined text-lg">delete</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 bg-gray-50/50 rounded-xl border border-dashed border-gray-300">
                            <p class="text-gray-500 text-sm">No hay tipos de servicio definidos.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Base de Conocimiento --}}
            <div x-show="activeTab === 'knowledge'" x-cloak>
                <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                    <h2 class="text-md font-semibold text-gray-800 flex items-center gap-2">
                        <span class="material-symbols-outlined text-gray-500">menu_book</span>
                        Base de Conocimiento
                    </h2>
                    <x-ui.button variant="primary" icon="add_circle" wire:click="openKbModal" class="text-xs px-3 py-1.5">Nuevo Artículo</x-ui.button>
                </div>
                <p class="text-xs text-gray-500 mb-4">Artículos técnicos vinculados a tipos de servicio.</p>

                <div class="space-y-3">
                    @forelse($articles as $article)
                        <div class="bg-gray-50/80 rounded-xl border border-gray-200 p-4">
                            <div class="flex items-start justify-between gap-4 mb-2">
                                <div class="flex-1">
                                    <h3 class="text-sm font-semibold text-gray-800">{{ $article->title }}</h3>
                                    @if($article->category)
                                        <span class="text-xs text-gray-500">{{ $article->category }}</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2">
                                    <button wire:click="editArticle({{ $article->id }})" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Editar">
                                        <span class="material-symbols-outlined text-lg">edit</span>
                                    </button>
                                    <button wire:click="deleteArticle({{ $article->id }})" onclick="return confirm('¿Eliminar este artículo?')" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition" title="Eliminar">
                                        <span class="material-symbols-outlined text-lg">delete</span>
                                    </button>
                                </div>
                            </div>
                            <p class="text-xs text-gray-600 line-clamp-2">{{ Str::limit($article->content, 150) }}</p>
                            @if($article->serviceTypes->isNotEmpty())
                                <div class="flex flex-wrap gap-1 mt-2">
                                    @foreach($article->serviceTypes as $st)
                                        <x-ui.badge variant="info">{{ str_replace('_', ' ', $st->name) }}</x-ui.badge>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-8 bg-gray-50/50 rounded-xl border border-dashed border-gray-300">
                            <p class="text-gray-500 text-sm">No hay artículos en la base de conocimiento.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Módulos --}}
            <div x-show="activeTab === 'modules'" x-cloak>
                <h2 class="text-md font-semibold text-gray-800 flex items-center gap-2 mb-1">
                    <span class="material-symbols-outlined text-gray-500">extension</span>
                    Módulos del Sistema
                </h2>
                <p class="text-xs text-gray-500 mb-4">Activa o desactiva módulos completos. Los cambios se aplican inmediatamente.</p>

                <div class="space-y-3">
                    @foreach($modules as $module => $active)
                    <div class="bg-gray-50/80 rounded-xl border border-gray-200 p-4 flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-sm font-medium capitalize text-gray-700">{{ str_replace('_', ' ', $module) }}</h3>
                            <p class="text-xs text-gray-500 mt-0.5">
                                @switch($module)
                                    @case('inventory') Módulo obligatorio de productos, movimientos y kardex. @break
                                    @case('suppliers') Proveedores, compras y devoluciones. @break
                                    @case('technicians') Solicitudes de técnicos, QR, aprobación. @break
                                    @case('technician_returns') Devoluciones de sobrantes y dañados. @break
                                    @case('work_orders') Órdenes de trabajo y asignaciones. @break
                                    @case('geolocation') Mapas y coordenadas (requiere work_orders). @break
                                    @case('reports') Reportes y dashboards. @break
                                    @default Módulo adicional.
                                @endswitch
                            </p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer flex-shrink-0 mt-0.5">
                            <input type="checkbox" wire:model.live="modules.{{ $module }}" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </x-ui.card>

    {{-- Modal Servicio --}}
    @if($showServiceModal)
        <div x-data="{ open: true }" x-show="open" x-cloak
            class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center"
            style="display: none;">
            <div class="relative mx-auto p-5 w-full max-w-md">
                <div class="bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                        <h3 class="text-lg font-semibold">{{ $editingServiceId ? 'Editar' : 'Nuevo' }} Tipo de Servicio</h3>
                        <button wire:click="closeServiceModal" class="text-gray-400 hover:text-gray-600 transition">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>
                    <div class="p-5 space-y-4">
                        <x-forms.group name="serviceName" label="Nombre">
                            <x-ui.input type="text" wire:model="serviceName" placeholder="Ej: instalacion, traslado" />
                        </x-forms.group>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de requerimiento</label>
                            <div class="space-y-2">
                                <label class="flex items-center gap-3">
                                    <input type="radio" wire:model="serviceRequirementTypeModal" value="" class="border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="text-sm text-gray-700">Ninguno</span>
                                </label>
                                <label class="flex items-center gap-3">
                                    <input type="radio" wire:model="serviceRequirementTypeModal" value="noc" class="border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="text-sm text-gray-700">Requiere NOC</span>
                                </label>
                                <label class="flex items-center gap-3">
                                    <input type="radio" wire:model="serviceRequirementTypeModal" value="ot" class="border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="text-sm text-gray-700">Requiere OT</span>
                                </label>
                                <label class="flex items-center gap-3">
                                    <input type="radio" wire:model="serviceRequirementTypeModal" value="contract" class="border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="text-sm text-gray-700">Requiere Contrato</span>
                                </label>
                                <label class="flex items-center gap-3">
                                    <input type="radio" wire:model="serviceRequirementTypeModal" value="potential" class="border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="text-sm text-gray-700">Cliente Potencial</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3">
                        <x-ui.button variant="ghost" wire:click="closeServiceModal">Cancelar</x-ui.button>
                        <x-ui.button variant="primary" wire:click="saveService">{{ $editingServiceId ? 'Actualizar' : 'Guardar' }}</x-ui.button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Tipo Documento --}}
    @if($showDocTypeModal)
        <div x-data="{ open: true }" x-show="open" x-cloak
            class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center"
            style="display: none;">
            <div class="relative mx-auto p-5 w-full max-w-md">
                <div class="bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                        <h3 class="text-lg font-semibold">{{ $editingDocTypeIndex !== null ? 'Editar' : 'Nuevo' }} Tipo de Documento</h3>
                        <button wire:click="closeDocTypeModal" class="text-gray-400 hover:text-gray-600 transition">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>
                    <div class="p-5 space-y-4">
                        <x-forms.group name="docTypeName" label="Nombre *">
                            <x-ui.input type="text" wire:model="docTypeName" placeholder="Ej: DUI, NIT, Pasaporte" />
                        </x-forms.group>
                    </div>
                    <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3">
                        <x-ui.button variant="ghost" wire:click="closeDocTypeModal">Cancelar</x-ui.button>
                        <x-ui.button variant="primary" wire:click="saveDocType">{{ $editingDocTypeIndex !== null ? 'Actualizar' : 'Guardar' }}</x-ui.button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal KB --}}
    @if($showKbModal)
        <div x-data="{ open: true }" x-show="open" x-cloak
            class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center"
            style="display: none;">
            <div class="relative mx-auto p-5 w-full max-w-2xl">
                <div class="bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                        <h3 class="text-lg font-semibold">{{ $editingArticleId ? 'Editar' : 'Nuevo' }} Artículo</h3>
                        <button wire:click="closeKbModal" class="text-gray-400 hover:text-gray-600 transition">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>
                    <div class="p-5 space-y-4">
                        <x-forms.group name="kbTitle" label="Título *">
                            <x-ui.input type="text" wire:model="kbTitle" placeholder="Título del artículo" />
                        </x-forms.group>
                        <x-forms.group name="kbContent" label="Contenido *">
                            <x-ui.textarea wire:model="kbContent" rows="6" placeholder="Contenido técnico..." />
                        </x-forms.group>
                        <x-forms.group name="kbCategory" label="Categoría">
                            <x-ui.input type="text" wire:model="kbCategory" placeholder="Ej: Procedimiento, Instalación, Diagnóstico" />
                        </x-forms.group>
                        <x-forms.group name="kbPriority" label="Prioridad">
                            <x-ui.select wire:model="kbPriority">
                                <option value="">Sin prioridad</option>
                                <option value="P1">P1 - Crítico</option>
                                <option value="P2">P2 - Alta</option>
                                <option value="P3">P3 - Media</option>
                                <option value="P4">P4 - Baja</option>
                            </x-ui.select>
                        </x-forms.group>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Vincular a Tipos de Servicio</label>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-2 max-h-40 overflow-y-auto">
                                @foreach($serviceTypes as $type)
                                    <label class="flex items-center gap-2 text-sm">
                                        <x-ui.checkbox value="{{ $type->id }}" wire:model="selectedKbServiceTypes" />
                                        {{ str_replace('_', ' ', $type->name) }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3">
                        <x-ui.button variant="ghost" wire:click="closeKbModal">Cancelar</x-ui.button>
                        <x-ui.button variant="primary" wire:click="saveArticle">{{ $editingArticleId ? 'Actualizar' : 'Guardar' }}</x-ui.button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Toast unificado -->
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