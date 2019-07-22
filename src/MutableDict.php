<?php namespace Monolith\Collections;

use ArrayIterator;
use Countable;
use IteratorAggregate;

class MutableDict implements IteratorAggregate, Countable
{
    private $items;

    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    public static function empty(): MutableDict
    {
        return new static;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->items);
    }

    public function add(string $key, $value): void
    {
        $this->items[$key] = $value;
    }

    public function get(string $key)
    {
        return isset($this->items[$key]) ? $this->items[$key] : null;
    }

    public function remove(string $key): void
    {
        unset($this->items[$key]);
    }

    public function toArray(): array
    {
        return $this->items;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function merge(MutableDict $that): void
    {
        if (get_class($this) !== get_class($that)) {
            throw CollectionTypeError::cannotMergeDifferentTypes($this, $that);
        }
        $this->items = array_merge($this->items, $that->items);
    }

    public function copy(): MutableDict
    {
        return clone $this;
    }


    /**
     * Don't forget to return [$key=>$value] to maintain associativity.
     *
     * @param callable $f
     * @return Dict
     * @throws DictMapFunctionHasIncorrectReturnFormat
     */
    public function map(callable $f): MutableDict
    {
        $newItems = [];

        foreach ($this->items as $key => $value) {
            $result = $f($value, $key);

            if (
                count($result) != 1 ||
                ! is_array($result)
            ) {
                throw new DictMapFunctionHasIncorrectReturnFormat("When calling `map` on a Dict the function must always use this format: return [key=>value]. Received " . json_encode($result) . " instead.");
            }

            $newItems[key($result)] = $result[key($result)];
        }

        return new MutableDict($newItems);
    }

    public function filter(?callable $f = null): MutableDict
    {
        return new static(array_filter($this->items, $f, ARRAY_FILTER_USE_BOTH));
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }
}
