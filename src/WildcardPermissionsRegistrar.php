<?php

namespace CoffeeCode\WildcardPermissions;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Config;

use CoffeeCode\WildcardPermissions\Contracts\{
    Role,
    WildcardPermission
};
use Illuminate\Contracts\Auth\Access\{
    Authorizable,
    Gate
};

class WildcardPermissionsRegistrar {

    protected string $permissionClass;
    protected string $roleClass;
    protected $permissions;
    public string $pivotRole;
    public string $pivotPermission;

    public function __construct()
    {
        $this->permissionClass = Config::get('wildcard-permissions.models.permission');
        $this->roleClass = Config::get('wildcard-permissions.models.role');
        $this->pivotRole = Config::get('wildcard-permissions.column_names.role_pivot_key') ?: 'role_id';
        $this->pivotPermission = Config::get('wildcard-permissions.column_names.permission_pivot_key') ?: 'permission_id';
    }

    /**
     * Register the permissions
     * 
     * @param Gate $gate
     * @return bool
     */
    public function registerPermissions(Gate $gate): bool
    {
        $gate->before(function (Authorizable $user, string $ability, array &$args = []) {
            if (method_exists($user, 'checkPermissionTo')) {
                return $user->checkPermissionTo($ability) ?: null;
            }
        });

        return true;
    }

    /**
     * Get the permission class
     */
    public function getPermissionClass(): string
    {
        return $this->permissionClass;
    }

    /**
     * Set the permission class
     */
    public function setPermissionClass($permissionClass)
    {
        $this->permissionClass = $permissionClass;
        Config::set('permission.models.permission', $permissionClass);
        app()->bind(WildcardPermission::class, $permissionClass);

        return $this;
    }

    public function getRoleClass(): string
    {
        return $this->roleClass;
    }

    /**
     * Set the role class
     */
    public function setRoleClass($roleClass)
    {
        $this->roleClass = $roleClass;
        Config::set('permission.models.role', $roleClass);
        app()->bind(Role::class, $roleClass);

        return $this;
    }

    /**
     * Get the permissions with roles
     *
     * @return Collection
     */
    protected function getPermissionsWithRoles(): Collection
    {
        return $this->permissionClass::select()->with('roles')->get();
    }
}