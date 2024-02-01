<?php

namespace CoffeeCode\WildcardPermission\Contracts;

use Illuminate\Database\Eloquent\Relations\HasMany;

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
     * @throws RoleNotFoundException
     * @return self
     */
    public static function findByShortName(string $shortName): self;


    /**
     * Find permission by its id
     *
     * @param integer $id
     * @throws RoleNotFoundException
     * @return self
     */
    public static function findById(int $id): self;

    /**
     * Check if role has permission to that wildcard
     * ex: $role->hasPermissionTo("admin:create");
     * ex: $role->hasPermissionTo("admin:create,read");
     * ex: $role->hasPermissionTo("admin:*");
     *
     * @param string $wildcard
     * @throws WildcardNotValidException
     * @return boolean
     */
    public function hasPermissionTo(string $wildcard): bool;
}