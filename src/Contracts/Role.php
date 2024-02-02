<?php

namespace CoffeeCode\WildcardPermission\Contracts;

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
    public function wildcardPermissions(): HasMany;

    /**
     * Find role by it's short name
     * ex: WilcardPermission::findByShortName('Admin edit Permission');
     *
     * @param string $shortName
     * @throws \CoffeeCode\WildcardPermission\Exceptions\RoleNotFoundException
     * @return self
     */
    public static function findByShortName(string $shortName): self;

    /**
     * Find role by it's guard name
     * ex: WilcardPermission::findByGuardName('admin:create');
     *
     * @param string $guardName
     * @throws \CoffeeCode\WildcardPermission\Exceptions\RoleNotFoundException
     * @return self
     */
    public static function findByGuardName(string $guardName): self;


    /**
     * Find permission by its id
     *
     * @param integer $id
     * @throws \CoffeeCode\WildcardPermission\Exceptions\RoleNotFoundException
     * @return self
     */
    public static function findById(int $id): self;

    /**
     * Check if role has permission to that wildcard
     * ex: $role->hasPermissionTo("admin:create");
     * ex: $role->hasPermissionTo("admin:create,read");
     * ex: $role->hasPermissionTo("admin:*");
     *
     * @param string $permission
     * @throws \CoffeeCode\WildcardPermission\Exceptions\WildcardNotValidException
     * @return boolean
     */
    public function hasPermissionTo(string $permission): bool;
}