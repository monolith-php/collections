<?php namespace Monolith\Collections;

use ArrayIterator;
use Countable;
use IteratorAggregate;

class Collection implements IteratorAggregate, Countable {

    /** @var array */
    protected $items;

    public function __construct(array $items = []) {
        $this->items = $items;
    }

    public function count(): int {
        return count($this->items);
    }

    public function add($value): Collection {
        $items = $this->items;
        $items[] = $value;
        return new static($items);
    }

    public function each(callable $f): void {
        foreach ($this->items as $i) {
            $f($i);
        }
    }

    public function equals(Collection $that): bool {
        return get_class($this) === get_class($that) && $this->items === $that->items;
    }

    public function map(callable $f): Collection {
        return new static(array_map($f, $this->items));
    }

    public function reduce(callable $f, $initial = null): Collection {
        return new static(array_reduce($this->items, $f, $initial));
    }

    public function filter(callable $f): Collection {
        return new static(array_filter($this->items, $f));
    }

    public function first() {
        return array_values($this->items)[0];
    }

    public function toArray(): array {
        return $this->copy()->items;
    }

    public function copy(): Collection {
        return clone $this;
    }

    public function merge(Collection $that): Collection {
        return new static(array_merge($this->items, $that->items));
    }

    public function reverse(): Collection {
        return new static(array_reverse($this->items));
    }

    public function getIterator(): ArrayIterator {
        return new ArrayIterator($this->items);
    }

    public function __clone() {
        foreach ($this->items as $i => $item) {
            $this->items[$i] = $item;
        }
    }
}