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

    public function equals(Collection $that): bool
    {
        return get_class($this) === get_class($that) && $this->items === $that->items;
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
        return $this->copy()->items;
    }

    public function copy(): Collection
    {
        return clone $this;
    }

    public function merge(Collection $that): Collection
    {
        if (get_class($this) !== get_class($that)) {
            throw new CannotMergeCollectionsOfDifferentType(get_class($this) . ' != ' . get_class($that));
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