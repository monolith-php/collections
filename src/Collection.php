<?php namespace Monolith\Collections;

use ArrayIterator;
use Countable;
use IteratorAggregate;

class Collection implements IteratorAggregate, Countable
{
    /** @var array */
    protected $items;

    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    public static function list(...$items): Collection
    {
        return new static($items);
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function add($item): Collection
    {
        $items = $this->items;
        $items[] = $item;
        return new static($items);
    }

    public function each(callable $f): void
    {
        foreach ($this->items as $i) {
            $f($i);
        }
    }

    public function equals(Collection $that, callable $func = null): bool
    {
        if (is_null($func)) {
            return get_class($this) === get_class($that) && $this->items === $that->items;
        }

        // always unequal with different counts
        if ($this->count() != $that->count()) {
            return false;
        }

        $one = $this->toArray();
        $two = $that->toArray();

        foreach (range(0, $this->count() - 1) as $i) {
            if ( ! $func($one[0], $two[0])) return false;
        }

        return true;
    }

    public function map(callable $f): Collection
    {
        return new static(array_map($f, $this->items));
    }

    public function flatMap(callable $f): Collection
    {
        return new static(array_merge(...array_map($f, $this->items)));
    }

    public function reduce(callable $f, $initial = null)
    {
        return array_reduce($this->items, $f, $initial);
    }

    public function filter(callable $f): Collection
    {
        return new static(array_filter($this->items, $f));
    }

    public function first(callable $f)
    {
        foreach ($this->items as $item) {
            if ($f($item)) {
                return $item;
            }
        }
        return null;
    }

    public function head()
    {
        return reset($this->items) ?: null;
    }

    public function tail()
    {
        return new static(array_slice($this->items, 1));
    }

    public function toArray(): array
    {
        return $this->copy()->items;
    }

    public function copy(): Collection
    {
        return clone $this;
    }

    public function merge(Collection $that): Collection
    {
        if (get_class($this) !== get_class($that)) {
            throw CollectionTypeError::cannotMergeDifferentTypes($this, $that);
        }
        return new static(array_merge($this->items, $that->items));
    }

    public function reverse(): Collection
    {
        return new static(array_reverse($this->items));
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }
}