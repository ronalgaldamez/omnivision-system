<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Http\Controllers\Auth\LogoutController;

// Ruta raíz redirige a login
Route::get('/', function () {
    return redirect()->route('login');
});

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/register', Register::class)->name('register');
});

// Protected routes
Route::middleware(['auth'])->group(function () {
    // Dashboard (acceso para todos los autenticados, sin middleware adicional)
    Route::get('/dashboard', \App\Livewire\Reports\Dashboard::class)->name('dashboard');
    Route::post('/logout', LogoutController::class)->name('logout');
    Route::get('/profile', \App\Livewire\Profile::class)->name('profile');

    // ========== INVENTORY ==========
    if (module_active('inventory')) {
        Route::prefix('products')->middleware('can:view products')->group(function () {
            Route::get('/', \App\Livewire\Inventory\ProductIndex::class)->name('products.index');
            Route::get('/{id}/show', \App\Livewire\Inventory\ProductShow::class)->name('products.show');
        });
        Route::prefix('products')->middleware('can:create products')->group(function () {
            Route::get('/create', \App\Livewire\Inventory\ProductForm::class)->name('products.create');
        });
        Route::prefix('products')->middleware('can:edit products')->group(function () {
            Route::get('/{id}/edit', \App\Livewire\Inventory\ProductForm::class)->name('products.edit');
        });
        Route::middleware('can:view movements')->group(function () {
            Route::get('/movements', \App\Livewire\Inventory\MovementIndex::class)->name('movements.index');
        });
        Route::middleware('can:create movements')->group(function () {
            Route::get('/movements/create', \App\Livewire\Inventory\MovementForm::class)->name('movements.create');
        });
        Route::middleware('can:view kardex')->group(function () {
            Route::get('/kardex', \App\Livewire\Inventory\KardexIndex::class)->name('kardex.index');
        });

    }

    // ========== SUPPLIERS ==========
    if (module_active('suppliers')) {
        Route::prefix('suppliers')->middleware('can:view suppliers')->group(function () {
            Route::get('/', \App\Livewire\Suppliers\SupplierIndex::class)->name('suppliers.index');
            Route::get('/{id}/show', \App\Livewire\Suppliers\SupplierShow::class)->name('suppliers.show');
        });
        Route::prefix('suppliers')->middleware('can:create suppliers')->group(function () {
            Route::get('/create', \App\Livewire\Suppliers\SupplierForm::class)->name('suppliers.create');
        });
        Route::prefix('suppliers')->middleware('can:edit suppliers')->group(function () {
            Route::get('/{id}/edit', \App\Livewire\Suppliers\SupplierForm::class)->name('suppliers.edit');
        });

        Route::middleware('can:view purchases')->group(function () {
            Route::get('/purchases', \App\Livewire\Suppliers\PurchaseIndex::class)->name('purchases.index');
            Route::get('/purchases/{id}/show', \App\Livewire\Suppliers\PurchaseShow::class)->name('purchases.show');
        });
        Route::middleware('can:create purchases')->group(function () {
            Route::get('/purchases/create', \App\Livewire\Suppliers\PurchaseForm::class)->name('purchases.create');
            Route::get('/returns/create', \App\Livewire\Suppliers\ReturnToSupplierForm::class)->name('returns.create');
        });
        Route::get('/returns', function () {
            return redirect()->route('products.index');
        })->name('returns.index');
    }

    // ========== REQUISITIONS ==========
    Route::prefix('requisitions')->middleware(['auth'])->group(function () {
        Route::middleware('can:view requisitions')->group(function () {
            Route::get('/', \App\Livewire\Technicians\RequisitionIndex::class)->name('technician.requisitions.index');
            Route::get('/{id}/show', \App\Livewire\Technicians\RequisitionDetail::class)->name('technician.requisitions.show');
        });
        Route::middleware('can:create requisitions')->group(function () {
            Route::get('/create', \App\Livewire\Technicians\RequisitionForm::class)->name('technician.requisitions.create');
            Route::post('/{id}/close', \App\Livewire\Technicians\RequisitionDetail::class)->name('technician.requisitions.close');
        });
    });

    // ========== TECHNICIANS MOBILE ==========
    Route::prefix('mobile/technician')->middleware(['auth'])->group(function () {
        Route::get('/work-orders', \App\Livewire\Mobile\WorkOrderList::class)
            ->middleware('can:access my daily jobs')
            ->name('mobile.work-orders.list');
        Route::get('/work-orders/{id}', \App\Livewire\Mobile\WorkOrderShow::class)
            ->middleware('can:access my daily jobs')
            ->name('mobile.work-orders.show');
        Route::get('/work-orders-map', \App\Livewire\Mobile\WorkOrderMap::class)
            ->middleware('can:view_map_ot_menu')
            ->name('mobile.work-orders.map');
    });

    // ========== WORK ORDERS ==========
    if (module_active('work_orders')) {
        Route::get('/work-orders', \App\Livewire\WorkOrders\WorkOrderIndex::class)->name('work-orders.index');
        Route::get('/work-orders/{id}/show', \App\Livewire\WorkOrders\WorkOrderShow::class)->name('work-orders.show');
        Route::get('/work-orders/map', \App\Livewire\WorkOrders\WorkOrderMap::class)->name('work-orders.map');

        Route::middleware('can:create work_orders')->group(function () {
            Route::get('/work-orders/create', \App\Livewire\WorkOrders\WorkOrderForm::class)->name('work-orders.create');
        });
        Route::middleware('can:edit work_orders')->group(function () {
            Route::get('/work-orders/{id}/edit', \App\Livewire\WorkOrders\WorkOrderForm::class)->name('work-orders.edit');
        });

        // TICKETS
        Route::prefix('tickets')->group(function () {
            Route::get('/', \App\Livewire\Tickets\TicketIndex::class)->name('tickets.index');
            Route::get('/create', \App\Livewire\Tickets\TicketForm::class)->name('tickets.create');
        });
    }

    // ========== CONTRACTS ==========
    Route::get('/contracts', \App\Livewire\Contracts\ContractIndex::class)->name('contracts.index');
    Route::get('/contracts/create', \App\Livewire\Contracts\ContractForm::class)->name('contracts.create');
    Route::get('/contracts/workflow/{ticket_id}', \App\Livewire\Contracts\ContractWorkflow::class)->name('contracts.workflow');
    Route::middleware(['can:access_contracts_inbox'])->group(function () {
        Route::get('/contratos/inbox', \App\Livewire\Contracts\ContractInbox::class)->name('contracts.inbox');
    });

    // ========== NOC ==========
    Route::middleware(['auth', 'can:access noc panel'])->group(function () {
        Route::get('/noc', \App\Livewire\Noc\NocInbox::class)->name('noc.panel');
    });

    // ========== BODEGA ==========
    Route::prefix('bodega')->middleware(['auth'])->group(function () {
        Route::get('/requisitions', \App\Livewire\Bodega\RequisitionBodegaIndex::class)->name('bodega.requisitions.index');
        Route::get('/shipments', \App\Livewire\Bodega\DistributionIndex::class)->name('bodega.shipments.index');
        Route::get('/shipments/create', \App\Livewire\Bodega\DistributionCreate::class)->name('bodega.shipments.create');
        Route::get('/shipments/{id}', \App\Livewire\Bodega\DistributionShow::class)->name('bodega.shipments.show');
        Route::get('/receive/{code?}', \App\Livewire\Bodega\DistributionReceive::class)->name('bodega.shipments.receive');
    });

    // ========== TECHNICIAN RETURNS ==========
    if (module_active('technician_returns') && module_active('technicians')) {
        Route::prefix('technician-returns')->middleware('can:view technician_returns')->group(function () {
            Route::get('/', \App\Livewire\TechnicianReturns\ReturnList::class)->name('technician-returns.index');
        });
        Route::prefix('technician-returns')->middleware('can:create technician_returns')->group(function () {
            Route::get('/create', \App\Livewire\TechnicianReturns\ReturnForm::class)->name('technician-returns.create');
        });
    }

    // ========== REPORTS ==========
    if (module_active('reports')) {
        Route::middleware('can:view reports')->group(function () {
            Route::get('/reports/stock', \App\Livewire\Reports\StockReport::class)->name('reports.stock');
            Route::get('/reports/movements', \App\Livewire\Reports\MovementsReport::class)->name('reports.movements');
            Route::get('/reports/technicians', \App\Livewire\Reports\TechnicianPerformance::class)->name('reports.technicians');
        });
    }
    
    // ========== CLIENTS ==========
    Route::prefix('admin/clients')->middleware(['auth'])->group(function () {
        // Rutas fijas primero
        Route::middleware('can:create clients')->group(function () {
            Route::get('/create', \App\Livewire\Admin\Clients\ClientForm::class)->name('admin.clients.create');
        });

        // Ruta de edición (contiene barra, no compite con /{id})
        Route::middleware('can:edit clients')->group(function () {
            Route::get('/{id}/edit', \App\Livewire\Admin\Clients\ClientForm::class)->name('admin.clients.edit');
        });

        // Ruta de índice (sin parámetro)
        Route::middleware('can:view clients')->group(function () {
            Route::get('/', \App\Livewire\Admin\Clients\ClientIndex::class)->name('admin.clients.index');
        });

        // Ruta de show (con parámetro) DEBE IR AL FINAL
        Route::middleware('can:view clients')->group(function () {
            Route::get('/{id}', \App\Livewire\Admin\Clients\ClientShow::class)->name('admin.clients.show');
        });
    });

    // ========== DEVICES ==========
    Route::prefix('devices')->middleware('can:access_inventory')->group(function () {
        Route::get('/', \App\Livewire\Inventory\Devices\DeviceIndex::class)->name('devices.index');
        Route::get('/register', \App\Livewire\Inventory\Devices\DeviceRegister::class)->name('devices.register');
        Route::get('/{id}/show', \App\Livewire\Inventory\Devices\DeviceShow::class)->name('devices.show');
    });

    // ========== ADMIN ==========
    Route::prefix('admin/branches')->middleware(['auth', 'can:access_admin'])->group(function () {
        Route::get('/', \App\Livewire\Admin\Branches\BranchIndex::class)->name('admin.branches.index');
        Route::get('/create', \App\Livewire\Admin\Branches\BranchForm::class)->name('admin.branches.create');
        Route::get('/{id}/edit', \App\Livewire\Admin\Branches\BranchForm::class)->name('admin.branches.edit');
    });
    Route::prefix('admin/users')->middleware(['auth', 'can:access_admin'])->group(function () {
        Route::get('/', \App\Livewire\Admin\Users\UserIndex::class)->name('admin.users.index');
        Route::get('/create', \App\Livewire\Admin\Users\UserCreate::class)->name('admin.users.create');
        Route::get('/{id}/edit', \App\Livewire\Admin\Users\UserForm::class)->name('admin.users.edit');
    });
    Route::prefix('admin/roles')->middleware(['auth', 'can:access_admin'])->group(function () {
        Route::get('/', \App\Livewire\Admin\Roles\RoleIndex::class)->name('admin.roles.index');
        Route::get('/create', \App\Livewire\Admin\Roles\RoleForm::class)->name('admin.roles.create');
        Route::get('/{id}/edit', \App\Livewire\Admin\Roles\RoleForm::class)->name('admin.roles.edit');
    });
    Route::prefix('admin/settings')->middleware(['auth', 'can:access_admin'])->group(function () {
        Route::get('/', \App\Livewire\Admin\SettingsManager::class)->name('admin.settings');
    });
    Route::prefix('admin/imports')->middleware(['auth', 'can:access_admin'])->group(function () {
        Route::get('/', \App\Livewire\Imports\ProductImport::class)->name('admin.imports.products');
    });
    Route::prefix('admin/catalog')->middleware(['auth', 'can:manage catalog'])->group(function () {
        Route::get('/', \App\Livewire\Admin\Catalog\CatalogManager::class)->name('admin.catalog');
    });
    Route::prefix('admin/plans')->middleware(['auth', 'can:manage catalog'])->group(function () {
        Route::get('/', \App\Livewire\Admin\Plans\PlanManager::class)->name('admin.plans');
    });
    Route::prefix('admin/shelves')->middleware(['auth', 'can:access_admin'])->group(function () {
        Route::get('/', \App\Livewire\Admin\ShelvesManager::class)->name('admin.shelves');
    });

    Route::prefix('admin/supervisor-zones')->middleware(['auth', 'can:assign supervisors to zones'])->group(function () {
        Route::get('/', \App\Livewire\Admin\SupervisorZones\SupervisorZoneManager::class)->name('admin.supervisor-zones');
    });
    Route::prefix('admin/vehiculos')->middleware(['auth', 'can:access_admin'])->group(function () {
        Route::get('/', \App\Livewire\Supervisor\VehiculoManager::class)->name('admin.vehiculos');
    });
    Route::prefix('admin/asignaciones')->middleware(['auth', 'can:access_admin'])->group(function () {
        Route::get('/', \App\Livewire\Supervisor\AsignacionManager::class)->name('admin.asignaciones');
    });

    // ========== UI COMPONENTS PREVIEW ==========
    Route::middleware(['auth', 'can:access_admin'])->get('/admin/ui-preview', function () {
        return view('components.ui-preview');
    })->name('admin.ui-preview');

    // ========== SLA ==========
    Route::prefix('sla')->middleware(['auth'])->group(function () {
        Route::middleware('can:view sla goals')->group(function () {
            Route::get('/goals', \App\Livewire\Admin\Sla\SlaGoalIndex::class)->name('admin.sla.goals.index');
        });
        Route::middleware('can:create sla goals')->group(function () {
            Route::get('/goals/create', \App\Livewire\Admin\Sla\SlaGoalForm::class)->name('admin.sla.goals.create');
        });
        Route::middleware('can:edit sla goals')->group(function () {
            Route::get('/goals/{id}/edit', \App\Livewire\Admin\Sla\SlaGoalForm::class)->name('admin.sla.goals.edit');
        });
    });

    // ========== SLA DASHBOARD ==========
    Route::middleware(['auth', 'can:view sla dashboard'])->group(function () {
        Route::get('/sla/dashboard', \App\Livewire\Sla\SlaDashboard::class)->name('sla.dashboard');
    });

    // ========== SLA TIMELINES ==========
    Route::middleware(['auth'])->group(function () {
        Route::get('/sla/tickets/{id}/timeline', \App\Livewire\Sla\TicketTimeline::class)->name('sla.ticket-timeline');
        Route::get('/sla/work-orders/{id}/timeline', \App\Livewire\Sla\WorkOrderTimeline::class)->name('sla.work-order-timeline');
    });
});

// ========== RUTAS PÚBLICAS (sin autenticación) ==========
Route::get('/contratos/firmar/{token}', \App\Livewire\Public\SignContract::class)->name('public.contract.sign');
Route::get('/contratos/coordenadas/{token}', \App\Livewire\Public\CaptureCoordinates::class)->name('public.contract.coordinates');
Route::get('/contratos/documentos/{token}', \App\Livewire\Public\UploadDocuments::class)->name('public.contract.documents');
