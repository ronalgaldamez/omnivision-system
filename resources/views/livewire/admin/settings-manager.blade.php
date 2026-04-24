<div class="max-w-3xl mx-auto bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <h1 class="text-lg font-semibold mb-4">Configuraciones del Sistema</h1>

    <div class="space-y-6">
        <!-- Switch para OT obligatoria -->
        <div class="flex items-center justify-between border-b pb-4">
            <div>
                <label class="block text-sm font-medium">Órdenes de Trabajo obligatorias</label>
                <p class="text-xs text-gray-500">Si está activo, los técnicos no podrán crear solicitudes sin una OT asignada.</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" wire:model.live="otRequired" class="sr-only peer">
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
            </label>
        </div>

        <!-- Switches para cada módulo -->
        <h2 class="text-md font-semibold mt-4">Módulos del Sistema</h2>
        <p class="text-xs text-gray-500 mb-2">Activa o desactiva módulos completos. Los cambios se aplican inmediatamente.</p>

        @foreach($modules as $module => $active)
        <div class="flex items-center justify-between border-b pb-3">
            <div>
                <h3 class="font-medium capitalize">{{ str_replace('_', ' ', $module) }}</h3>
                <p class="text-xs text-gray-500">
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
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" wire:model.live="modules.{{ $module }}" class="sr-only peer">
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
            </label>
        </div>
        @endforeach
    </div>

    <!-- Toast -->
    <div x-data="{ toast: null, toastType: null, toastMessage: '' }"
         x-on:show-toast.window="toast = true; toastType = $event.detail.type; toastMessage = $event.detail.message; setTimeout(() => toast = false, 3000)"
         x-show="toast" x-cloak class="fixed bottom-5 right-5 z-50 transition-all duration-300" style="display: none;">
        <div x-show="toastType === 'success'" class="bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg flex items-center gap-2">
            <span class="material-symbols-outlined">check_circle</span> <span x-text="toastMessage"></span>
        </div>
    </div>
    <style>[x-cloak] { display: none !important; }</style>
</div>