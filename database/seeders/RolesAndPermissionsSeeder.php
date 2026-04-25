<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ========== PERMISOS COMPLETOS ==========
        $permissions = [
            // Inventory
            'view products',
            'create products',
            'edit products',
            'delete products',
            'view movements',
            'create movements',
            'view kardex',

            // Suppliers
            'view suppliers',
            'create suppliers',
            'edit suppliers',
            'delete suppliers',
            'view purchases',
            'create purchases',

            // Technicians (solicitudes)
            'view technician_requests',
            'create technician_requests',
            'approve technician_requests',

            // Work Orders
            'view work_orders',
            'create work_orders',
            'edit work_orders',
            'delete work_orders',
            'complete work_orders',

            // Technician Returns
            'view technician_returns',
            'create technician_returns',

            // Catalog (brands, models, categories)
            'view catalog',
            'manage catalog',   // crear, editar, eliminar

            // Reports (generales)
            'view reports',

            // Dashboard (panel personalizado)
            'view dashboard',

            // NUEVOS PERMISOS para secretaria y NOC
            'view clients',
            'create clients',
            'edit clients',
            'view tickets',
            'create tickets',
            'edit tickets',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // ========== ROLES EXISTENTES ==========
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $warehouseRole = Role::firstOrCreate(['name' => 'warehouse']);
        $technicianRole = Role::firstOrCreate(['name' => 'technician']);
        $accountantRole = Role::firstOrCreate(['name' => 'accountant']);
        $buyerRole = Role::firstOrCreate(['name' => 'buyer']);

        // Admin: todos los permisos
        $adminRole->syncPermissions(Permission::all());

        // Warehouse (bodeguero)
        $warehouseRole->syncPermissions([
            'view products',
            'create products',
            'edit products',
            'view movements',
            'create movements',
            'view kardex',
            'view suppliers',
            'create suppliers',
            'edit suppliers',
            'view purchases',
            'create purchases',
            'view technician_requests',
            'approve technician_requests',
            'view work_orders',
            'create work_orders',
            'edit work_orders',
            'delete work_orders',
            'complete work_orders',
            'view technician_returns',
            'create technician_returns',
            'view catalog',
            'manage catalog',
            'view reports',
            'view dashboard',
        ]);

        // Technician (técnico)
        $technicianRole->syncPermissions([
            'view products',
            'view kardex',
            'view technician_requests',
            'create technician_requests',
            'view work_orders',
            'view dashboard',
            'complete work_orders',
        ]);

        // Accountant (contador)
        $accountantRole->syncPermissions([
            'view products',
            'view movements',
            'view kardex',
            'view purchases',
            'view reports',
            'view dashboard',
        ]);

        // Buyer (comprador)
        $buyerRole->syncPermissions([
            'view products',
            'view suppliers',
            'create purchases',
        ]);

        // ========== NUEVOS ROLES ==========
        $secretaryRole = Role::firstOrCreate(['name' => 'secretary']);
        $nocRole = Role::firstOrCreate(['name' => 'noc']);
        $supervisorRole = Role::firstOrCreate(['name' => 'supervisor']);

        // Asignar permisos básicos a los nuevos roles
        $secretaryRole->givePermissionTo([
            'view clients',
            'create clients',
            'edit clients',
            'view tickets',
            'create tickets'
        ]);

        $nocRole->givePermissionTo([
            'view tickets',
            'edit tickets',
            'create tickets',
        ]);

        $supervisorRole->givePermissionTo([
            'view work_orders',
            'edit work_orders'
        ]);
    }
}   