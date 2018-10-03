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

    public function add(string $key, $value): void
    {
        $this->items[$key] = $value;
    }

    public function get(string $key)
    {
        return isset($this->items[$key]) ? $this->items[$key] : null;
    }

    public function remove(string $key): void
    {
        unset($this->items[$key]);
    }

    public function toArray(): array
    {
        return $this->items;
    }

    public function merge(Map $that): Map
    {
        if (get_class($this) !== get_class($that)) {
            throw new CannotMergeCollectionsOfDifferentType(get_class($this) . ' != ' . get_class($that));
        }
        return new static(array_merge($this->items, $that->items));
    }

    public function copy(): Map
    {
        return clone $this;
    }
}
