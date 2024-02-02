<?php

namespace CoffeeCode\WildcardPermission;

use Illuminate\Support\Collection;

class Wildcard {
    public $AND = 'and';
    public $OR = 'or';
    public $ALL = 'all';
    public $NAMESPACE = 'namespace';

    public $operation;
    public $namespaces;
    public $lookups;
    public $valid = false;

    protected $OPERATORS = [];
    protected $EXACT_REGEX = '/^[a-z][a-zA-Z:]*[a-z]$/';
    protected $OR_REGEX = '/^[a-z][a-zA-Z:|]*[a-z]$/';
    protected $AND_REGEX = '/^[a-z][a-zA-Z:,]*[a-z]$/';
    protected $ALL_REGEX = '/^[a-z][a-zA-Z:]*[a-z]|[:*]$/';
    protected $value;

    public function __construct($value)
    {
        $this->value = $value;
        $this->OPERATORS = [
            $this->AND => [
                'value' => ',',
                'regex' => $this->AND_REGEX
            ],
            $this->OR => [
                'value' => '|',
                'regex' => $this->OR_REGEX
            ],
            $this->ALL => [
                'value' => '*',
                'regex' => $this->ALL_REGEX
            ],
            $this->NAMESPACE => [
                'value' => ':',
                'regex' => $this->EXACT_REGEX
            ]
        ];
        $this->valid = $this->isExact() || $this->isWildcard();
    }

    /**
     * Get the value of the wildcard\
     *
     * @return string
     */
    public function getValue() {
        if ($this->isExact()) {
            return str_replace($this->OPERATORS[$this->ALL]['value'], '', $this->value);
        }

        return $this->value;
    }

    /**
     * Check if the value is a valid wildcard
     * ex: admin:create
     * ex: admin:create,read
     * ex: admin:create|read
     * ex: admin:*
     *
     * @return bool
     */
    public function isWildcard(): bool {
        if ($this->isOperation($this->OR)) {
            return true;
        }

        if ($this->isOperation($this->AND)) {
            return true;
        }

        if ($this->isOperation($this->ALL)) {
            return true;
        }

        return false;
    }

    /**
     * Check if the wildcard is exact
     * ex: admin:create
     * ex: admin:create:read
     *
     * @return bool
     */
    public function isExact(): bool {

        return boolval(preg_match($this->EXACT_REGEX, $this->value));
    }

    /**
     * Check if the wildcard is valid and construct the namespaces and lookups
     * ex: admin:*
     * ex: admin:create,read
     * ex: admin:create|read
     *
     * @return bool
     */
    protected function isOperation($operation): bool {
        if (!boolval(preg_match($this->OPERATORS[$operation]['regex'], $this->value))) {
            return false;
        }

        $namespaces = explode($this->OPERATORS[$this->NAMESPACE]['value'], $this->value);
        $lastNamespace = array_pop($namespaces);

        foreach ($namespaces as $namespace) {
            if (str_contains($namespace, $this->OPERATORS[$operation]['value'])) {
                return false;
            }
        }

        $this->namespaces = $namespaces;
        $this->lookups = explode($this->OPERATORS[$operation]['value'], $lastNamespace);
        $this->operation = $operation;

        return true;
    }

    /**
     * Get the possibilities of the wildcard\
     *
     * @return Collection
     */
    public function getPossibilities(): Collection {
        if ($this->isExact()) {
            return collect([$this->value]);
        }

        $possibilities = [];
        $baseNamespace = implode($this->OPERATORS[$this->NAMESPACE]['value'], $this->namespaces);
        
        foreach ($this->lookups as $lookup) {
            $possibilities[] = $baseNamespace . $this->OPERATORS[$this->NAMESPACE]['value'] . $lookup;
        }

        return collect($possibilities);
    }
}
