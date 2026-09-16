<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserRoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ThemeController;
use App\Http\Controllers\Sales\SalesOrderController;
use App\Http\Controllers\Purchase\PurchaseOrderController;
use App\Http\Controllers\Accounting\InvoiceController;
use App\Http\Controllers\Accounting\VoucherController;
use App\Http\Controllers\Accounting\ReportController;
use App\Http\Controllers\Accounting\LedgerReportController;
use App\Http\Controllers\StorageController;

// Public Storage File Route (Serves avatars & uploaded files reliably across Artisan Serve & Windows WAMP)
Route::get('/storage/{path}', [StorageController::class, 'show'])->where('path', '.*')->name('storage.file');

// Auth Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Customer Sale Orders Routes
    Route::get('/sale-order-list', [SalesOrderController::class, 'index'])->name('sales.orders');
    Route::get('/sale-order-details/{slid}', [SalesOrderController::class, 'showDetails'])->name('sales.order.details');
    Route::get('/sales/customers', [SalesOrderController::class, 'getCustomers'])->name('sales.customers');
    Route::get('/sales/orders', [SalesOrderController::class, 'getOrders'])->name('sales.orders.data');
    Route::get('/sales/order-details', [SalesOrderController::class, 'getOrderDetails'])->name('sales.order-details.data');
    Route::get('/sales/dispatches', [SalesOrderController::class, 'getDispatches'])->name('sales.dispatches');
    Route::get('/sales/summary', [SalesOrderController::class, 'getSummary'])->name('sales.summary');

    // Dispatch Sales Invoicing Routes
    Route::get('/dispatch-invoicing', [InvoiceController::class, 'index'])->name('sales.dispatch-invoicing');
    Route::get('/invoices/pending-vehicles', [InvoiceController::class, 'getPendingVehicles'])->name('invoices.pending-vehicles');
    Route::get('/invoices/dispatch-details', [InvoiceController::class, 'getDispatchDetails'])->name('invoices.dispatch-details');
    Route::get('/invoices/next-number', [InvoiceController::class, 'getNextInvoiceNumber'])->name('invoices.next-number');
    Route::get('/invoices/sale-order-dispatches', [InvoiceController::class, 'getSaleOrderDispatches'])->name('invoices.sale-order-dispatches');
    Route::post('/invoices/store', [InvoiceController::class, 'store'])->name('invoices.store');
    Route::get('/invoices/print/{id}', [InvoiceController::class, 'printInvoice'])->name('invoices.print');
    Route::post('/invoices/generate', [InvoiceController::class, 'invoiceGenerate'])->name('invoices.generate');
    Route::post('/invoices/share/email', [InvoiceController::class, 'shareEmail'])->name('invoices.share.email');
    Route::post('/invoices/share/whatsapp', [InvoiceController::class, 'shareWhatsapp'])->name('invoices.share.whatsapp');
    Route::get('/invoices/share/details', [InvoiceController::class, 'getShareDetails'])->name('invoices.share.details');

    // Accounting Vouchers Routes
    Route::prefix('vouchers')->name('vouchers.')->group(function () {
        Route::get('/', [VoucherController::class, 'index'])->name('index');
        Route::get('/data', [VoucherController::class, 'data'])->name('data');
        Route::get('/next-number', [VoucherController::class, 'getNextVoucherNumber'])->name('next-number');
        Route::get('/parties', [VoucherController::class, 'getParties'])->name('parties');
        Route::get('/party-bill', [VoucherController::class, 'getPartyBill'])->name('party-bill');
        Route::get('/ledgers', [VoucherController::class, 'getLedgers'])->name('ledgers');
        Route::post('/', [VoucherController::class, 'store'])->name('store');
        Route::get('/{id}', [VoucherController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [VoucherController::class, 'edit'])->name('edit');
        Route::put('/{id}', [VoucherController::class, 'update'])->name('update');
        Route::delete('/{id}', [VoucherController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/print', [VoucherController::class, 'print'])->name('print');
    });
    Route::get('/accounting/vouchers', function () {
        return redirect()->route('vouchers.index');
    });

    // Accounting Reports Routes
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/data', [ReportController::class, 'data'])->name('data');
        Route::get('/export', [ReportController::class, 'export'])->name('export');
    });

    // Accounting General Ledger Statement Routes
    Route::prefix('ledger')->name('ledger.')->group(function () {
        Route::get('/', [LedgerReportController::class, 'index'])->name('index');
        Route::get('/data', [LedgerReportController::class, 'data'])->name('data');
        Route::get('/print', [LedgerReportController::class, 'print'])->name('print');
        Route::get('/export', [LedgerReportController::class, 'export'])->name('export');
    });

    // Customer Purchase Orders Routes
    Route::get('/purchase-order-list', [PurchaseOrderController::class, 'index'])->name('purchase.orders');
    Route::get('/purchase-order-details/{poid}', [PurchaseOrderController::class, 'showDetails'])->name('purchase.order.details');
    Route::get('/purchase/customers', [PurchaseOrderController::class, 'getCustomers'])->name('purchase.customers');
    Route::get('/purchase/orders', [PurchaseOrderController::class, 'getOrders'])->name('purchase.orders.data');
    Route::get('/purchase/order-details', [PurchaseOrderController::class, 'getOrderDetails'])->name('purchase.order-details.data');
    Route::get('/purchase/received-material', [PurchaseOrderController::class, 'getReceivedMaterial'])->name('purchase.received-material');
    Route::get('/purchase/summary', [PurchaseOrderController::class, 'getSummary'])->name('purchase.summary');

    // Profile & Preferences Routes
    Route::post('/theme/update', [ThemeController::class, 'updateTheme'])->name('theme.update');
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile/update', [ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/avatar/remove', [ProfileController::class, 'removeAvatar'])->name('profile.avatar.remove');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.updatePassword');

    // Dynamic Roles & Permissions Management Routes
    Route::resource('roles', RoleController::class);

    // User Management Routes
    Route::get('/api/roles/{role}/permissions', [UserController::class, 'getRolePermissions'])->name('api.roles.permissions');
    Route::resource('users', UserController::class);

    // User Role Assignment Routes
    Route::get('/users/roles/assignment', [UserRoleController::class, 'index'])->name('users.roles');
    Route::post('/users/roles/assignment', [UserRoleController::class, 'updateUserRoles'])->name('users.roles.update');
});




