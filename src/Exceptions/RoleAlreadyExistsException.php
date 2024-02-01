<?php

namespace CoffeeCode\WildcardPermission\Exceptions;

use InvalidArgumentException;

class RoleAlreadyExists extends InvalidArgumentException {
    public static function create(string $shortName) {
        return new static("Role \"`{$shortName}`\" already exists.");
    }
}