<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Módulos del Sistema
    |--------------------------------------------------------------------------
    | Activar (true) o desactivar (false) módulos completos.
    | Al desactivar, las rutas, menús y lógica asociada no se cargarán.
    */
    'modules' => [
        'inventory' => true,           // Módulo obligatorio
        'suppliers' => true,           // Proveedores, compras, devoluciones
        'technicians' => true,         // Solicitudes, aprobación, códigos
        'technician_returns' => true,  // Devoluciones sobrantes/dañados
        'work_orders' => true,        // Órdenes de trabajo (opcional)
        'geolocation' => false,        // Mapas y coordenadas (opcional)
        'reports' => true,             // Reportes y dashboards
    ],

    /*
    |--------------------------------------------------------------------------
    | Dependencias entre módulos
    |--------------------------------------------------------------------------
    | Si un módulo requiere otro para funcionar, se puede validar en tiempo de ejecución.
    */
    'dependencies' => [
        'technician_returns' => ['technicians'],
        'work_orders' => ['technicians'],
        'geolocation' => ['work_orders'], // puede ser standalone, pero se recomienda con work_orders
    ],
];