<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Lista oficial de permisos (únicos que deben existir)
        $permissions = [
            // Inventario
            'view products',
            'create products',
            'edit products',
            'delete products',
            'view movements',
            'create movements',
            'view kardex',
            // Proveedores y Compras
            'view suppliers',
            'create suppliers',
            'edit suppliers',
            'delete suppliers',
            'view purchases',
            'create purchases',
            // Técnicos (sin solicitudes)
            'view work_orders',
            'create work_orders',
            'edit work_orders',
            'delete work_orders',
            'complete work_orders',
            'assign technicians',
            'cancel work orders',
            'view all work orders',
            'view technician_returns',
            'create technician_returns',
            // Catálogo
            'view catalog',
            'manage catalog',
            // Reportes
            'view reports',
            'view dashboard',
            // Clientes
            'view clients',
            'create clients',
            'edit clients',
            // Tickets
            'view tickets',
            'create tickets',
            'edit tickets',
            'view any tickets',
            'view own tickets',
            'update tickets',
            'delete tickets',
            'access noc panel',
            'view low stock',
            'view pending noc tickets',
            'view resolutions',
            'view own work_orders',
            // Requisiciones
            'view requisitions',
            'create requisitions',
            // ACCESO A MÓDULOS
            'access_inventory',
            'access_suppliers',
            'access_technicians',
            'access_reports',
            'access_support',
            'access_admin',
        ];

        // 🧹 Eliminar cualquier permiso que NO esté en la lista oficial
        Permission::whereNotIn('name', $permissions)->delete();

        // Crear los permisos oficiales (si no existen)
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

        // Admin recibe TODOS los permisos
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
            'view work_orders',
            'create work_orders',
            'edit work_orders',
            'delete work_orders',
            'complete work_orders',
            'assign technicians',
            'view technician_returns',
            'create technician_returns',
            'view catalog',
            'manage catalog',
            'view reports',
            'view dashboard',
            'view low stock',
            'access_inventory',
            'access_suppliers',
            'access_technicians',
            'access_reports',
            'access_support',
        ]);

        // Technician
        $technicianRole->syncPermissions([
            'view products',
            'view kardex',
            'view work_orders',
            'complete work_orders',
            'view dashboard',
            'view requisitions',
            'create requisitions',
            'access_technicians',
        ]);

        // Accountant
        $accountantRole->syncPermissions([
            'view products',
            'view movements',
            'view kardex',
            'view purchases',
            'view reports',
            'view dashboard',
            'access_reports',
        ]);

        // Buyer
        $buyerRole->syncPermissions([
            'view products',
            'view suppliers',
            'create purchases',
            'access_suppliers',
        ]);

        // Secretary
        $secretaryRole->syncPermissions([
            'view clients',
            'create clients',
            'edit clients',
            'view own tickets',
            'create tickets',
            'view own work_orders',
            'access_support',
        ]);

        // NOC
        $nocRole->syncPermissions([
            'view any tickets',
            'view own tickets',
            'create tickets',
            'update tickets',
            'access noc panel',
            'view pending noc tickets',
            'view resolutions',
            'view own work_orders',
            'view all work orders',
            'access_support',
            'access_technicians',
        ]);

        // Supervisor
        $supervisorRole->syncPermissions([
            'view work_orders',
            'edit work_orders',
            'view low stock',
            'view reports',
            'view dashboard',
            'assign technicians',
            'cancel work orders',
            'view all work orders',
            'complete work_orders',
            'view requisitions',   // puede ver pero no crear
            'access_technicians',
            'access_reports',
            'access_support',
        ]);

        // Limpiar caché al final
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}