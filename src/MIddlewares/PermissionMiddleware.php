<?php

namespace CoffeeCode\WildcardPermissions\Middlewares;

use CoffeeCode\WildcardPermissions\Exceptions\UnauthorizedException;
use Illuminate\Support\Facades\Auth;

class PermissionMiddleware {

    /**
     * @param $request
     * @param $next
     * @return mixed
     */
    public function handle($request, $next, $permission) {
        $authGuard = Auth::guard();

        $user = $authGuard->user();

        if (! $user) {
            throw UnauthorizedException::notLoggedIn();
        }

        if (! method_exists($user, 'hasPermissionTo')) {
            throw UnauthorizedException::missingTraitHasRoles($user);
        }

        $permissions = is_array($permission) ? $permission : explode('&', $permission);

        $isAuthorized = collect($permissions)->some(function ($permission) use ($user) {
            return $user->hasPermissionTo($permission);
        });

        if (! $isAuthorized) {
            throw UnauthorizedException::forPermissions($permissions);
        }

        return $next($request);
    }

    /**
     * @param $permission
     * @return string|array
     */
    public static function using($permission) {
        $permissionStr = is_array($permission) ? implode('&', $permission) : $permission;

        return static::class.":".$permissionStr;
    }
}