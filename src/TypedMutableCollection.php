<?php namespace Monolith\Collections;

abstract class TypedMutableCollection extends MutableCollection {
    public function __construct(array $items = []) {
        $this->guardType($items);
        parent::__construct($items);
    }

    abstract protected function collectionType(): string;

    protected function guardType($items) : void {
        // this allows guardType($items) to receive
        // both a single item or an array of items
        if ( ! array($items)) {
            $items = array($items);
        }

        // throw an exception if any items are not
        // an instance of the required type
        foreach ($items as $item) {
            $collectionType = $this->collectionType();
            if ($item instanceof $collectionType) {
                continue;
            }
            $name = (is_object($item) ? get_class($item) : $item);
            throw new \InvalidArgumentException("Got {$name} but expected {$collectionType}");
        }
    }

    public function add($value) {
        $this->guardType($value);
        parent::add($value);
    }

    public function map(Callable $f) : MutableCollection {
        try {
            return new static(array_map($f, $this->items));
        } catch (\Exception $e) {
            return new MutableCollection(array_map($f, $this->items));
        }
    }
}