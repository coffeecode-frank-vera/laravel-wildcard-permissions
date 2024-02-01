<?php

namespace CoffeeCode\WildcardPermission\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use CoffeeCode\WildcardPermission\WildcardPermissionRegistrar;

trait HasRoles {
    
    /**
     * Return Roles assigned to model
     *
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany {
        $relation = $this->morphToMany(
            config('permission.models.role'),
            'model',
            config('permission.table_names.model_has_roles'),
            config('permission.column_names.model_id'),
            app(WildcardPermissionRegistrar::class)->pivotRole
        );

        return $relation;
    }

    /**
     * Undocumented function
     *
     * @param Role|int $role
     * @return void
     */
    public function assignRole($role) {

    }

    public function assignRoles(array $roles) {
        
    }

    public function unassignRole($role) {

    }

    public function unassignRoles(array $roles) {
    
    }
}