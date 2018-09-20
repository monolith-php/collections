<?php namespace Monolith\Collections;

class Map {

    private $items;

    public function __construct(array $items = []) {
        $this->items = $items;
    }

    public function has(string $key): bool {
        return array_key_exists($key, $this->items);
    }

    public function add(string $key, $value): void {
        $this->items[$key] = $value;
    }

    public function get(string $key) {
        return isset($this->items[$key]) ? $this->items[$key] : null;
    }

    public function toArray(): array {
        return $this->items;
    }

    public function copy(): Map {
        return clone $this;
    }
}
