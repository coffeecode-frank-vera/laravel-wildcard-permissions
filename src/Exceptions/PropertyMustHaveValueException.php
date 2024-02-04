<?php

namespace CoffeeCode\WildcardPermissions\Exceptions;

use Illuminate\Support\Collection;
use InvalidArgumentException;

class PropertyMustHaveValueException extends InvalidArgumentException {
    public static function create(Collection $expectedProperties) {
        return new static("The given properties`{$expectedProperties->implode(', ')}` must have a value.");
    }
}