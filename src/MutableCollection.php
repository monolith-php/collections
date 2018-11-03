<?php namespace Monolith\Collections;

use ArrayIterator;
use Countable;
use IteratorAggregate;

class MutableCollection implements IteratorAggregate, Countable
{
    /** @var array */
    protected $items;

    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    public static function list(...$items): MutableCollection
    {
        return new static($items);
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function add($value)
    {
        $this->items[] = $value;
    }

    public function each(callable $f): void
    {
        foreach ($this->items as $i) {
            $f($i);
        }
    }

    public function equals(MutableCollection $that): bool
    {
        return get_class($this) === get_class($that) && $this->items === $that->items;
    }

    public function map(callable $f): MutableCollection
    {
        return new static(array_map($f, $this->items));
    }

    public function flatMap(callable $f): MutableCollection
    {
        return new static(array_merge(...array_map($f, $this->items)));
    }

    public function reduce(callable $f, $initial = null)
    {
        return array_reduce($this->items, $f, $initial);
    }

    public function filter(callable $f)
    {
        $this->items = array_filter($this->items, $f);
    }

    public function head()
    {
        return isset($this->items[0]) ? array_values($this->items)[0] : null;
    }

    public function tail()
    {
        return new static(array_slice($this->items, 1));
    }

    public function toArray(): array
    {
        return $this->items;
    }

    public function copy(): MutableCollection
    {
        return clone $this;
    }

    public function merge(MutableCollection $that)
    {
        if (get_class($this) !== get_class($that)) {
            throw new CannotMergeCollectionsOfDifferentType(get_class($this) . ' != ' . get_class($that));
        }

        $this->items = array_merge($this->items, $that->toArray());
    }

    public function reverse()
    {
        return $this->items = array_reverse($this->items);
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }

    public function __clone()
    {
        foreach ($this->items as $i => $item) {
            $this->items[$i] = $item;
        }
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    public function toCollection() {
        return new Collection($this->items);
    }
}