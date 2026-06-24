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

        // Mapeo de rutas que necesitan un nombre especial
        $custom = [
            // Puedes agregar aquí excepciones manuales si el automático no gusta
            'admin.users.index'     => 'Gestión de Usuarios',
            'admin.roles.index'     => 'Gestión de Roles',
            'admin.settings'        => 'Configuración',
            'technician.requisitions.index' => 'Mis Requisiciones',
            'technician.requisitions.create' => 'Nueva Requisición',
            'technician.requisitions.show'   => 'Detalle de Requisición',
            'noc.panel'                   => 'Panel NOC',
            'work-orders.index'           => 'Órdenes de Trabajo',
            'work-orders.show'            => 'Detalle de Orden',
            'tickets.index'               => 'Tickets',
            'tickets.create'              => 'Nuevo Ticket',
            'products.index'              => 'Productos',
            'products.create'             => 'Nuevo Producto',
            'movements.index'             => 'Movimientos',
            'movements.create'            => 'Nuevo Movimientos',
            'kardex.index'                => 'Ver Kardex',
            'suppliers.index'             => 'Proveedor',
            'suppliers.create'            => 'Nuevo Proveedor',
            'suppliers.show'              => 'Ver Proveedor',
            'suppliers.edit'              => 'Editar Proveedor',
            'purchases.index'             => 'Historial de Compras',
            'purchases.create'            => 'Nueva Compra',
            'purchases.show'              => 'Ver Compra',
            'sla.dashboard'               => 'Dashboard SLA',
            'admin.sla.goals.index'       => 'Metas SLA',
            'admin.sla.goals.create'      => 'Nueva Meta SLA',
            'admin.sla.goals.edit'        => 'Editar Meta SLA',
            'sla.ticket-timeline'         => 'Timeline del Ticket',
            'sla.work-order-timeline'     => 'Timeline de OT',
        ];

        if (array_key_exists($routeName, $custom)) {
            return $custom[$routeName];
        }

        // Generación automática: convertir "work-orders.map" → "Work Orders Map"
        return Str::headline($routeName);
    }
}