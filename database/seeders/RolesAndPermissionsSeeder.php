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
            'view catalog',
            'manage catalog',
            'view reports',
            'view dashboard',
            'view clients',
            'create clients',
            'edit clients',
            'delete clients',
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
            'view requisitions',
            'create requisitions',
            'access_inventory',
            'access_suppliers',
            'access_technicians',
            'access_reports',
            'access_support',
            'access_admin',
            'view_movements_menu',
            'view_new_movement_menu',
            'view_products_menu',
            'view_kardex_menu',
            'view_suppliers_menu',
            'view_purchase_history_menu',
            'view_new_purchase_menu',
            'view_returns_menu',
            'view_register_return_menu',
            'view_work_orders_menu',
            'view_map_ot_menu',
            'view_requisitions_menu',
            'view_low_stock_menu',
            'view_movements_report_menu',
            'view_technician_performance_menu',
            'view_new_ticket_menu',
            'view_all_tickets_menu',
            'view_noc_panel_menu',
            'view_users_menu',
            'view_roles_menu',
            'view_catalog_menu',
            'view_settings_menu',
            'view technician dashboard',
            'assign any technician in returns',
            'access my daily jobs',
            'capture coordinates',
            // Supervisor zones
            'assign supervisors to zones',
            // SLA
            'view sla goals',
            'create sla goals',
            'edit sla goals',
            'delete sla goals',
            'view sla dashboard',
        ];

        Permission::whereNotIn('name', $permissions)->delete();
        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // Roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $warehouseRole = Role::firstOrCreate(['name' => 'warehouse']);
        $technicianRole = Role::firstOrCreate(['name' => 'technician']);
        $accountantRole = Role::firstOrCreate(['name' => 'accountant']);
        $buyerRole = Role::firstOrCreate(['name' => 'buyer']);
        $atencionClienteRole = Role::firstOrCreate(['name' => 'atencion_al_cliente'], ['prefix' => 'SAC']);
        $nocRole = Role::firstOrCreate(['name' => 'noc']);
        $fieldSupervisorRole = Role::firstOrCreate(['name' => 'field_supervisor'], ['prefix' => 'FS']);
        $salesRepRole = Role::firstOrCreate(['name' => 'sales_rep'], ['prefix' => 'SR']);

        // Asignación de permisos
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
            'view_movements_menu',
            'view_new_movement_menu',
            'view_products_menu',
            'view_kardex_menu',
            'access_suppliers',
            'view_suppliers_menu',
            'view_purchase_history_menu',
            'view_new_purchase_menu',
            'access_technicians',
            'view_returns_menu',
            'view_register_return_menu',
            'view_work_orders_menu',
            'view_map_ot_menu',
            'view_requisitions_menu',
            'access_reports',
            'view_low_stock_menu',
            'view_movements_report_menu',
            'view_technician_performance_menu',
            'access_support',
            'view_new_ticket_menu',
            'view_all_tickets_menu',
            'view_noc_panel_menu',
            'capture coordinates',
        ]);

        $technicianRole->syncPermissions([
            'view work_orders',
            'create work_orders',
            'edit work_orders',
            'complete work_orders',
            'view own work_orders',
            'view dashboard',
            'view requisitions',
            'create requisitions',
            'access_technicians',
            'view_work_orders_menu',
            'view_map_ot_menu',
            'view_requisitions_menu',
            'access my daily jobs',
            'view technician dashboard',
            'capture coordinates',
        ]);

        $accountantRole->syncPermissions([
            'view products',
            'view movements',
            'view kardex',
            'view purchases',
            'view reports',
            'view dashboard',
            'access_reports',
            'view_low_stock_menu',
            'view_movements_report_menu',
            'view_technician_performance_menu',
        ]);

        $buyerRole->syncPermissions([
            'view products',
            'view suppliers',
            'create purchases',
            'access_suppliers',
            'view_suppliers_menu',
            'view_new_purchase_menu',
        ]);

        $atencionClienteRole->syncPermissions([
            'view clients',
            'create clients',
            'edit clients',
            'delete clients',
            'view own tickets',
            'create tickets',
            'view own work_orders',
            'view work_orders',
            'create work_orders',
            'edit work_orders',
            'access_support',
            'view_new_ticket_menu',
            'view_all_tickets_menu',
        ]);

        $nocRole->syncPermissions([
            'view dashboard',
            'view any tickets',
            'view own tickets',
            'create tickets',
            'update tickets',
            'access noc panel',
            'view pending noc tickets',
            'view resolutions',
            'view own work_orders',
            'view all work orders',
            'view work_orders',
            'create work_orders',
            'edit work_orders',
            'assign technicians',
            'access_support',
            'view_new_ticket_menu',
            'view_all_tickets_menu',
            'view_noc_panel_menu',
            'access_technicians',
            'view_work_orders_menu',
            'view technician dashboard',
            'view movements',
            'view low stock',
            'view requisitions',
        ]);

        $fieldSupervisorRole->syncPermissions([
            'view work_orders',
            'create work_orders',
            'edit work_orders',
            'view low stock',
            'view reports',
            'view dashboard',
            'assign technicians',
            'cancel work orders',
            'view all work orders',
            'complete work_orders',
            'view requisitions',
            'access_technicians',
            'view_work_orders_menu',
            'view_map_ot_menu',
            'view_requisitions_menu',
            'access_reports',
            'view_low_stock_menu',
            'view_movements_report_menu',
            'view_technician_performance_menu',
            'access_support',
            'view_all_tickets_menu',
        ]);

        $salesRepRole->syncPermissions([
            'view work_orders',
            'create work_orders',
            'edit work_orders',
            'view own work_orders',
            'capture coordinates',
            'access_technicians',
            'view_work_orders_menu',
        ]);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}