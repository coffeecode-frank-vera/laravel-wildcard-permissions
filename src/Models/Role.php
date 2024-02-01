<?php

use CoffeeCode\WildcardPermission\Contracts\Role as RoleContract;
use CoffeeCode\WildcardPermission\Exceptions\PropertyMustHaveValueException;
use Illuminate\Database\Eloquent\Model;

class Role extends Model implements RoleContract {

    protected $guarded = [];

    public function __construct(array $attrs = []) {
        parent::__construct($attrs);

        $this->guarded[] = $this->primaryKey;
        $this->table = config('permission.table_names.roles') ?: parent::getTable();
    }

    public static function create(array $attrs = []) {
        // Validation
        if (empty($attrs['shortName'])) {
            throw PropertyMustHaveValueException::create(collect(['shortName']));
        }


    }

    /**
     * Role have many Permissions
     *
     * @return BelongsToMany
     */
    public function wildcardPermissions(): HasMany {

    }

    /**
     * Find role by it's short name
     * ex: WilcardPermission::findByShortName('Admin edit Permission');
     *
     * @param string $shortName
     * @throws RoleNotFoundException
     * @return self
     */
    public static function findByShortName(string $shortName): self {

    }


    /**
     * Find permission by its id
     *
     * @param integer $id
     * @throws RoleNotFoundException
     * @return self
     */
    public static function findById(int $id): self {

    }

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
    public function hasPermissionTo(string $wildcard): bool {

    }

    protected function checkIfExists(): bool {
        try {
            static::findByShortName($this->shortName);
        } catch ()
    }
}