<?php

return [
    'models' => [
        'permission' => CoffeeCode\WildcardPermission\Models\WildcardPermission::class,
        'role' => CoffeeCode\WildcardPermission\Models\Role::class
    ],
    'table_names' => [
        'roles' => 'roles',
        'wildcard_permissions' => 'wildcard_permissions',
        'model_has_permissions' => 'model_has_permissions',
        'model_has_roles' => 'model_has_roles',
        'roles_has_permissions' => 'roles_has_permissions',
    ],
    'pivot_names' => [
        'role_pivot_key' => 'role_id',
        'permission_pivot_key' => 'permission_id',
        'model_id' => 'model_id'
    ]
];