<?php

namespace CoffeeCode\WildcardPermission\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use CoffeeCode\WildcardPermission\{
    Contracts\Role,
    Exceptions\WildcardNotValidException,
    Models\WildcardPermission,
    Wildcard,
    WildcardPermissionRegistrar
};
use Illuminate\Support\{
    Facades\Config,
    Facades\Log,
    Collection
};

trait HasPermissions {

    /**
     * A model may have multiple direct permissions.
     * 
     * @return BelongsToMany
     */
    public function permissions(): BelongsToMany {
        return $this->morphToMany(
            Config::get('permission.models.permission'),
            'model',
            Config::get('permission.table_names.model_has_permissions'),
            Config::get('permission.column_names.model_id'),
            app(WildcardPermissionRegistrar::class)->pivotPermission
        );
    }

    /**
     * Check if the model has the given permission.
     * 
     * @param string|Wildcard $wildcard
     * @throws WildcardNotValidException
     * @return bool
     */
    public function hasPermissionTo($permission) {

        return $this->hasDirectPermission($permission) || $this->hasPermissionViaRole($permission);
    }

    /**
     * Check if the model has the given permission.
     * 
     * @param string|Wildcard $wildcard
     * @return bool
     */
    public function checkPermissionTo($permission): bool{
        try {

            return $this->hasPermissionTo($permission);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }

        return false;
    }

    /**
     * Check if the model has direct permission.
     * 
     * @param string|Wildcard $wildcard
     * @throws WildcardNotValidException
     * @return bool
     */
    public function hasDirectPermission($wildcard): bool {

        return $this->checkPermissionsTo($this->permissions, $wildcard);
    }

    /**
     * Check if the model has permission via role.
     * 
     * @param string|Wildcard $wildcard
     * @throws WildcardNotValidException
     * @return bool
     */
    public function hasPermissionViaRole($wildcard): bool {
        
        return $this->checkPermissionsTo($this->getPermissionsViaRoles(), $wildcard);
    }

    /**
     * Check if the wildcard match any of the given permissions.
     * 
     * @param string|Wildcard $wildcard
     * @throws WildcardNotValidException
     * @return bool
     */
    protected function checkPermissionsTo($permissions, $wildcard): bool{
        if (! is_a($wildcard, Wildcard::class)) {
            $wildcard = new Wildcard($wildcard);
        }

        if (! $wildcard->valid) {
            throw WildcardNotValidException::create($wildcard->getValue());
        }

        $guardNames = $permissions->pluck('guardName')->unique();

        if ($wildcard->isExact()) {
            return $guardNames->contains($wildcard->getValue());
        }

        if ($wildcard->operation === $wildcard->ALL) {
            return $guardNames->contains(fn ($guardName) => str_contains($guardName, $wildcard->getValue()));
        }

        $possibilities = $wildcard->getPossibilities();
        if ($wildcard->operation === $wildcard->OR) {
            return $guardNames->contains(fn ($guardName) => $possibilities->contains($guardName));
        }

        return ! $possibilities
                    ->map(fn ($possibility) => $guardNames->contains($possibility))
                    ->contains(false);
    }

    /**
     * Get all the permissions assigned to the model.
     * 
     * @param string|Wildcard $wildcard
     * @throws WildcardNotValidException
     * @return Collection
     */
    public function getAllPermissions(): Collection {
        $permissions = $this->permissions;

        if (method_exists($this, 'roles')) {
            $permissions = $permissions->merge($this->getPermissionsViaRoles())->unique();
        }

        return $permissions->sort()->values();
    }

    /**
     * Get all the permissions assigned to the model via roles.
     * 
     * @param string|Wildcard $wildcard
     * @throws WildcardNotValidException
     * @return Collection
     */
    public function getPermissionsViaRoles(): Collection {
        if (is_a($this, Role::class) || is_a($this, WildcardPermission::class)) {
            return collect();
        }

        return $this->loadMissing('roles', 'roles.permissions')
            ->roles->flatMap(fn ($role) => $role->permissions)
            ->sort()->values();
    }

    /**
     * Get Permission model in case a string is provided.
     * 
     * @param string|Wildcard $wildcard
     * @throws WildcardNotValidException
     * @return Collection
     */
    protected function getStoredPermission($permission) {
        if (is_string($permission)) {
            return app(WildcardPermissionRegistrar::class)->getPermissionClass()::findByGuardName($permission);
        }

        if (is_int($permission)) {
            return app(WildcardPermissionRegistrar::class)->getPermissionClass()::findById($permission);
        }

        return $permission;
    }

    /**
     * Grant the given permission to the model.
     * 
     * @param string|Wildcard ...$permissions
     * @return $this
     */
    public function givePermissionTo(...$permissions) {
        $permissions = collect($permissions)
            ->flatten()
            ->map(fn ($permission) => $this->getStoredPermission($permission))
            ->all();
        
        $actualPermissions = $this->permissions;
        $permissions = collect($permissions)
                    ->reject(fn ($permission) => $actualPermissions->contains($permission));

        $this->permissions()->saveMany($permissions);

        return $this;
    }

    /**
     * Revoke the given permission from the model.
     * 
     * @param string|Wildcard ...$permissions
     * @return $this
     */
    public function revokePermissionsTo(...$permissions) {
        $permissions = collect($permissions)
            ->flatten()
            ->map(fn ($permission) => $this->getStoredPermission($permission))
            ->all();

        $this->permissions()->detach($permissions);

        return $this;
    }
}