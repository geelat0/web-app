<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
        // $this->registerPolicies();

        Gate::define('view-organizational-outcome', function () {
            return Auth::user()->role->hasPermission('manage_organizational_outcome') || Auth::user()->role->name === 'IT' ||  Auth::user()->role->name === 'SAP';
        });

        Gate::define('view-indicator', function () {
            return Auth::user()->role->hasPermission('manage_indicator') || Auth::user()->role->name === 'IT' ||  Auth::user()->role->name === 'SAP';
        });

        Gate::define('view-entries', function () {
            return Auth::user()->role->hasPermission('access_entries') || Auth::user()->role->name === 'IT' ||  Auth::user()->role->name === 'SAP';
        });

        Gate::define('generate-reports', function () {
            return Auth::user()->role->hasPermission('access_report_generation') || Auth::user()->role->name === 'IT' ||  Auth::user()->role->name === 'SAP';
        });

        Gate::define('manage-user-management', function () {
            return Auth::user()->role->hasPermission('manage_users') || Auth::user()->role->name === 'IT' ||  Auth::user()->role->name === 'SAP';
        });

        Gate::define('manage-roles', function () {
            return Auth::user()->role->hasPermission('manage_roles') || Auth::user()->role->name === 'IT' ||  Auth::user()->role->name === 'SAP';
        });

        Gate::define('manage-users', function () {
            return Auth::user()->role->hasPermission('manage_users') || Auth::user()->role->name === 'IT' ||  Auth::user()->role->name === 'SAP';
        });

        Gate::define('view-history', function () {
            return Auth::user()->role->hasPermission('manage_history') || Auth::user()->role->name === 'IT' ||  Auth::user()->role->name === 'SAP';
        });

        Gate::define('view-permissions', function () {
            return Auth::user()->role->hasPermission('manage_permissions') || Auth::user()->role->name === 'IT' ||  Auth::user()->role->name === 'SAP';
        });
        Gate::define('access_pending_entries', function () {
            return Auth::user()->role->hasPermission('access_pending_entries') ||  Auth::user()->role->name === 'SAP';
        });
        Gate::define('filter_dashboard', function () {
            return Auth::user()->role->hasPermission('filter_dashboard') || Auth::user()->role->name === 'IT'  ||  Auth::user()->role->name === 'SAP';
        });
    }
}
