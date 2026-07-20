<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Enums\PermissionEnum;
use App\Models\Role;
use App\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ═══════════════════════════════════════════
        // CAPA 1 — Definir todos los permisos
        // ═══════════════════════════════════════════

        Permission::whereNotIn('name', PermissionEnum::allPermissions())->delete();

        foreach (PermissionEnum::allPermissions() as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // ═══════════════════════════════════════════
        // CAPA 2 — Crear roles (sin permisos aún)
        // ═══════════════════════════════════════════

        $admin             = Role::firstOrCreate(['name' => 'admin']);
        $branchAdmin       = Role::firstOrCreate(['name' => 'branch_admin']);
        $warehouse         = Role::firstOrCreate(['name' => 'warehouse']);
        $technician        = Role::firstOrCreate(['name' => 'technician']);
        $accountant        = Role::firstOrCreate(['name' => 'accountant']);
        $buyer             = Role::firstOrCreate(['name' => 'buyer']);
        $atencionCliente   = Role::firstOrCreate(['name' => 'atencion_al_cliente'], ['prefix' => 'SAC']);
        $noc               = Role::firstOrCreate(['name' => 'noc']);
        $fieldSupervisor   = Role::firstOrCreate(['name' => 'field_supervisor'], ['prefix' => 'FS']);
        $salesRep          = Role::firstOrCreate(['name' => 'sales_rep'], ['prefix' => 'SR']);

        // ═══════════════════════════════════════════
        // CAPA 3 — Matriz de asignación (rol → permisos)
        // ═══════════════════════════════════════════

        // ── Admin: todos los permisos ──
        $admin->syncPermissions(Permission::all());

        // ── Branch Admin: administrador de sucursal ──
        // Igual que warehouse + clientes + SLA + soporte extendido + reportes
        // Sin access_admin → no puede gestionar usuarios, roles, catálogo ni config.
        $branchAdmin->syncPermissions([
            // Inventario
            PermissionEnum::AccessInventory,
            PermissionEnum::ViewProducts,
            PermissionEnum::CreateProducts,
            PermissionEnum::EditProducts,
            PermissionEnum::ViewMovements,
            PermissionEnum::CreateMovements,
            PermissionEnum::ViewKardex,
            PermissionEnum::ViewProductsMenu,
            PermissionEnum::ViewMovementsMenu,
            PermissionEnum::ViewNewMovementMenu,
            PermissionEnum::ViewKardexMenu,

            // Proveedores (solo consulta, sin compras)
            PermissionEnum::AccessSuppliers,
            PermissionEnum::ViewSuppliers,
            PermissionEnum::CreateSuppliers,
            PermissionEnum::EditSuppliers,
            PermissionEnum::ViewSuppliersMenu,

            // Técnicos
            PermissionEnum::AccessTechnicians,
            PermissionEnum::ViewTechnicianReturns,
            PermissionEnum::CreateTechnicianReturns,
            PermissionEnum::ViewReturnsMenu,
            PermissionEnum::ViewRegisterReturnMenu,

            // Órdenes de trabajo
            PermissionEnum::ViewWorkOrders,
            PermissionEnum::CreateWorkOrders,
            PermissionEnum::EditWorkOrders,
            PermissionEnum::DeleteWorkOrders,
            PermissionEnum::CompleteWorkOrders,
            PermissionEnum::AssignTechnicians,
            PermissionEnum::CancelWorkOrders,
            PermissionEnum::ViewAllWorkOrders,
            PermissionEnum::ViewWorkOrdersMenu,
            PermissionEnum::ViewMapOtMenu,
            PermissionEnum::ViewRequisitionsMenu,

            // Reportes
            PermissionEnum::AccessReports,
            PermissionEnum::ViewReports,
            PermissionEnum::ViewLowStock,
            PermissionEnum::ViewLowStockMenu,
            PermissionEnum::ViewMovementsReportMenu,
            PermissionEnum::ViewTechnicianPerformanceMenu,

            // Soporte / Tickets
            PermissionEnum::AccessSupport,
            PermissionEnum::ViewAnyTickets,
            PermissionEnum::CreateTickets,
            PermissionEnum::UpdateTickets,
            PermissionEnum::ViewNewTicketMenu,
            PermissionEnum::ViewAllTicketsMenu,

            // Clientes
            PermissionEnum::ViewClients,
            PermissionEnum::CreateClients,
            PermissionEnum::EditClients,
            PermissionEnum::DeleteClients,

            // SLA
            PermissionEnum::ViewSlaGoals,
            PermissionEnum::ViewSlaDashboard,

            // Dashboard
            PermissionEnum::ViewDashboard,
            PermissionEnum::CaptureCoordinates,
        ]);

        // ── Warehouse / Bodeguero ──
        $warehouse->syncPermissions([
            PermissionEnum::AccessInventory,
            PermissionEnum::ViewProducts,
            PermissionEnum::CreateProducts,
            PermissionEnum::EditProducts,
            PermissionEnum::ViewMovements,
            PermissionEnum::CreateMovements,
            PermissionEnum::ViewKardex,
            PermissionEnum::ViewProductsMenu,
            PermissionEnum::ViewMovementsMenu,
            PermissionEnum::ViewNewMovementMenu,
            PermissionEnum::ViewKardexMenu,

            PermissionEnum::AccessSuppliers,
            PermissionEnum::ViewSuppliers,
            PermissionEnum::CreateSuppliers,
            PermissionEnum::EditSuppliers,
            PermissionEnum::ViewPurchases,
            PermissionEnum::CreatePurchases,
            PermissionEnum::ViewSuppliersMenu,
            PermissionEnum::ViewPurchaseHistoryMenu,
            PermissionEnum::ViewNewPurchaseMenu,

            PermissionEnum::AccessTechnicians,
            PermissionEnum::ViewTechnicianReturns,
            PermissionEnum::CreateTechnicianReturns,
            PermissionEnum::ViewReturnsMenu,
            PermissionEnum::ViewRegisterReturnMenu,

            PermissionEnum::ViewWorkOrders,
            PermissionEnum::CreateWorkOrders,
            PermissionEnum::EditWorkOrders,
            PermissionEnum::DeleteWorkOrders,
            PermissionEnum::CompleteWorkOrders,
            PermissionEnum::AssignTechnicians,
            PermissionEnum::ViewWorkOrdersMenu,
            PermissionEnum::ViewMapOtMenu,
            PermissionEnum::ViewRequisitionsMenu,

            PermissionEnum::AccessReports,
            PermissionEnum::ViewReports,
            PermissionEnum::ViewLowStock,
            PermissionEnum::ViewLowStockMenu,
            PermissionEnum::ViewMovementsReportMenu,
            PermissionEnum::ViewTechnicianPerformanceMenu,

            PermissionEnum::AccessSupport,
            PermissionEnum::ViewNewTicketMenu,
            PermissionEnum::ViewAllTicketsMenu,
            PermissionEnum::ViewNocPanelMenu,

            PermissionEnum::ViewDashboard,
            PermissionEnum::ViewCatalog,
            PermissionEnum::ManageCatalog,
            PermissionEnum::CaptureCoordinates,
        ]);

        // ── Technician / Técnico de campo ──
        $technician->syncPermissions([
            PermissionEnum::AccessTechnicians,
            PermissionEnum::ViewWorkOrders,
            PermissionEnum::CreateWorkOrders,
            PermissionEnum::EditWorkOrders,
            PermissionEnum::CompleteWorkOrders,
            PermissionEnum::ViewOwnWorkOrders,
            PermissionEnum::ViewWorkOrdersMenu,
            PermissionEnum::ViewMapOtMenu,

            PermissionEnum::ViewRequisitions,
            PermissionEnum::CreateRequisitions,
            PermissionEnum::ViewRequisitionsMenu,

            PermissionEnum::ViewDashboard,
            PermissionEnum::ViewTechnicianDashboard,
            PermissionEnum::AccessMyDailyJobs,
            PermissionEnum::CaptureCoordinates,
        ]);

        // ── Accountant / Contador ──
        $accountant->syncPermissions([
            PermissionEnum::AccessReports,
            PermissionEnum::ViewReports,
            PermissionEnum::ViewProducts,
            PermissionEnum::ViewMovements,
            PermissionEnum::ViewKardex,
            PermissionEnum::ViewPurchases,
            PermissionEnum::ViewDashboard,
            PermissionEnum::ViewLowStock,
            PermissionEnum::ViewLowStockMenu,
            PermissionEnum::ViewMovementsReportMenu,
            PermissionEnum::ViewTechnicianPerformanceMenu,
        ]);

        // ── Buyer / Comprador ──
        $buyer->syncPermissions([
            PermissionEnum::AccessSuppliers,
            PermissionEnum::ViewProducts,
            PermissionEnum::ViewSuppliers,
            PermissionEnum::CreatePurchases,
            PermissionEnum::ViewSuppliersMenu,
            PermissionEnum::ViewNewPurchaseMenu,
        ]);

        // ── Atención al Cliente (SAC) ──
        $atencionCliente->syncPermissions([
            PermissionEnum::AccessSupport,
            PermissionEnum::ViewClients,
            PermissionEnum::CreateClients,
            PermissionEnum::EditClients,
            PermissionEnum::DeleteClients,
            PermissionEnum::ViewOwnTickets,
            PermissionEnum::CreateTickets,
            PermissionEnum::ViewOwnWorkOrders,
            PermissionEnum::ViewWorkOrders,
            PermissionEnum::CreateWorkOrders,
            PermissionEnum::EditWorkOrders,
            PermissionEnum::ViewNewTicketMenu,
            PermissionEnum::ViewAllTicketsMenu,
            PermissionEnum::ViewSlaDashboard,
            PermissionEnum::ViewDashboard,
        ]);

        // ── Contratos Inbox (solo admin por defecto; se asigna manualmente a quien corresponda) ──
        // (admin ya tiene todos los permisos)

        // ── NOC ──
        $noc->syncPermissions([
            PermissionEnum::AccessNocPanel,
            PermissionEnum::ViewPendingNocTickets,
            PermissionEnum::ViewResolutions,
            PermissionEnum::ViewNocPanelMenu,

            PermissionEnum::AccessSupport,
            PermissionEnum::ViewAnyTickets,
            PermissionEnum::ViewOwnTickets,
            PermissionEnum::CreateTickets,
            PermissionEnum::UpdateTickets,
            PermissionEnum::ViewNewTicketMenu,
            PermissionEnum::ViewAllTicketsMenu,

            PermissionEnum::AccessTechnicians,
            PermissionEnum::ViewAllWorkOrders,
            PermissionEnum::ViewOwnWorkOrders,
            PermissionEnum::ViewWorkOrders,
            PermissionEnum::CreateWorkOrders,
            PermissionEnum::EditWorkOrders,
            PermissionEnum::AssignTechnicians,
            PermissionEnum::ViewWorkOrdersMenu,
            PermissionEnum::ViewTechnicianDashboard,

            PermissionEnum::ViewDashboard,
            PermissionEnum::ViewMovements,
            PermissionEnum::ViewLowStock,
            PermissionEnum::ViewRequisitions,
        ]);

        // ── Field Supervisor / Supervisor de campo ──
        $fieldSupervisor->syncPermissions([
            PermissionEnum::AccessTechnicians,
            PermissionEnum::ViewAllWorkOrders,
            PermissionEnum::ViewOwnWorkOrders,
            PermissionEnum::ViewWorkOrders,
            PermissionEnum::CreateWorkOrders,
            PermissionEnum::EditWorkOrders,
            PermissionEnum::CompleteWorkOrders,
            PermissionEnum::AssignTechnicians,
            PermissionEnum::CancelWorkOrders,
            PermissionEnum::ViewWorkOrdersMenu,
            PermissionEnum::ViewMapOtMenu,

            PermissionEnum::ViewRequisitions,
            PermissionEnum::CreateRequisitions,
            PermissionEnum::AdjustRequisitions,
            PermissionEnum::ViewRequisitionsMenu,

            PermissionEnum::ViewTechnicianReturns,
            PermissionEnum::CreateTechnicianReturns,

            PermissionEnum::AccessReports,
            PermissionEnum::ViewReports,
            PermissionEnum::ViewLowStock,
            PermissionEnum::ViewLowStockMenu,
            PermissionEnum::ViewMovementsReportMenu,
            PermissionEnum::ViewTechnicianPerformanceMenu,

            PermissionEnum::AccessSupport,
            PermissionEnum::ViewNewTicketMenu,
            PermissionEnum::ViewAllTicketsMenu,
            PermissionEnum::ViewNocPanelMenu,

            PermissionEnum::ViewDashboard,
            PermissionEnum::ViewTechnicianDashboard,
            PermissionEnum::ViewCatalog,
            PermissionEnum::ManageCatalog,
            PermissionEnum::AccessMyDailyJobs,
            PermissionEnum::CaptureCoordinates,
            PermissionEnum::AssignSupervisorsToZones,
            PermissionEnum::ViewSlaDashboard,
        ]);

        // ── Sales Rep / Vendedor ──
        $salesRep->syncPermissions([
            PermissionEnum::AccessTechnicians,
            PermissionEnum::ViewWorkOrders,
            PermissionEnum::CreateWorkOrders,
            PermissionEnum::EditWorkOrders,
            PermissionEnum::ViewOwnWorkOrders,
            PermissionEnum::ViewWorkOrdersMenu,
            PermissionEnum::CaptureCoordinates,
        ]);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
