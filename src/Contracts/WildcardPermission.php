<?php

namespace CoffeeCode\WildcardPermission\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Contract for Wildcard Permission Model
 */

interface WildcardPermission {
    /**
     * Permissions could be part of many roles
     *
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany;

    /**
     * Find permission by it's short name
     * ex: WilcardPermission::findByShortName('Admin edit Permission');
     *
     * @param string $shortName
     * @throws PermissionNotFoundException
     * @return self
     */
    public static function findByShortName(string $shortName): self;

    /**
     * Find permission by its guard name
     * ex: WilcardPermission::findByGuardName('admin:create');
     *
     * @param string $guardName
     * @throws PermissionNotFoundException
     * @return self
     */
    public static function findByGuardName(string $guardName): self;

    /**
     * Find permission by its id
     *
     * @param integer $id
     * @throws PermissionNotFoundException
     * @return self
     */
    public static function findById(int $id): self;
}