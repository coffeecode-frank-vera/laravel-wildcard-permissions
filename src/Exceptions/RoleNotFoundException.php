<?php

namespace CoffeeCode\WildcardPermission\Exceptions;

use InvalidArgumentException;

class RoleNotFoundException extends InvalidArgumentException {
    public static function create(string $name) {
        return new static("Role \"`{$name}`\" not found.");
    }
}