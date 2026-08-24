<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserRoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ThemeController;

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




