<?php namespace Monolith\Collections;

use Countable;
use ArrayIterator;
use IteratorAggregate;

class MutableCollection implements IteratorAggregate, Countable
{
    public function __construct(
        protected array $items = []
    ) {
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

    public function map(callable $f): static
    {
        return new static(array_map($f, $this->items));
    }

    public function flatMap(callable $f): static
    {
        return new static(array_merge(...array_map($f, $this->items)));
    }

    public function reduce(callable $f, $initial = null)
    {
        return array_reduce($this->items, $f, $initial);
    }

    public function filter(?callable $f = null): static
    {
        return is_null($f)
            ? new static(array_values(array_filter($this->items)))
            : new static(array_values(array_filter($this->items, $f)));
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
        $value = reset($this->items);

        if (false === $value) {
            return null;
        }
        
        return $value;
    }

    public function tail(): static
    {
        return new static(array_slice($this->items, 1));
    }

    public function copy(): static
    {
        return clone $this;
    }

    public function merge(MutableCollection $that)
    {
        if (get_class($this) !== get_class($that)) {
            throw CollectionTypeError::cannotMergeDifferentTypes($this, $that);
        }

        $this->items = array_merge($this->items, $that->toArray());
    }

    public function toArray(): array
    {
        return $this->items;
    }

    public function reverse(): static
    {
        $this->items = array_reverse($this->items);
        return $this;
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

    public function sort(?callable $f): self
    {
        usort($this->items, $f);
        return $this;
    }

    public function toCollection(): Collection
    {
        return new Collection($this->items);
    }

    public static function of(array $items): static
    {
        return new static($items);
    }

    public static function empty(): static
    {
        return new static;
    }

    public static function list(...$items): static
    {
        return new static($items);
    }
}