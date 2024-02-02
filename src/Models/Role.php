<?php

namespace CoffeeCode\WildcardPermission\Models;

use Illuminate\Support\Facades\{
    Config,
    Log
};

use CoffeeCode\WildcardPermission\{
    Contracts\Role as RoleContract,
    Exceptions\PropertyMustHaveValueException,
    Exceptions\RoleAlreadyExists,
    Exceptions\RoleNotFoundException,
    Exceptions\WildcardNotValidException,
    Wildcard,
    WildcardPermissionRegistrar
};
use Illuminate\Database\Eloquent\{
    Relations\BelongsToMany,
    Model
};

class Role extends Model implements RoleContract
{

    protected $guarded = [];

    /**
     * Role constructor
     */
    public function __construct(array $attrs = [])
    {
        parent::__construct($attrs);

        $this->guarded[] = $this->primaryKey;
        $this->setTable(Config::get('permission.table_names.roles'));
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
        if (empty($attrs['shortName']) || empty($attrs['guardName'])) {
            throw PropertyMustHaveValueException::create(collect(['shortName', 'guardName']));
        }

        if (static::checkIfExists($attrs)) {
            throw RoleAlreadyExists::create($attrs['shortName']);
        }

        return static::query()->create($attrs);
    }

    /**
     * Role have many Permissions
     *
     * @return BelongsToMany
     */
    public function wildcardPermissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Config::get('permission.models.permission'),
            Config::get('permission.table_names.role_has_permissions'),
            app(WildcardPermissionRegistrar::class)->pivotRole,
            app(WildcardPermissionRegistrar::class)->pivotPermission
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
            Config::get('permission.models.user'),
            Config::get('permission.table_names.user_has_roles'),
            app(WildcardPermissionRegistrar::class)->pivotRole,
            Config::get('permission.column_names.user_id'),
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

        return static::findByProperty('shortName', $shortName);
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

        return static::findByProperty('guardName', $guardName);
    }

    /**
     * Check if role has permission to that wildcard
     * ex: $role->hasPermissionTo("admin:create");
     * ex: $role->hasPermissionTo("admin:create,read");
     * ex: $role->hasPermissionTo("admin:*");
     *
     * @param string $permission
     * @throws WildcardNotValidException
     * @return boolean
     */
    public function hasPermissionTo(string $permission): bool
    {
        $wildcard = new Wildcard($permission);

        if (!$wildcard->valid) {
            throw WildcardNotValidException::create($permission);
        }

        return false;
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
            static::findByShortName($attrs['shortName']);

            return true;
        } catch (RoleNotFoundException $exception) {
            Log::info($exception->getMessage());
        }

        try {
            static::findByGuardName($attrs['guardName']);

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