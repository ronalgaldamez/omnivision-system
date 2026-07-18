<?php

namespace App\Enums;

enum PermissionEnum: string
{
    // ─── Módulo Inventario ───
    case AccessInventory        = 'access_inventory';
    case ViewProducts           = 'view products';
    case CreateProducts         = 'create products';
    case EditProducts           = 'edit products';
    case DeleteProducts         = 'delete products';
    case ViewMovements          = 'view movements';
    case CreateMovements        = 'create movements';
    case ViewKardex             = 'view kardex';

    // ─── Submenús Inventario ───
    case ViewProductsMenu       = 'view_products_menu';
    case ViewMovementsMenu      = 'view_movements_menu';
    case ViewNewMovementMenu    = 'view_new_movement_menu';
    case ViewKardexMenu         = 'view_kardex_menu';

    // ─── Módulo Proveedores ───
    case AccessSuppliers        = 'access_suppliers';
    case ViewSuppliers          = 'view suppliers';
    case CreateSuppliers        = 'create suppliers';
    case EditSuppliers          = 'edit suppliers';
    case DeleteSuppliers        = 'delete suppliers';
    case ViewPurchases          = 'view purchases';
    case CreatePurchases        = 'create purchases';

    // ─── Submenús Proveedores ───
    case ViewSuppliersMenu      = 'view_suppliers_menu';
    case ViewPurchaseHistoryMenu = 'view_purchase_history_menu';
    case ViewNewPurchaseMenu    = 'view_new_purchase_menu';

    // ─── Módulo Órdenes de Trabajo ───
    case ViewWorkOrders         = 'view work_orders';
    case CreateWorkOrders       = 'create work_orders';
    case EditWorkOrders         = 'edit work_orders';
    case DeleteWorkOrders       = 'delete work_orders';
    case CompleteWorkOrders     = 'complete work_orders';
    case AssignTechnicians      = 'assign technicians';
    case CancelWorkOrders       = 'cancel work orders';
    case ViewAllWorkOrders      = 'view all work orders';
    case ViewOwnWorkOrders      = 'view own work_orders';

    // ─── Submenús Órdenes de Trabajo ───
    case ViewWorkOrdersMenu     = 'view_work_orders_menu';
    case ViewMapOtMenu          = 'view_map_ot_menu';

    // ─── Módulo Técnicos ───
    case AccessTechnicians      = 'access_technicians';
    case ViewTechnicianReturns  = 'view technician_returns';
    case CreateTechnicianReturns = 'create technician_returns';
    case AssignAnyTechnicianInReturns = 'assign any technician in returns';
    case ViewRequisitions       = 'view requisitions';
    case CreateRequisitions     = 'create requisitions';
    case AdjustRequisitions     = 'adjust requisitions';
    case ViewLowStock           = 'view low stock';

    // ─── Submenús Técnicos ───
    case ViewReturnsMenu        = 'view_returns_menu';
    case ViewRegisterReturnMenu = 'view_register_return_menu';
    case ViewRequisitionsMenu   = 'view_requisitions_menu';
    case ViewLowStockMenu       = 'view_low_stock_menu';

    // ─── Módulo Reportes ───
    case AccessReports          = 'access_reports';
    case ViewReports            = 'view reports';

    // ─── Submenús Reportes ───
    case ViewMovementsReportMenu = 'view_movements_report_menu';
    case ViewTechnicianPerformanceMenu = 'view_technician_performance_menu';

    // ─── Módulo Soporte (Tickets) ───
    case AccessSupport          = 'access_support';
    case ViewTickets            = 'view tickets';
    case CreateTickets          = 'create tickets';
    case EditTickets            = 'edit tickets';
    case ViewAnyTickets         = 'view any tickets';
    case ViewOwnTickets         = 'view own tickets';
    case UpdateTickets          = 'update tickets';
    case DeleteTickets          = 'delete tickets';

    // ─── Submenús Soporte ───
    case ViewNewTicketMenu      = 'view_new_ticket_menu';
    case ViewAllTicketsMenu     = 'view_all_tickets_menu';

    // ─── Panel NOC ───
    case AccessNocPanel         = 'access noc panel';
    case ViewPendingNocTickets  = 'view pending noc tickets';
    case ViewResolutions        = 'view resolutions';
    case ViewNocPanelMenu       = 'view_noc_panel_menu';

    // ─── Módulo Clientes ───
    case ViewClients            = 'view clients';
    case CreateClients          = 'create clients';
    case EditClients            = 'edit clients';
    case DeleteClients          = 'delete clients';

    // ─── Módulo Administración ───
    case AccessAdmin            = 'access_admin';
    case ViewDashboard          = 'view dashboard';
    case ViewCatalog            = 'view catalog';
    case ManageCatalog          = 'manage catalog';

    // ─── Submenús Administración ───
    case ViewUsersMenu          = 'view_users_menu';
    case ViewRolesMenu          = 'view_roles_menu';
    case ViewCatalogMenu        = 'view_catalog_menu';
    case ViewSettingsMenu       = 'view_settings_menu';

    // ─── SLA ───
    case ViewSlaGoals           = 'view sla goals';
    case CreateSlaGoals         = 'create sla goals';
    case EditSlaGoals           = 'edit sla goals';
    case DeleteSlaGoals         = 'delete sla goals';
    case ViewSlaDashboard       = 'view sla dashboard';

    // ─── Override de toggles en ticket ───
    case ManageRequiresNocToggle     = 'manage_requires_noc_toggle';
    case ManageCreateOtToggle        = 'manage_create_ot_toggle';
    case ManageRequiresContractToggle = 'manage_requires_contract_toggle';

    // ─── Contratos ───
    case AccessContractsInbox   = 'access_contracts_inbox';
    case ViewContractsPanelMenu = 'view_contracts_panel_menu';

    // ─── Otros ───
    case CaptureCoordinates     = 'capture coordinates';
    case AccessMyDailyJobs      = 'access my daily jobs';
    case ViewTechnicianDashboard = 'view technician dashboard';
    case AssignSupervisorsToZones = 'assign supervisors to zones';

    // ═══════════════════════════════════════════════════
    // Métodos de agrupación por módulo para la Matriz
    // ═══════════════════════════════════════════════════

    /**
     * Todos los permisos del módulo Inventario.
     */
    public static function inventory(): array
    {
        return [
            self::AccessInventory,
            self::ViewProducts,
            self::CreateProducts,
            self::EditProducts,
            self::DeleteProducts,
            self::ViewMovements,
            self::CreateMovements,
            self::ViewKardex,
            self::ViewProductsMenu,
            self::ViewMovementsMenu,
            self::ViewNewMovementMenu,
            self::ViewKardexMenu,
        ];
    }

    /**
     * Todos los permisos del módulo Proveedores.
     */
    public static function suppliers(): array
    {
        return [
            self::AccessSuppliers,
            self::ViewSuppliers,
            self::CreateSuppliers,
            self::EditSuppliers,
            self::DeleteSuppliers,
            self::ViewPurchases,
            self::CreatePurchases,
            self::ViewSuppliersMenu,
            self::ViewPurchaseHistoryMenu,
            self::ViewNewPurchaseMenu,
        ];
    }

    /**
     * Todos los permisos del módulo Órdenes de Trabajo.
     */
    public static function workOrders(): array
    {
        return [
            self::ViewWorkOrders,
            self::CreateWorkOrders,
            self::EditWorkOrders,
            self::DeleteWorkOrders,
            self::CompleteWorkOrders,
            self::AssignTechnicians,
            self::CancelWorkOrders,
            self::ViewAllWorkOrders,
            self::ViewOwnWorkOrders,
            self::ViewWorkOrdersMenu,
            self::ViewMapOtMenu,
        ];
    }

    /**
     * Todos los permisos del módulo Técnicos.
     */
    public static function technicians(): array
    {
        return [
            self::AccessTechnicians,
            self::ViewTechnicianReturns,
            self::CreateTechnicianReturns,
            self::AssignAnyTechnicianInReturns,
            self::ViewRequisitions,
            self::CreateRequisitions,
            self::AdjustRequisitions,
            self::ViewLowStock,
            self::ViewReturnsMenu,
            self::ViewRegisterReturnMenu,
            self::ViewRequisitionsMenu,
            self::ViewLowStockMenu,
        ];
    }

    /**
     * Todos los permisos del módulo Reportes.
     */
    public static function reports(): array
    {
        return [
            self::AccessReports,
            self::ViewReports,
            self::ViewMovementsReportMenu,
            self::ViewTechnicianPerformanceMenu,
        ];
    }

    /**
     * Todos los permisos del módulo Soporte / Tickets.
     */
    public static function support(): array
    {
        return [
            self::AccessSupport,
            self::ViewTickets,
            self::CreateTickets,
            self::EditTickets,
            self::ViewAnyTickets,
            self::ViewOwnTickets,
            self::UpdateTickets,
            self::DeleteTickets,
            self::ViewNewTicketMenu,
            self::ViewAllTicketsMenu,
            self::ManageRequiresNocToggle,
            self::ManageCreateOtToggle,
            self::ManageRequiresContractToggle,
        ];
    }

    /**
     * Permisos del panel NOC.
     */
    public static function noc(): array
    {
        return [
            self::AccessNocPanel,
            self::ViewPendingNocTickets,
            self::ViewResolutions,
            self::ViewNocPanelMenu,
        ];
    }

    /**
     * Todos los permisos del módulo Clientes.
     */
    public static function clients(): array
    {
        return [
            self::ViewClients,
            self::CreateClients,
            self::EditClients,
            self::DeleteClients,
        ];
    }

    /**
     * Todos los permisos del módulo Administración.
     */
    public static function admin(): array
    {
        return [
            self::AccessAdmin,
            self::ViewDashboard,
            self::ViewCatalog,
            self::ManageCatalog,
            self::ViewUsersMenu,
            self::ViewRolesMenu,
            self::ViewCatalogMenu,
            self::ViewSettingsMenu,
        ];
    }

    /**
     * Todos los permisos del módulo SLA.
     */
    public static function sla(): array
    {
        return [
            self::ViewSlaGoals,
            self::CreateSlaGoals,
            self::EditSlaGoals,
            self::DeleteSlaGoals,
            self::ViewSlaDashboard,
        ];
    }

    /**
     * Permisos del módulo Contratos.
     */
    public static function contracts(): array
    {
        return [
            self::AccessContractsInbox,
            self::ViewContractsPanelMenu,
        ];
    }

    /**
     * Permisos de campo / móvil.
     */
    public static function field(): array
    {
        return [
            self::CaptureCoordinates,
            self::AccessMyDailyJobs,
            self::ViewTechnicianDashboard,
        ];
    }

    /**
     * Permisos de supervisor de zona.
     */
    public static function supervisor(): array
    {
        return [
            self::AssignSupervisorsToZones,
        ];
    }

    /**
     * Devuelve todos los permisos como array de strings para el seeder.
     */
    public static function allPermissions(): array
    {
        return array_map(fn(self $case) => $case->value, self::cases());
    }
}
