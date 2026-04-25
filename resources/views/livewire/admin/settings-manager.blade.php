<div class="max-w-3xl mx-auto">
    <!-- Tarjeta principal -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <!-- Encabezado con fondo sutil -->
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50">
            <h1 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                <span class="material-symbols-outlined text-gray-500">settings</span>
                Configuraciones del Sistema
            </h1>
            <p class="text-sm text-gray-500 mt-1">Ajustes generales y control de módulos</p>
        </div>

        <!-- Contenido -->
        <div class="p-6 space-y-6">
            <!-- Switch para OT obligatoria -->
            <div class="bg-gray-50/80 rounded-xl border border-gray-200 p-4 flex items-center justify-between gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Órdenes de Trabajo obligatorias</label>
                    <p class="text-xs text-gray-500 mt-0.5">Si está activo, los técnicos no podrán crear solicitudes sin una OT asignada.</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                    <input type="checkbox" wire:model.live="otRequired" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
            </div>

            <!-- Sección de módulos -->
            <div class="pt-2">
                <h2 class="text-md font-semibold text-gray-800 flex items-center gap-2 mb-1">
                    <span class="material-symbols-outlined text-gray-500">extension</span>
                    Módulos del Sistema
                </h2>
                <p class="text-xs text-gray-500 mb-4">Activa o desactiva módulos completos. Los cambios se aplican inmediatamente.</p>

                <div class="space-y-3">
                    @foreach($modules as $module => $active)
                    <div class="bg-gray-50/80 rounded-xl border border-gray-200 p-4 flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-sm font-medium capitalize text-gray-700">
                                {{ str_replace('_', ' ', $module) }}
                            </h3>
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
    </div>

    <!-- Toast unificado con el diseño del sistema -->
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