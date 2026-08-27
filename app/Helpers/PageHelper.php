<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

if (!function_exists('page_title_from_route')) {
    function page_title_from_route(): string
    {
        $routeName = Route::currentRouteName();

        if (!$routeName) {
            return config('app.name', 'Sistema Kardex');
        }

        // Mapeo de rutas con nombre legible en español.
        $custom = [
            'dashboard'                => 'Inicio',

            // ── Admin ──
            'admin.users.index'        => 'Gestión de Usuarios',
            'admin.users.create'       => 'Nuevo Usuario',
            'admin.users.edit'         => 'Editar Usuario',
            'admin.roles.index'        => 'Gestión de Roles',
            'admin.roles.create'       => 'Nuevo Rol',
            'admin.roles.edit'         => 'Editar Rol',
            'admin.settings'           => 'Configuración',
            'admin.branches.index'     => 'Sucursales',
            'admin.branches.create'    => 'Nueva Sucursal',
            'admin.branches.edit'      => 'Editar Sucursal',
            'admin.clients.index'      => 'Clientes',
            'admin.clients.create'     => 'Nuevo Cliente',
            'admin.clients.edit'       => 'Editar Cliente',
            'admin.clients.show'       => 'Ver Cliente',
            'admin.asignaciones'       => 'Asignaciones',
            'admin.vehiculos'          => 'Vehículos',
            'admin.shelves'            => 'Estanterías',
            'admin.supervisor-zones'   => 'Supervisores por Zona',
            'admin.catalog'            => 'Catálogo',
            'admin.imports.products'   => 'Importar Productos',
            'admin.plans'              => 'Planes y Zonas',
            'admin.plans.rules'        => 'Reglas de Plan',
            'admin.changelog.index'    => 'Actualizaciones',

            // ── SLA ──
            'sla.dashboard'            => 'Dashboard SLA',
            'admin.sla.goals.index'    => 'Metas SLA',
            'admin.sla.goals.create'   => 'Nueva Meta SLA',
            'admin.sla.goals.edit'     => 'Editar Meta SLA',
            'sla.ticket-timeline'      => 'Timeline del Ticket',
            'sla.work-order-timeline'  => 'Timeline de OT',

            // ── Soporte ──
            'noc.panel'                => 'Bandeja NOC',
            'tickets.index'            => 'Tickets',
            'tickets.create'           => 'Nuevo Ticket',

            // ── Inventario ──
            'products.index'           => 'Productos',
            'products.create'          => 'Nuevo Producto',
            'products.edit'            => 'Editar Producto',
            'products.show'            => 'Ver Producto',
            'movements.index'          => 'Movimientos',
            'movements.create'         => 'Nuevo Movimiento',
            'kardex.index'             => 'Ver Kardex',
            'devices.index'            => 'Dispositivos',
            'devices.register'         => 'Registrar Dispositivo',
            'devices.show'             => 'Detalle de Dispositivo',

            // ── Bodega ──
            'bodega.requisitions.index' => 'Requisiciones de Bodega',
            'bodega.shipments.index'    => 'Envíos',
            'bodega.shipments.create'   => 'Nuevo Envío',
            'bodega.shipments.receive'  => 'Recibir Envío',
            'bodega.shipments.show'     => 'Detalle de Envío',

            // ── Proveedores / Compras ──
            'suppliers.index'          => 'Proveedores',
            'suppliers.create'         => 'Nuevo Proveedor',
            'suppliers.show'           => 'Ver Proveedor',
            'suppliers.edit'           => 'Editar Proveedor',
            'purchases.index'          => 'Historial de Compras',
            'purchases.create'         => 'Nueva Compra',
            'purchases.show'           => 'Ver Compra',

            // ── Órdenes de Trabajo ──
            'work-orders.index'        => 'Órdenes de Trabajo',
            'work-orders.create'       => 'Nueva Orden de Trabajo',
            'work-orders.edit'         => 'Editar Orden de Trabajo',
            'work-orders.show'         => 'Detalle de Orden',
            'work-orders.map'          => 'Mapa de OT',

            // ── Devoluciones ──
            'technician-returns.index'  => 'Devoluciones',
            'technician-returns.create' => 'Registrar Devolución',
            'returns.create'            => 'Registrar Devolución',

            // ── Requisiciones (técnico) ──
            'technician.requisitions.index'  => 'Mis Requisiciones',
            'technician.requisitions.create' => 'Nueva Requisición',
            'technician.requisitions.show'   => 'Detalle de Requisición',
            'technician.requisitions.close'  => 'Cierre Semanal',

            // ── Contratos ──
            'contracts.index'          => 'Contratos',
            'contracts.inbox'          => 'Bandeja de Contratos',
            'contracts.create'         => 'Nuevo Contrato',
            'contracts.workflow'       => 'Contrato',
            'contracts.payments'       => 'Pagos',

            // ── Reportes ──
            'reports.stock'            => 'Stock Bajo',
            'reports.movements'        => 'Reporte de Movimientos',
            'reports.technicians'      => 'Rendimiento de Técnicos',
            'reports.performance'      => 'Rendimiento Global',

            // ── Otros ──
            'notifications.index'      => 'Notificaciones',
            'profile'                  => 'Mi Perfil',
        ];

        if (array_key_exists($routeName, $custom)) {
            return $custom[$routeName];
        }

        // Fallback automático en español: traduce los segmentos conocidos de la ruta.
        return translate_route_segments($routeName);
    }
}

if (!function_exists('translate_route_segments')) {
    function translate_route_segments(string $routeName): string
    {
        $dictionary = [
            'dashboard'   => 'Inicio',
            'admin'       => 'Admin',
            'users'       => 'Usuarios',
            'roles'       => 'Roles',
            'settings'    => 'Configuración',
            'branches'    => 'Sucursales',
            'clients'     => 'Clientes',
            'asignaciones'=> 'Asignaciones',
            'vehiculos'   => 'Vehículos',
            'shelves'     => 'Estanterías',
            'catalog'     => 'Catálogo',
            'plans'       => 'Planes',
            'changelog'   => 'Actualizaciones',
            'sla'         => 'SLA',
            'goals'       => 'Metas',
            'timeline'    => 'Timeline',
            'noc'         => 'NOC',
            'panel'       => 'Panel',
            'tickets'     => 'Tickets',
            'ticket'      => 'Ticket',
            'products'    => 'Productos',
            'product'     => 'Producto',
            'movements'   => 'Movimientos',
            'kardex'      => 'Kardex',
            'devices'     => 'Dispositivos',
            'device'      => 'Dispositivo',
            'bodega'      => 'Bodega',
            'requisitions'=> 'Requisiciones',
            'requisition' => 'Requisición',
            'shipments'   => 'Envíos',
            'shipment'    => 'Envío',
            'suppliers'   => 'Proveedores',
            'supplier'    => 'Proveedor',
            'purchases'   => 'Compras',
            'purchase'    => 'Compra',
            'work-orders' => 'Órdenes de Trabajo',
            'work-order'  => 'Orden de Trabajo',
            'technician'  => 'Técnico',
            'technicians' => 'Técnicos',
            'returns'     => 'Devoluciones',
            'contracts'   => 'Contratos',
            'contract'    => 'Contrato',
            'inbox'       => 'Bandeja',
            'payments'    => 'Pagos',
            'workflow'    => 'Flujo',
            'reports'     => 'Reportes',
            'stock'       => 'Stock',
            'performance' => 'Rendimiento',
            'notifications'=> 'Notificaciones',
            'profile'     => 'Perfil',
            'supervisor'  => 'Supervisor',
            'zones'       => 'Zonas',
            'zone'        => 'Zona',
            'map'         => 'Mapa',
        ];

        $action = [
            'index'  => '',
            'create' => 'Nuevo',
            'edit'   => 'Editar',
            'show'   => 'Detalle',
            'close'  => 'Cierre',
            'receive'=> 'Recibir',
            'register'=> 'Registrar',
        ];

        $segments = explode('.', $routeName);

        $words = [];
        foreach ($segments as $segment) {
            if (array_key_exists($segment, $action)) {
                if ($action[$segment] !== '') {
                    $words[] = $action[$segment];
                }
                continue;
            }

            if (array_key_exists($segment, $dictionary)) {
                $words[] = $dictionary[$segment];
            } else {
                $words[] = Str::headline(str_replace('-', ' ', $segment));
            }
        }

        return $words ? implode(' ', $words) : Str::headline($routeName);
    }
}
