<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

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
        // Blade directive: @canPermission('permission.slug')
        Blade::if('canPermission', function ($permissionSlug) {
            $user = auth()->user();
            return $user && $user->hasPermission($permissionSlug);
        });

        // Blade directive: @canAnyPermission(['permission1', 'permission2'])
        Blade::if('canAnyPermission', function (array $permissionSlugs) {
            $user = auth()->user();
            return $user && $user->hasAnyPermission($permissionSlugs);
        });
    }
}
