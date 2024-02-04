<?php

namespace CoffeeCode\WildcardPermissions\Models;

use Illuminate\Support\Facades\{
    Config,
    Log
};

use CoffeeCode\WildcardPermissions\{
    Contracts\Role as RoleContract,
    Exceptions\PropertyMustHaveValueException,
    Exceptions\RoleAlreadyExistsException,
    Exceptions\RoleNotFoundException,
    Exceptions\WildcardNotValidException,
    Traits\HasPermissions,
    Wildcard,
    WildcardPermissionsRegistrar
};
use Illuminate\Database\Eloquent\{
    Relations\BelongsToMany,
    Model
};

class Role extends Model implements RoleContract
{
    use HasPermissions;

    protected $guarded = [];

    /**
     * Role constructor
     */
    public function __construct(array $attrs = [])
    {
        parent::__construct($attrs);

        $this->guarded[] = $this->primaryKey;
        $this->setTable(Config::get('wildcard-permissions.table_names.roles'));
    }

    /**
     * Create a new role
     *
     * @param array $attrs
     * @throws PropertyMustHaveValueException
     * @return self
     */
    public static function create(array $attrs = [])
    {
        if (empty($attrs['short_name']) || empty($attrs['guard_name'])) {
            throw PropertyMustHaveValueException::create(collect(['short_name', 'guard_name']));
        }

        if (static::checkIfExists($attrs)) {
            throw RoleAlreadyExistsException::create($attrs['short_name']);
        }

        return static::query()->create($attrs);
    }

    /**
     * Role have many Permissions
     *
     * @return BelongsToMany
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Config::get('wildcard-permissions.models.permission'),
            Config::get('wildcard-permissions.table_names.roles_has_permissions'),
            app(WildcardPermissionsRegistrar::class)->pivotRole,
            app(WildcardPermissionsRegistrar::class)->pivotPermission
        );
    }

    /**
     * Permissions could be part of many roles
     *
     * Get the roles that have the permission.
     *
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            Config::get('wildcard-permissions.models.user'),
            Config::get('wildcard-permissions.table_names.user_has_roles'),
            app(WildcardPermissionsRegistrar::class)->pivotRole,
            Config::get('wildcard-permissions.column_names.user_id'),
        );
    }

    /**
     * Find role by it's short name
     * ex: WilcardPermission::findByShortName('Admin edit Permission');
     *
     * @param string $shortName
     * @throws RoleNotFoundException
     * @return self
     */
    public static function findByShortName(string $shortName): self
    {

        return static::findByProperty('short_name', $shortName);
    }


    /**
     * Find permission by its id
     *
     * @param integer $id
     * @throws RoleNotFoundException
     * @return self
     */
    public static function findById(int $id): self
    {
        $role = static::query()->find($id);

        if (!$role) {
            throw RoleNotFoundException::create($id);
        }

        return $role;
    }

    /**
     * Find role by it's guard name
     * ex: WilcardPermission::findByGuardName('admin:create');
     *
     * @param string $guardName
     * @throws RoleNotFoundException
     * @return self
     */
    public static function findByGuardName(string $guardName): self
    {

        return static::findByProperty('guard_name', $guardName);
    }

    /**
     * Check if role has permission to that wildcard
     * ex: $role->hasPermissionTo("admin:create");
     * ex: $role->hasPermissionTo("admin:create,read");
     * ex: $role->hasPermissionTo("admin:*");
     *
     * @param mixed $permission
     * @throws WildcardNotValidException
     * @return bool
     */
    public function hasPermissionTo($permission): bool
    {
        if (is_a($permission, WildcardPermission::class)) {
            return $this->permissions->contains($permission);
        }

        return $this->checkPermissionsTo($this->permissions, $permission);
    }

    /**
     * Check if role exists
     *
     * @param array $attrs
     * @return boolean
     */
    public static function checkIfExists($attrs): bool
    {
        try {
            static::findByShortName($attrs['short_name']);

            return true;
        } catch (RoleNotFoundException $exception) {
            Log::info($exception->getMessage());
        }

        try {
            static::findByGuardName($attrs['guard_name']);

            return true;
        } catch (RoleNotFoundException $exception) {
            Log::info($exception->getMessage());
        }

        return false;
    }

    /**
     * Find role by property
     *
     * @param string $property
     * @param string $value
     * @return self
     */
    protected static function findByProperty(string $property, string $value): self
    {
        $role = static::query()->where($property, $value)->first();

        if (!$role) {
            throw RoleNotFoundException::create($value);
        }

        return $role;
    }
}