<div class="max-w-3xl mx-auto">
    <!-- Tarjeta principal -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <!-- Encabezado con fondo sutil -->
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50">
            <h1 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                <span class="material-symbols-outlined text-gray-500">
                    {{ $roleId ? 'edit' : 'add_circle' }}
                </span>
                {{ $roleId ? 'Editar' : 'Nuevo' }} Rol
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                {{ $roleId ? 'Modifica los datos y permisos del rol' : 'Registra un nuevo rol y asigna sus permisos' }}
            </p>
        </div>

        <!-- Contenido del formulario -->
        <div class="p-6">
            <form wire:submit="save" class="space-y-6">
                <!-- Nombre del Rol -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-gray-400 text-base">badge</span>
                        Nombre del Rol
                    </label>
                    <div class="relative">
                        <input type="text" wire:model="name"
                            class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm"
                            placeholder="Ej: administrador, técnico, reportes...">
                        <span
                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">edit_note</span>
                    </div>
                    @error('name') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Prefijo del Rol -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-gray-400 text-base">tag</span>
                        Prefijo (Abreviatura)
                    </label>
                    <div class="relative">
                        <input type="text" wire:model="prefix"
                            class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm"
                            placeholder="Ej: SEC, NOC, ADM, SUP...">
                        <span
                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">edit_note</span>
                    </div>
                    @error('prefix') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Permisos agrupados por categorías -->
                <div class="border-t border-gray-200 pt-6">
                    <h2 class="text-md font-semibold text-gray-800 flex items-center gap-2 mb-4">
                        <span class="material-symbols-outlined text-gray-500">lock</span>
                        Permisos
                    </h2>

                    @php
                        $permissionCategoryMap = [
                            // Productos
                            'view products' => 'Productos',
                            'create products' => 'Productos',
                            'edit products' => 'Productos',
                            'delete products' => 'Productos',
                            // Movimientos
                            'view movements' => 'Movimientos',
                            'create movements' => 'Movimientos',
                            // Kardex
                            'view kardex' => 'Kardex',
                            // Proveedores
                            'view suppliers' => 'Proveedores',
                            'create suppliers' => 'Proveedores',
                            'edit suppliers' => 'Proveedores',
                            'delete suppliers' => 'Proveedores',
                            // Compras
                            'view purchases' => 'Compras',
                            'create purchases' => 'Compras',
                            // Devoluciones
                            'view technician_returns' => 'Devoluciones',
                            'create technician_returns' => 'Devoluciones',
                            // Órdenes de Trabajo
                            'view work_orders' => 'Órdenes de Trabajo',
                            'create work_orders' => 'Órdenes de Trabajo',
                            'edit work_orders' => 'Órdenes de Trabajo',
                            'delete work_orders' => 'Órdenes de Trabajo',
                            'complete work_orders' => 'Órdenes de Trabajo',
                            'assign technicians' => 'Órdenes de Trabajo',
                            'cancel work orders' => 'Órdenes de Trabajo',
                            'view all work orders' => 'Órdenes de Trabajo',
                            'view own work_orders' => 'Órdenes de Trabajo',
                            'access my daily jobs' => 'Órdenes de Trabajo',
                            // Catálogo
                            'view catalog' => 'Catálogo',
                            'manage catalog' => 'Catálogo',
                            // Reportes
                            'view reports' => 'Reportes',
                            // Dashboard
                            'view dashboard' => 'Dashboard',
                            // Clientes
                            'view clients' => 'Clientes',
                            'create clients' => 'Clientes',
                            'edit clients' => 'Clientes',
                            // Tickets
                            'view tickets' => 'Tickets',
                            'create tickets' => 'Tickets',
                            'edit tickets' => 'Tickets',
                            'view any tickets' => 'Tickets',
                            'view own tickets' => 'Tickets',
                            'update tickets' => 'Tickets',
                            'delete tickets' => 'Tickets',
                            'access noc panel' => 'Tickets',
                            'view pending noc tickets' => 'Tickets',
                            'view resolutions' => 'Tickets',
                            // Acceso a Módulos
                            'access_inventory' => 'Acceso a Módulos',
                            'access_suppliers' => 'Acceso a Módulos',
                            'access_technicians' => 'Acceso a Módulos',
                            'access_reports' => 'Acceso a Módulos',
                            'access_support' => 'Acceso a Módulos',
                            'access_admin' => 'Acceso a Módulos',
                            // Submenús Inventario
                            'view_movements_menu' => 'Submenús Inventario',
                            'view_new_movement_menu' => 'Submenús Inventario',
                            'view_products_menu' => 'Submenús Inventario',
                            'view_kardex_menu' => 'Submenús Inventario',
                            // Submenús Compras
                            'view_suppliers_menu' => 'Submenús Compras',
                            'view_purchase_history_menu' => 'Submenús Compras',
                            'view_new_purchase_menu' => 'Submenús Compras',
                            'view_returns_menu' => 'Submenús Compras',
                            // Submenús Técnicos
                            'view_returns_menu' => 'Submenús Técnicos',
                            'view_register_return_menu' => 'Submenús Técnicos',
                            'view_work_orders_menu' => 'Submenús Técnicos',
                            'view_map_ot_menu' => 'Submenús Técnicos',
                            'view_requisitions_menu' => 'Submenús Técnicos',
                            // Submenús Reportes
                            'view_low_stock_menu' => 'Submenús Reportes',
                            'view_movements_report_menu' => 'Submenús Reportes',
                            'view_technician_performance_menu' => 'Submenús Reportes',
                            // Submenús Soporte
                            'view_new_ticket_menu' => 'Submenús Soporte',
                            'view_all_tickets_menu' => 'Submenús Soporte',
                            'view_noc_panel_menu' => 'Submenús Soporte',
                            // Submenús Admin
                            'view_users_menu' => 'Submenús Admin',
                            'view_roles_menu' => 'Submenús Admin',
                            'view_catalog_menu' => 'Submenús Admin',
                            'view_settings_menu' => 'Submenús Admin',
                            // Otros
                            'view low stock' => 'General',
                            'view requisitions' => 'General',
                            'create requisitions' => 'General',
                        ];

                        $categorized = [];
                        foreach ($permissions as $perm) {
                            $category = $permissionCategoryMap[$perm->name] ?? 'General';
                            $categorized[$category][] = $perm;
                        }

                        $categoryOrder = [
                            'Productos',
                            'Movimientos',
                            'Kardex',
                            'Proveedores',
                            'Compras',
                            'Solicitudes',
                            'Devoluciones',
                            'Órdenes de Trabajo',
                            'Catálogo',
                            'Reportes',
                            'Dashboard',
                            'Clientes',
                            'Tickets',
                            'Acceso a Módulos',
                            'Submenús Inventario',
                            'Submenús Compras',
                            'Submenús Técnicos',
                            'Submenús Reportes',
                            'Submenús Soporte',
                            'Submenús Admin',
                            'General',
                        ];

                        $orderedCategorized = [];
                        foreach ($categoryOrder as $cat) {
                            if (isset($categorized[$cat])) {
                                $orderedCategorized[$cat] = $categorized[$cat];
                            }
                        }
                        foreach ($categorized as $cat => $perms) {
                            if (!isset($orderedCategorized[$cat])) {
                                $orderedCategorized[$cat] = $perms;
                            }
                        }

                        $categoryIcons = [
                            'Productos' => 'inventory_2',
                            'Movimientos' => 'swap_vert',
                            'Kardex' => 'receipt',
                            'Proveedores' => 'warehouse',
                            'Compras' => 'shopping_cart',
                            'Solicitudes' => 'inbox',
                            'Devoluciones' => 'assignment_return',
                            'Órdenes de Trabajo' => 'engineering',
                            'Catálogo' => 'category',
                            'Reportes' => 'assessment',
                            'Dashboard' => 'dashboard',
                            'Clientes' => 'person',
                            'Tickets' => 'confirmation_number',
                            'Acceso a Módulos' => 'extension',
                            'Submenús Inventario' => 'inventory_2',
                            'Submenús Compras' => 'shopping_cart',
                            'Submenús Técnicos' => 'handyman',
                            'Submenús Reportes' => 'assessment',
                            'Submenús Soporte' => 'support_agent',
                            'Submenús Admin' => 'admin_panel_settings',
                            'General' => 'settings',
                        ];
                    @endphp

                    <div class="space-y-5">
                        @foreach($orderedCategorized as $category => $perms)
                            <div>
                                <h3
                                    class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                    <span
                                        class="material-symbols-outlined text-gray-400 text-base">{{ $categoryIcons[$category] ?? 'settings' }}</span>
                                    {{ $category }}
                                </h3>
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                                    @foreach($perms as $perm)
                                        <label
                                            class="flex items-center gap-2.5 p-2.5 bg-gray-50/80 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-100 transition">
                                            <input type="checkbox" value="{{ $perm->name }}" wire:model="selectedPermissions"
                                                class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-2 focus:ring-blue-500/20 w-4 h-4">
                                            <span class="text-sm text-gray-700">{{ $perm->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @error('selectedPermissions') <span class="text-xs text-red-500 mt-2 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Botones de acción -->
                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('admin.roles.index') }}"
                        class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-300 transition shadow-sm">
                        Cancelar
                    </a>
                    <button type="submit"
                        class="px-5 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 transition inline-flex items-center gap-2">
                        <span class="material-symbols-outlined text-base">save</span>
                        {{ $roleId ? 'Actualizar' : 'Guardar' }}
                    </button>
                </div>
            </form>

            <!-- Mensajes de sesión -->
            @if(session('message'))
                <div
                    class="mt-4 flex items-center gap-2 text-sm text-green-700 bg-green-50 px-4 py-3 rounded-lg border border-green-200">
                    <span class="material-symbols-outlined text-green-600">check_circle</span>
                    {{ session('message') }}
                </div>
            @endif
        </div>
    </div>
</div>