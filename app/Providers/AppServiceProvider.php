<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Blade;
use App\Models\User;
use App\Models\Permission;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // Super Admin Bypass Gate Check
        Gate::before(function (User $user, $ability) {
            if ($user->isSuperAdmin()) {
                return true;
            }
        });

        // Dynamic Gate Definition for All Permissions
        try {
            if (Schema::hasTable('permissions')) {
                Permission::active()->get()->each(function ($permission) {
                    Gate::define($permission->slug, function (User $user) use ($permission) {
                        return $user->hasPermission($permission->slug);
                    });
                });
            }
        } catch (\Throwable $e) {
            // Silence exception during early setup / migration phases
        }

        // Custom Blade Directives for Permissions & Roles
        Blade::directive('permission', function ($expression) {
            return "<?php if(auth()->check() && auth()->user()->hasPermission({$expression})): ?>";
        });

        Blade::directive('endpermission', function () {
            return "<?php endif; ?>";
        });

        Blade::directive('role', function ($expression) {
            return "<?php if(auth()->check() && auth()->user()->hasRole({$expression})): ?>";
        });

        Blade::directive('endrole', function () {
            return "<?php endif; ?>";
        });
    }
}
