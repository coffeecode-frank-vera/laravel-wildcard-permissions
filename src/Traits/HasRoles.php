<?php

namespace CoffeeCode\WildcardPermissions\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Config;

use CoffeeCode\WildcardPermissions\{
    Contracts\Role,
    Contracts\WildcardPermission,
    WildcardPermissionsRegistrar
};

trait HasRoles {
    use HasPermissions;
    
    /**
     * Role class
     *
     * @var Role
     */
    protected $roleClass;

    public static function bootHasRoles()
    {
        static::deleting(function ($model) {
            if (method_exists($model, 'isForceDeleting') && ! $model->isForceDeleting()) {
                return;
            }

            $model->roles()->detach();
            if (is_a($model, WildcardPermission::class)) {
                $model->users()->detach();
            }
        });
    }

    /**
     * Return Roles assigned to model
     *
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany {
        $relation = $this->morphToMany(
            Config::get('wildcard-permissions.models.role'),
            'model',
            Config::get('wildcard-permissions.table_names.model_has_roles'),
            Config::get('wildcard-permissions.column_names.model_id'),
            app(WildcardPermissionsRegistrar::class)->pivotRole
        );

        return $relation;
    }

    /**
     * Return the role class
     *
     * @return Role
     */
    public function getRoleClass(): Role
    {
        if (! $this->roleClass) {
            $this->roleClass = app(WildcardPermissionsRegistrar::class)->getRoleClass();
        }

        return $this->roleClass;
    }

    /**
     * Assign the given role to the model
     *
     * @param string|Role ...$role
     * @return $this
     */
    public function assignRole(...$role): self {
        $roles = collect($role)
            ->flatten()
            ->map(fn ($role) => $this->getStoredRole($role))
            ->all();

        $actualRoles = $this->roles;
        $roles = collect($roles)
            ->reject(fn ($role) => $actualRoles->contains($role));

        $this->roles()->saveMany($roles);

        return $this;
    }

    /**
     * Revoke the given role from the model
     *
     * @param string|Role ...$role
     * @return $this
     */
    public function unassignRole(...$role): self {
        $roles = collect($role)
            ->flatten()
            ->map(fn ($role) => $this->getStoredRole($role)->id)
            ->all();

        $this->roles()->detach($roles);

        return $this;
    }

    /**
     * Determine if the model has the given role
     *
     * @param string|Role $role
     * @return Role
     */
    protected function getStoredRole($role): Role {
        if (is_string($role)) {
            return app(WildcardPermissionsRegistrar::class)->getRoleClass()::findByGuardName($role);
        }

        if (is_int($role)) {
            return app(WildcardPermissionsRegistrar::class)->getRoleClass()::findById($role);
        }

        return $role;
    }

    /**
     * Determine if the model has the given role
     *
     * @param string|Role ...$role
     * @return bool
     */
    public function hasRole($role): bool {
        if (is_string($role)) {
            return $this->roles->contains('guard_name', $role);
        }

        if (is_int($role)) {
            return $this->roles->contains('id', $role);
        }

        return $this->roles->contains($role);
    }

    /**
     * Determine if the model has any of the given roles
     *
     * @param string|Role ...$roles
     * @return bool
     */
    public function hasAnyRole(...$roles) {

        return collect($roles)
            ->map(fn ($role) => $this->hasRole($role))
            ->reduce(fn ($carry, $item) => $carry || $item, false);
    }

    /**
     * Determine if the model has all of the given roles
     *
     * @param string|Role ...$roles
     * @return bool
     */
    public function hasAllRoles(...$roles) {

        return collect($roles)
            ->map(fn ($role) => $this->hasRole($role))
            ->reduce(fn ($carry, $item) => $carry && $item, true);
    }

}