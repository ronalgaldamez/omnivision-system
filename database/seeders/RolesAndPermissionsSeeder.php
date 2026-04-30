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
            'view products',
            'create products',
            'edit products',
            'delete products',
            'view movements',
            'create movements',
            'view kardex',
            'view suppliers',
            'create suppliers',
            'edit suppliers',
            'delete suppliers',
            'view purchases',
            'create purchases',
            'view technician_requests',
            'create technician_requests',
            'approve technician_requests',
            'view work_orders',
            'create work_orders',
            'edit work_orders',
            'delete work_orders',
            'complete work_orders',
            'assign technicians',
            'cancel work orders',
            'view all work orders',           // ← correcto (espacios)
            'view technician_returns',
            'create technician_returns',
            'view catalog',
            'manage catalog',
            'view reports',
            'view dashboard',
            'view clients',
            'create clients',
            'edit clients',
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
            'view own work_orders',           // ← correcto (guión bajo en 'work_orders')
        ];

        // 🧹 Paso 1: Eliminar cualquier permiso que NO esté en la lista oficial
        Permission::whereNotIn('name', $permissions)->delete();

        // Paso 2: Crear los permisos oficiales (si no existen)
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

        // Asignación de permisos (sin cambios en los nombres)
        $adminRole->syncPermissions(Permission::all());

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
            'assign technicians',
            'view technician_returns',
            'create technician_returns',
            'view catalog',
            'manage catalog',
            'view reports',
            'view dashboard',
            'view low stock',
        ]);

        $technicianRole->syncPermissions([
            'view products',
            'view kardex',
            'view technician_requests',
            'create technician_requests',
            'view work_orders',
            'complete work_orders',
            'view dashboard',
        ]);

        $accountantRole->syncPermissions([
            'view products',
            'view movements',
            'view kardex',
            'view purchases',
            'view reports',
            'view dashboard',
        ]);

        $buyerRole->syncPermissions([
            'view products',
            'view suppliers',
            'create purchases',
        ]);

        $secretaryRole->syncPermissions([
            'view clients',
            'create clients',
            'edit clients',
            'view own tickets',
            'create tickets',
            'view own work_orders',
        ]);

        $nocRole->syncPermissions([
            'view any tickets',
            'view own tickets',
            'create tickets',
            'update tickets',
            'access noc panel',
            'view pending noc tickets',
            'view resolutions',
            'view own work_orders',
            'view all work orders',  // ← correcto
        ]);

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

        // Limpiar caché al final
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}