<?php

namespace CoffeeCode\WildcardPermissions\Middlewares;

use CoffeeCode\WildcardPermissions\Exceptions\UnauthorizedException;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware {
     /**
     * @param $request
     * @param $next
     * @param $role
     * @return mixed
     */
    public function handle($request, $next, $role) {
        $authGuard = Auth::guard();

        $user = $authGuard->user();

        if (! $user) {
            throw UnauthorizedException::notLoggedIn();
        }

        if (! method_exists($user, 'hasAnyRole')) {
            throw UnauthorizedException::missingTraitHasRoles($user);
        }

        $roles = is_array($role) ? $role : explode('&', $role);

        if (! $user->hasAnyRole($roles)) {
            throw UnauthorizedException::forRoles($roles);
        }

        return $next($request);
    }

    /**
     * @param $role
     * @return string|array
     */
    public static function using($role) {
        $roleStr = is_array($role) ? implode('&', $role) : $role;

        return static::class.":".$roleStr;
    }
}