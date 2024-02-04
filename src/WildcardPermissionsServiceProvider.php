<?php

namespace CoffeeCode\WildcardPermissions;

use CoffeeCode\WildcardPermissions\WildcardPermissionsRegistrar;
use Illuminate\Auth\Access\Gate;
use Illuminate\Support\ServiceProvider;

class WildcardPermissionsServiceProvider extends ServiceProvider {
    public function boot() {
        $this->registerPublishing();
        $this->callAfterResolving(Gate::class, function (Gate $gate) {
            $this->app->make(WildcardPermissionsRegistrar::class)->registerPermissions($gate);
        });

        $this->app->singleton(WildcardPermissionsRegistrar::class);
    }

    public function register() {
        $this->mergeConfigFrom(__DIR__ . '/../config/wildcard-permissions.php', 'wildcard-permissions');
    }

    protected function registerPublishing() {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/wildcard-permissions.php' => config_path('wildcard-permissions.php'),
            ], 'wildcard-permissions-config');

            $this->publishes([
                __DIR__ . '/../database/migrations/create_wildcard_permissions_tables.php' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_wildcard_permissions_tables.php'),
            ], 'wildcard-permissions-migrations');
        }
    }
}