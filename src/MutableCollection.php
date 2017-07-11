<?php namespace Monolith\Collections;

use ArrayIterator;
use Countable;
use IteratorAggregate;

class MutableCollection implements IteratorAggregate, Countable {
    /** @var array */
    protected $items;

    public function __construct(array $items = []) {
        $this->items = $items;
    }

    public function count(): int {
        return count($this->items);
    }

    public function add($value) {
        $this->items[] = $value;
    }

    public function each(callable $f): void {
        foreach ($this->items as $i) {
            $f($i);
        }
    }

    public function equals(MutableCollection $that): bool {
        return get_class($this) === get_class($that) && $this->items === $that->items;
    }

    public function map(callable $f) : MutableCollection {
        return new static(array_map($f, $this->items));
    }

    public function reduce(callable $f, $initial = null) {
        return array_reduce($this->items, $f, $initial);
    }

    public function filter(callable $f): MutableCollection {
        $this->items = array_filter($this->items, $f);
    }

    public function first() {
        return array_values($this->items)[0];
    }

    public function toArray(): array {
        return $this->items;
    }

    public function copy(): MutableCollection {
        return clone $this;
    }

    public function merge(MutableCollection $c) {
        $this->items = array_merge($this->items, $c->toArray());
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