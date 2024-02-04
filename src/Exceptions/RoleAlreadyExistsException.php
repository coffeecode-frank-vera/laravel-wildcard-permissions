<?php

namespace CoffeeCode\WildcardPermissions\Exceptions;

use InvalidArgumentException;

class RoleAlreadyExistsException extends InvalidArgumentException {
    public static function create(string $shortName) {
        return new static("Role \"`{$shortName}`\" already exists.");
    }
}