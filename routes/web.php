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

// Public Storage File Route (Serves avatars & uploaded files reliably across Artisan Serve & Windows WAMP)
Route::get('/storage/{path}', function ($path) {
    $filePath = storage_path('app/public/' . $path);
    if (!file_exists($filePath)) {
        abort(404);
    }
    return response()->file($filePath);
})->where('path', '.*')->name('storage.file');

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




