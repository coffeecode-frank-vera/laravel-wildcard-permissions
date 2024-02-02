<?php

namespace CoffeeCode\WildcardPermission;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Config;

use CoffeeCode\WildcardPermission\Contracts\{
    Role,
    WildcardPermission
};
use Illuminate\Contracts\Auth\Access\{
    Authorizable,
    Gate
};

class WildcardPermissionRegistrar {

    protected string $permissionClass;

    protected string $roleClass;

    protected $permissions;

    public string $pivotRole;

    public string $pivotPermission;

    public function __construct()
    {
        $this->permissionClass = Config::get('permission.models.permission');
        $this->roleClass = Config::get('permission.models.role');
        $this->pivotRole = Config::get('permission.column_names.role_pivot_key') ?: 'role_id';
        $this->pivotPermission = Config::get('permission.column_names.permission_pivot_key') ?: 'permission_id';
    }

    public function registerPermissions(Gate $gate): bool
    {
        $gate->before(function (Authorizable $user, string $ability, array &$args = []) {
            if (method_exists($user, 'checkPermissionTo')) {
                return $user->checkPermissionTo($ability) ?: null;
            }
        });

        return true;
    }

    public function getPermissionClass(): string
    {
        return $this->permissionClass;
    }

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

    public function setRoleClass($roleClass)
    {
        $this->roleClass = $roleClass;
        Config::set('permission.models.role', $roleClass);
        app()->bind(Role::class, $roleClass);

        return $this;
    }

    protected function getPermissionsWithRoles(): Collection
    {
        return $this->permissionClass::select()->with('roles')->get();
    }
}