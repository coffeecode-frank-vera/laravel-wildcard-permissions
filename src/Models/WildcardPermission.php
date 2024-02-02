<?php

namespace CoffeeCode\WildcardPermission\Models;

use Illuminate\Support\Facades\{
    Config,
    Log
};

use CoffeeCode\WildcardPermission\{
    Contracts\WildcardPermission as WilcardPermissionContract,
    Exceptions\PermissionAlreadyExistsException,
    Exceptions\PermissionNotFoundException,
    Exceptions\PropertyMustHaveValueException,
    Exceptions\WildcardNotValidException,
    Wildcard,
    WildcardPermissionRegistrar
};
use Illuminate\Database\Eloquent\{
    Relations\BelongsToMany,
    Model
};
class WildcardPermission extends Model implements WilcardPermissionContract
{

    protected $guarded = [];

    public function __construct(array $attrs = [])
    {
        parent::__construct($attrs);
        
        $this->guarded[] = $this->primaryKey;
        $this->setTable(Config::get('wildcard-permission.table_names.permissions'));
    }

    public function create(array $attrs): self
    {
        if (empty($attrs['shortName']) || empty($attrs['guardName'])) {
            throw PropertyMustHaveValueException::create(collect(['shortName', 'guardName']));
        }

        $wildcard = new Wildcard($attrs['guardName']);
        if (! $wildcard->isExact()) {
            throw WildcardNotValidException::create($attrs['guardName']);
        }

        if (static::checkIfExists($attrs)) {
            throw PermissionAlreadyExistsException::create($attrs['shortName']);
        }

        return static::query()->create($attrs);
    }

    /**
     * Permission have many Roles
     *
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Config::get('wildcard-permission.models.role'),
            Config::get('wildcard-permission.table_names.role_has_permissions'),
            app(WildcardPermissionRegistrar::class)->pivotPermission,
            app(WildcardPermissionRegistrar::class)->pivotRole
        );
    }

    public function users() {
        return $this->morphedByMany(
            $this->getModelForGuard($this->attributes['guard_name'] ?? Config::get('auth.defaults.guard')),
            'model',
            Config::get('wildcard-permission.table_names.user_has_permissions'),
            app(WildcardPermissionRegistrar::class)->pivotPermission,
            Config::get('wildcard-permission.column_names.model_id')
        );
    }

    public static function findByShortName(string $shortName): self
    {
        $permission = static::where('short_name', $shortName)->first();
        if (!$permission) {
            throw PermissionNotFoundException::create($shortName);
        }
        return $permission;
    }

    public static function findByGuardName(string $guardName): self
    {
        $permission = static::where('guard_name', $guardName)->first();
        if (!$permission) {
            throw PermissionNotFoundException::create($guardName);
        }
        return $permission;
    }

    public static function findById(int $id): self
    {
        $permission = static::find($id);
        if (!$permission) {
            throw PermissionNotFoundException::create($id);
        }
        return $permission;
    }

    /**
     * Check if permission exists
     *
     * @param array $attrs
     * @return boolean
     */
    public static function checkIfExists($attrs): bool
    {
        try {
            static::findByShortName($attrs['shortName']);

            return true;
        } catch (PermissionNotFoundException $exception) {
            Log::info($exception->getMessage());
        }

        try {
            static::findByGuardName($attrs['guardName']);

            return true;
        } catch (PermissionNotFoundException $exception) {
            Log::info($exception->getMessage());
        }

        return false;
    }

    /**
     * Find permission by property
     *
     * @param string $property
     * @param string $value
     * @return self
     */
    protected static function findByProperty(string $property, string $value): self
    {
        $permission = static::query()->where($property, $value)->first();

        if (!$permission) {
            throw PermissionNotFoundException::create($value);
        }

        return $permission;
    }

    function getModelForGuard(string $guard)
    {
        return collect(Config::get('auth.guards'))
            ->map(fn ($guard) => isset($guard['provider']) ? Config::get("auth.providers.{$guard['provider']}.model") : null)
            ->get($guard);
    }
}