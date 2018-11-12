<?php

namespace Stem\Models\Query;

/**
 * Represents a "fluent" field in a query.
 *
 * @package Stem\Models\Query
 *
 * @method Query equals(mixed|null $value)
 * @method Query notEquals(mixed|null $value)
 * @method Query lessThan(mixed|null $value)
 * @method Query lessThanEquals(mixed|null $value)
 * @method Query greaterThan(mixed|null $value)
 * @method Query greaterThanEquals(mixed|null $value)
 * @method Query none(mixed|null $value)
 * @method Query all(mixed|null $value)
 * @method Query in(mixed|null $value)
 * @method Query notIn(mixed|null $value)
 * @method Query between(mixed|null $value)
 * @method Query notBetween(mixed|null $value)
 * @method Query exists(mixed|null $value)
 * @method Query notExists(mixed|null $value)
 * @method Query null(mixed|null $value)
 * @method Query notNull(mixed|null $value)
 * @method Query like(mixed|null $value)
 * @method Query notLike(mixed|null $value)
 *
 */
class Field {
    /** @var Query The query that owns this field */
    protected $query;

    /** @var string[] The operators that this field can use */
    protected $allowedOperators = [];

    /** @var null|string Name of the field */
    protected $fieldName = null;

    /** @var null|callable Callback to call when the value has been set */
    protected $callback = null;

    /** @var null|mixed */
    protected $value = null;

    /** @var null|string */
    protected $operator = null;

    /** @var array All of the operators that can be used on any field */
    protected static $allOperators = [
        'equals' => '=',
        'notEquals' => '!=',
        'lessThan' => '<',
        'lessThanEquals' => '<=',
        'greaterThan' => '>',
        'greaterThanEquals' => '>=',
        'none' => 'none',
        'all' => 'all',
        'in' => 'in',
        'notIn' => 'not in',
        'between' => 'between',
        'notBetween' => 'not between',
        'exists' => 'exists',
        'notExists' => 'not exists',
        'null' => 'null',
        'notNull' => 'not null',
        'like' => 'like',
        'notLike' => 'not like',
        'regexp' => 'regexp',
        'notRegexp' => 'not regexp',
        'rlike' => 'rlike'
    ];

    /**
     * Field constructor.
     *
     * @param Query $query
     * @param string $fieldName
     * @param array $allowedOperators
     * @param callable $callback
     * @throws \Exception
     */
    public function __construct(Query $query, string $fieldName, array $allowedOperators, callable $callback) {
        $this->query = $query;
        $this->fieldName = $fieldName;
        $this->callback = $callback;

        $shortOps = array_values(static::$allOperators);

        foreach($allowedOperators as $op) {
            $lop = strtolower($op);

            if (!in_array($lop, $shortOps)) {
                throw new \Exception("Invalid operator '$op'. Valid operators are: ".implode(', ', $shortOps));
            }

            $this->allowedOperators[] = array_search($lop, static::$allOperators);
        }
    }

    /**
     * Name of the field
     *
     * @return null|string
     */
    public function fieldName() {
        return $this->fieldName;
    }

    /**
     * Value of the field
     *
     * @return mixed|null
     */
    public function value() {
        return $this->value;
    }

    /**
     * Operator being used
     *
     * @return null|string
     */
    public function operator() {
        return $this->operator;
    }

    /**
     * Magic method for calling the field operator
     *
     * @param $name
     * @param $arguments
     * @return Query
     * @throws \Exception
     */
    public function __call($name, $arguments) {
        if (!in_array($name, $this->allowedOperators)) {
            throw new \Exception("Invalid operator '$name'.  Valid operators: ".implode(", ", $this->allowedOperators));
        }

        $this->value = $arguments[0] ?? null;
        $this->operator = static::$allOperators[$name];

        if ($this->callback) {
            call_user_func($this->callback, $this->fieldName, $this->operator, $this->value);
        }

        return $this->query;
    }

}