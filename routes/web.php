<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Dashboard;
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
    Route::get('/dashboard', \App\Livewire\Reports\Dashboard::class)->name('dashboard');
    Route::post('/logout', LogoutController::class)->name('logout');

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

    // ========== TECHNICIANS (escritorio) ==========
    if (module_active('technicians')) {
        Route::prefix('technician-requests')->middleware('can:view technician_requests')->group(function () {
            Route::get('/', \App\Livewire\Technicians\RequestList::class)->name('technician-requests.index');
        });
        Route::prefix('technician-requests')->middleware('can:approve technician_requests')->group(function () {
            Route::get('/{id}/approve', \App\Livewire\Technicians\RequestApprovalForm::class)->name('technician-requests.approve');
        });
        Route::middleware('can:approve technician_requests')->group(function () {
            Route::get('/code-delivery', \App\Livewire\Technicians\CodeDeliveryForm::class)->name('code-delivery.index');
        });
    }

    // ========== TECHNICIANS MOBILE ==========
    Route::prefix('mobile/technician')->middleware(['auth', 'role:technician', 'can:create technician_requests'])->group(function () {
        Route::get('/requests', \App\Livewire\Mobile\Technician\RequestList::class)->name('mobile.technician.requests');
        Route::get('/requests/create/{work_order_id?}', \App\Livewire\Mobile\Technician\RequestForm::class)->name('mobile.technician.requests.create');
        Route::get('/requests/{id}/edit', \App\Livewire\Mobile\Technician\RequestForm::class)->name('mobile.technician.requests.edit');
        Route::get('/work-orders/{id}', \App\Livewire\Mobile\WorkOrderShow::class)->name('mobile.work-orders.show');
        Route::get('/work-orders', \App\Livewire\Mobile\WorkOrderList::class)->name('mobile.work-orders.list');
        Route::get('/work-orders-map', \App\Livewire\Mobile\WorkOrderMap::class)->name('mobile.work-orders.map');
    });

    // ========== WORK ORDERS ==========
    if (module_active('work_orders')) {
        Route::prefix('work-orders')->middleware('can:view work_orders')->group(function () {
            Route::get('/', \App\Livewire\WorkOrders\WorkOrderIndex::class)->name('work-orders.index');
            Route::get('/{id}/show', \App\Livewire\WorkOrders\WorkOrderShow::class)->name('work-orders.show');
            Route::get('/map', \App\Livewire\WorkOrders\WorkOrderMap::class)->name('work-orders.map');
        });
        Route::prefix('work-orders')->middleware('can:create work_orders')->group(function () {
            Route::get('/create', \App\Livewire\WorkOrders\WorkOrderForm::class)->name('work-orders.create');
        });
        Route::prefix('work-orders')->middleware('can:edit work_orders')->group(function () {
            Route::get('/{id}/edit', \App\Livewire\WorkOrders\WorkOrderForm::class)->name('work-orders.edit');
        });

        // ========== TICKETS ==========
        Route::prefix('tickets')->middleware('can:view tickets')->group(function () {
            Route::get('/', \App\Livewire\Tickets\TicketIndex::class)->name('tickets.index');
            Route::get('/create', \App\Livewire\Tickets\TicketForm::class)->name('tickets.create');
        });
    }

    // ========== NOC ==========
    Route::middleware(['auth', 'can:edit tickets'])->group(function () {
        Route::get('/noc', \App\Livewire\Noc\NocPanel::class)->name('noc.panel');
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
        Route::middleware('can:view dashboard')->group(function () {
            Route::get('/dashboard', \App\Livewire\Reports\Dashboard::class)->name('dashboard');
        });
        Route::middleware('can:view reports')->group(function () {
            Route::get('/reports/stock', \App\Livewire\Reports\StockReport::class)->name('reports.stock');
            Route::get('/reports/movements', \App\Livewire\Reports\MovementsReport::class)->name('reports.movements');
            Route::get('/reports/technicians', \App\Livewire\Reports\TechnicianPerformance::class)->name('reports.technicians');
        });
    }

    // ========== ADMIN ==========
    Route::prefix('admin/users')->middleware(['auth', 'role:admin'])->group(function () {
        Route::get('/', \App\Livewire\Admin\Users\UserIndex::class)->name('admin.users.index');
        Route::get('/create', \App\Livewire\Admin\Users\UserCreate::class)->name('admin.users.create');
        Route::get('/{id}/edit', \App\Livewire\Admin\Users\UserForm::class)->name('admin.users.edit');
    });
    Route::prefix('admin/roles')->middleware(['auth', 'role:admin'])->group(function () {
        Route::get('/', \App\Livewire\Admin\Roles\RoleIndex::class)->name('admin.roles.index');
        Route::get('/create', \App\Livewire\Admin\Roles\RoleForm::class)->name('admin.roles.create');
        Route::get('/{id}/edit', \App\Livewire\Admin\Roles\RoleForm::class)->name('admin.roles.edit');
        Route::get('/admin/settings', \App\Livewire\Admin\SettingsManager::class)->name('admin.settings');
    });
    Route::prefix('admin/catalog')->middleware(['auth', 'can:manage catalog'])->group(function () {
        Route::get('/', \App\Livewire\Admin\Catalog\CatalogManager::class)->name('admin.catalog');
    });
});