<?php

namespace CoffeeCode\WildcardPermission\Exceptions;

use InvalidArgumentException;

class PermissionNotFoundException extends InvalidArgumentException {
    public static function create(string $name) {
        return new static("Permission \"`{$name}`\" not found.");
    }
}