<?php

namespace CoffeeCode\WildcardPermission;

use Illuminate\Contracts\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Access\Gate;
use CoffeeCode\WildcardPermission\Contracts\WildcardPermission;
use CoffeeCode\WildcardPermission\Contracts\Role;
use Illuminate\Database\Eloquent\Collection;

class WildcardPermissionRegistrar {

    protected string $permissionClass;

    protected string $roleClass;

    protected $permissions;

    public string $pivotRole;

    public string $pivotPermission;

    public function __construct()
    {
        $this->permissionClass = config('permission.models.permission');
        $this->roleClass = config('permission.models.role');
    }

    public function registerPermissions(Gate $gate): bool
    {
        $gate->before(function (Authorizable $user, string $ability, array &$args = []) {
            if (is_string($args[0] ?? null) && ! class_exists($args[0])) {
                $guard = array_shift($args);
            }
            if (method_exists($user, 'checkPermissionTo')) {
                return $user->checkPermissionTo($ability, $guard ?? null) ?: null;
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
        config()->set('permission.models.permission', $permissionClass);
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
        config()->set('permission.models.role', $roleClass);
        app()->bind(Role::class, $roleClass);

        return $this;
    }

    protected function getPermissionsWithRoles(): Collection
    {
        return $this->permissionClass::select()->with('roles')->get();
    }
}