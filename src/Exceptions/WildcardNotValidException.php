<?php

namespace CoffeeCode\WildcardPermissions\Exceptions;

use InvalidArgumentException;

class WildcardNotValidException extends InvalidArgumentException {
    public static function create(string $wildcard) {
        return new static("Wildcard \"`{$wildcard}`\" is not valid, wildcards please read the documentation to see the correct syntax.");
    }
}