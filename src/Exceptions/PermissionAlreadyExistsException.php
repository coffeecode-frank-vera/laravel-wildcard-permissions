<?php

namespace CoffeeCode\WildcardPermissions\Exceptions;

use InvalidArgumentException;

class PermissionAlreadyExistsException extends InvalidArgumentException {
    public static function create(string $shortName) {
        return new static("Wildcard Permission \"`{$shortName}`\" already exists.");
    }
}