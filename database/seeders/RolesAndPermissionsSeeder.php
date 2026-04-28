<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

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

            // Technicians
            'view technician_requests',
            'create technician_requests',
            'approve technician_requests',

            // Work Orders
            'view work_orders',
            'create work_orders',
            'edit work_orders',
            'delete work_orders',
            'complete work_orders',
            // Nuevos
            'assign technicians',
            'cancel work orders',
            'view all work orders',

            // Technician Returns
            'view technician_returns',
            'create technician_returns',

            // Catalog
            'view catalog',
            'manage catalog',

            // Reports
            'view reports',

            // Dashboard
            'view dashboard',

            // Clients & Tickets
            'view clients',
            'create clients',
            'edit clients',

            // Tickets (antiguos y nuevos)
            'view tickets',
            'create tickets',
            'edit tickets',
            'view any tickets',
            'view own tickets',
            'update tickets',
            'delete tickets',
            'access noc panel',

            // Dashboard específicos
            'view low stock',
            'view pending noc tickets',
            'view resolutions',
            'view own work_orders',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // Roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $warehouseRole = Role::firstOrCreate(['name' => 'warehouse']);
        $technicianRole = Role::firstOrCreate(['name' => 'technician']);
        $accountantRole = Role::firstOrCreate(['name' => 'accountant']);
        $buyerRole = Role::firstOrCreate(['name' => 'buyer']);
        $secretaryRole = Role::firstOrCreate(['name' => 'secretary']);
        $nocRole = Role::firstOrCreate(['name' => 'noc']);
        $supervisorRole = Role::firstOrCreate(['name' => 'supervisor']);

        // Admin: todos los permisos
        $adminRole->syncPermissions(Permission::all());

        // Warehouse
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
            'assign technicians',   // puede asignar técnicos
            'view technician_returns',
            'create technician_returns',
            'view catalog',
            'manage catalog',
            'view reports',
            'view dashboard',
            'view low stock',
        ]);

        // Technician
        $technicianRole->syncPermissions([
            'view products',
            'view kardex',
            'view technician_requests',
            'create technician_requests',
            'view work_orders',
            'complete work_orders',
            'view dashboard',
        ]);

        // Accountant
        $accountantRole->syncPermissions([
            'view products',
            'view movements',
            'view kardex',
            'view purchases',
            'view reports',
            'view dashboard',
        ]);

        // Buyer
        $buyerRole->syncPermissions([
            'view products',
            'view suppliers',
            'create purchases',
        ]);

        // Secretary
        $secretaryRole->syncPermissions([
            'view clients',
            'create clients',
            'edit clients',
            'view own tickets',
            'create tickets',
            'view own work_orders',   // ver órdenes relacionadas con sus tickets
        ]);

        // NOC
        $nocRole->syncPermissions([
            'view any tickets',       // puede ver todos los tickets (necesario para el panel)
            'view own tickets',
            'create tickets',
            'update tickets',         // editar tickets
            'access noc panel',       // panel NOC
            'view pending noc tickets',
            'view resolutions',
            'view own work_orders',   // ver órdenes relacionadas con tickets que él gestionó
        ]);

        // Supervisor
        $supervisorRole->syncPermissions([
            'view work_orders',
            'edit work_orders',
            'view technician_requests',
            'view low stock',
            'view reports',
            'view dashboard',
            'assign technicians',
            'cancel work orders',
            'view all work orders',
            'complete work_orders',
        ]);
    }
}