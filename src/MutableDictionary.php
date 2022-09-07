<?php namespace Monolith\Collections;

use Countable;
use ArrayIterator;
use IteratorAggregate;

class MutableDictionary implements IteratorAggregate, Countable
{
    public function __construct(
        private array $items = []
    ) {
    }

    public function has(mixed $key): bool
    {
        return array_key_exists($key, $this->items);
    }

    public function add(mixed $key, mixed $value): void
    {
        $this->items[$key] = $value;
    }

    public function get(mixed $key)
    {
        return $this->items[$key] ?? null;
    }

    public function remove(mixed $key): void
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

    public function merge(MutableDictionary $that): void
    {
        if (get_class($this) !== get_class($that)) {
            throw CollectionTypeError::cannotMergeDifferentTypes($this, $that);
        }
        $this->items = array_merge($this->items, $that->items);
    }

    public function copy(): static
    {
        return clone $this;
    }

    /**
     * Don't forget to return [$key=>$value] to maintain associativity.
     *
     * @param callable $f
     * @return Dictionary
     * @throws DictionaryMapFunctionHasIncorrectReturnFormat
     */
    public function map(callable $f): static
    {
        $newItems = [];

        foreach ($this->items as $key => $value) {
            $result = $f($value, $key);

            if (
                count($result) != 1 ||
                ! is_array($result)
            ) {
                throw new DictionaryMapFunctionHasIncorrectReturnFormat("When calling `map` on a Dict the function must always use this format: return [key=>value]. Received " . json_encode($result) . " instead.");
            }

            $newItems[key($result)] = $result[key($result)];
        }

        return new MutableDictionary($newItems);
    }

    public function filter(?callable $f = null): static
    {
        return new static(
            array_filter($this->items, $f, ARRAY_FILTER_USE_BOTH)
        );
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }

    public function toCollection(): Collection
    {
        return new Collection(array_values($this->items));
    }

    public static function of(array $associativeArray): static
    {
        return new static($associativeArray);
    }

    public static function empty(): static
    {
        return new static;
    }
}
