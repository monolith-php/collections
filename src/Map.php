<?php namespace Monolith\Collections;

class Map
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

    public function add(string $key, $value): Map
    {
        $newItems = $this->items;
        $newItems[$key] = $value;
        return new static($newItems);
    }

    public function get(string $key)
    {
        return isset($this->items[$key]) ? $this->items[$key] : null;
    }

    public function remove(string $key): Map
    {
        $newItems = $this->items;
        unset($newItems[$key]);
        return new static($newItems);
    }

    public function toArray(): array
    {
        return $this->items;
    }

    public function merge(Map $that): Map
    {
        if (get_class($this) !== get_class($that)) {
            throw CollectionTypeError::cannotMergeDifferentTypes($this, $that);
        }
        $newItems = array_merge($this->items, $that->items);
        return new static($newItems);
    }

    public function copy(): Map
    {
        return clone $this;
    }
}
