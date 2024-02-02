<?php

namespace CoffeeCode\WildcardPermission\Traits;

use CoffeeCode\WildcardPermission\Contracts\Role;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use CoffeeCode\WildcardPermission\WildcardPermissionRegistrar;
use Illuminate\Support\Facades\Config;

trait HasRoles {
    use HasPermissions;
    
    /**
     * Role class
     *
     * @var Role
     */
    protected $roleClass;
    /**
     * Return Roles assigned to model
     *
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany {
        $relation = $this->morphToMany(
            Config::get('permission.models.role'),
            'model',
            Config::get('permission.table_names.model_has_roles'),
            Config::get('permission.column_names.model_id'),
            app(WildcardPermissionRegistrar::class)->pivotRole
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
            $this->roleClass = app(WildcardPermissionRegistrar::class)->getRoleClass();
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
            ->map(fn ($role) => $this->getStoredRole($role))
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
            return app(WildcardPermissionRegistrar::class)->getRoleClass()::findByGuardName($role);
        }

        if (is_int($role)) {
            return app(WildcardPermissionRegistrar::class)->getRoleClass()::findById($role);
        }

        return $role;
    }
}