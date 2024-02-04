<?php

namespace CoffeeCode\WildcardPermissions\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Contract for Wildcard Permission Model
 */

interface Role {
    /**
     * Role have many Permissions
     *
     * @return BelongsToMany
     */
    public function permissions(): BelongsToMany;

    /**
     * Find role by it's short name
     * ex: WilcardPermission::findByShortName('Admin edit Permission');
     *
     * @param string $shortName
     * @throws \CoffeeCode\WildcardPermissions\Exceptions\RoleNotFoundException
     * @return self
     */
    public static function findByShortName(string $shortName): self;

    /**
     * Find role by it's guard name
     * ex: WilcardPermission::findByGuardName('admin:create');
     *
     * @param string $guardName
     * @throws \CoffeeCode\WildcardPermissions\Exceptions\RoleNotFoundException
     * @return self
     */
    public static function findByGuardName(string $guardName): self;


    /**
     * Find permission by its id
     *
     * @param integer $id
     * @throws \CoffeeCode\WildcardPermissions\Exceptions\RoleNotFoundException
     * @return self
     */
    public static function findById(int $id): self;

    /**
     * Check if role has permission to that wildcard
     * ex: $role->hasPermissionTo("admin:create");
     * ex: $role->hasPermissionTo("admin:create,read");
     * ex: $role->hasPermissionTo("admin:*");
     *
     * @param mixed $permission
     * @throws \CoffeeCode\WildcardPermissions\Exceptions\WildcardNotValidException
     * @return bool
     */
    public function hasPermissionTo($permission): bool;
}