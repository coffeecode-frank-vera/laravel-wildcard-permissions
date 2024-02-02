<?php

namespace CoffeeCode\WildcardPermission;

use CoffeeCode\WildcardPermission\WildcardPermissionRegistrar;
use Illuminate\Auth\Access\Gate;
use Illuminate\Support\ServiceProvider;

class WilcardPermissionsServiceProvider extends ServiceProvider {
    public function boot() {
        $this->registerPublishing();
        $this->callAfterResolving(Gate::class, function (Gate $gate) {
            $this->app->make(WildcardPermissionRegistrar::class)->registerPermissions($gate);
        });

        $this->app->singleton(WildcardPermissionRegistrar::class);
    }

    public function register() {
        $this->mergeConfigFrom(__DIR__ . '/../config/wildcard-permission.php', 'wildcard-permission');
    }

    protected function registerPublishing() {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/wildcard-permission.php' => config_path('wildcard-permission.php'),
            ], 'wildcard-permission-config');

            $this->publishes([
                __DIR__ . '/../database/migrations/create_wildcard_permission_tables.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_wildcard_permission_tables.php'),
            ], 'wildcard-permission-migrations');
        }
    }
}