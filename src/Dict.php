<?php namespace Monolith\Collections;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use function spec\Monolith\Collections\dd;

class Dict
    implements IteratorAggregate, Countable
{
    private $items;

    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->items);
    }

    public function add(string $key, $value): Dict
    {
        $newItems = $this->items;
        $newItems[$key] = $value;
        return new static($newItems);
    }

    public function get(string $key)
    {
        return isset($this->items[$key]) ? $this->items[$key] : null;
    }

    public function remove(string $key): Dict
    {
        $newItems = $this->items;
        unset($newItems[$key]);
        return new static($newItems);
    }

    public function toArray(): array
    {
        return $this->items;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function merge(Dict $that): Dict
    {
        if (get_class($this) !== get_class($that)) {
            throw CollectionTypeError::cannotMergeDifferentTypes($this, $that);
        }
        $newItems = array_merge($this->items, $that->items);
        return new static($newItems);
    }

    public function copy(): Dict
    {
        return clone $this;
    }

    /**
     * Callable argument is $value, $key
     *
     * @param callable $f
     */
    public function each(callable $f)
    {
        array_walk($this->items, $f);
    }

    /**
     * Don't forget to return [$key=>$value] to maintain associativity.
     *
     * @param callable $f
     * @return Dict
     */
    public function map(callable $f): Dict
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

        return new static($newItems);
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }
}
