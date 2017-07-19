<?php namespace Monolith\Collections;

class Map {
    private $items = [];

    public function hasKey(string $key): bool {
        return array_key_exists($key, $this->items);
    }

    public function add(string $key, $value) {
        $this->items[$key] = $value;
    }

    public function get(string $key) {
        return $this->items[$key];
    }

    public function toArray(): array {
        return $this->items;
    }
}