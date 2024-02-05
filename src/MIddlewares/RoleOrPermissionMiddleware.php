<?php

namespace CoffeeCode\WildcardPermissions\Middlewares;

use CoffeeCode\WildcardPermissions\Exceptions\UnauthorizedException;
use Illuminate\Support\Facades\Auth;

class RoleOrPermissionMiddleware {

    /**
     * @param $request
     * @param $next
     * @param $roleOrPermission
     * @return mixed
     */
    public function handle($request, $next, $roleOrPermission) {
        $authGuard = Auth::guard();

        $user = $authGuard->user();

        if (! $user) {
            throw UnauthorizedException::notLoggedIn();
        }

        if (! method_exists($user, 'hasAnyRole') || ! method_exists($user, 'hasPermissionTo')) {
            throw UnauthorizedException::missingTraitHasRoles($user);
        }

        $rolesOrPermissions = is_array($roleOrPermission) ? $roleOrPermission : explode('&', $roleOrPermission);

        $isAuthorizedByPermissions = collect($rolesOrPermissions)->some(function ($permission) use ($user) {
            return $user->checkPermissionTo($permission);
        });

        if (! $isAuthorizedByPermissions && ! $user->hasAnyRole($rolesOrPermissions)) {
            throw UnauthorizedException::forRolesOrPermissions($rolesOrPermissions);
        }

        return $next($request);
    }

    /**
     * @param $roleOrPermission
     * @return string|array
     */
    public static function using($roleOrPermission) {
        $roleOrPermissionStr = is_array($roleOrPermission) ? implode('&', $roleOrPermission) : $roleOrPermission;

        return static::class.":".$roleOrPermissionStr;
    }
}