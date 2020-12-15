<?php namespace Monolith\Collections;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use function spec\Monolith\Collections\dd;

class Collection implements IteratorAggregate, Countable, ArrayAccess
{
    /** @var array */
    protected $items;

    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    public function contains($value): bool
    {
        return in_array($value, $this->items);
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

    public function equals(Collection $that, callable $func = null): bool
    {
        if (is_null($func)) {
            return get_class($this) === get_class($that) && $this->items === $that->items;
        }

        // always unequal with different counts
        if ($this->count() != $that->count()) {
            return false;
        }

        $one = $this->toArray();
        $two = $that->toArray();

        foreach (range(0, $this->count() - 1) as $i) {
            if ( ! $func($one[0], $two[0])) {
                return false;
            }
        }

        return true;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function toArray(): array
    {
        return $this->copy()->items;
    }

    public function copy(): Collection
    {
        return clone $this;
    }

    public function map(callable $f): Collection
    {
        return new static(array_map($f, $this->items));
    }

    public function mapKeyValues(callable $f): Collection
    {
        return new static(array_map($f, array_keys($this->items), $this->items));
    }

    public function flatMap(callable $f): Collection
    {
        return new static(array_merge(...array_map($f, $this->items)));
    }

    public function reduce(callable $f, $initial = null)
    {
        return array_reduce($this->items, $f, $initial);
    }

    public function filter(?callable $f = null): Collection
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

    public function tail(): Collection
    {
        return new static(array_slice($this->items, 1));
    }

    public function toDictionary(): Dictionary
    {
        return new Dictionary($this->toArray());
    }

    public function merge(Collection $that)
    {
        if (get_class($this) !== get_class($that)) {
            throw CollectionTypeError::cannotMergeDifferentTypes($this, $that);
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

    // would love to have a 'stotic' return type but we'll have to wait
    // until php 8

    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    public function sort(?callable $f): self
    {
        $items = $this->items;
        usort($items, $f);
        return Collection::of($items);
    }

    /**
     * Concatenates string items with a delimiter.
     *
     * @param string $delimiter
     * @return string
     */
    public function implode($delimiter = ', '): string
    {
        return implode($delimiter, $this->items);
    }

    /**
     * Whether a offset exists
     * @link https://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->items);
    }

    /**
     * Offset to retrieve
     * @link https://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return $this->items[$offset];
    }

    /**
     * Offset to set
     * This is not supported due to immutable nature.
     */
    public function offsetSet($offset, $value)
    {
        throw new CannotWriteToImmutableCollectionUsingArrayAccess();
    }

    /**
     * Offset to unset
     * This is not supported due to immutable nature.
     */
    public function offsetUnset($offset)
    {
        throw new CannotWriteToImmutableCollectionUsingArrayAccess();
    }

    public function zip(Collection $that): Dictionary
    {
        return new Dictionary(
            array_map(null, $this->items, $that->items)
        );
    }

    public function unique(?callable $f = null)
    {
        if (is_null($f)) {
            return new static(array_values(array_unique($this->items)));
        }

        $hashTable = new MutableDictionary();

        $this->each(
            function ($item) use ($hashTable, $f) {
                $hash = $f($item);
                $hashTable->add($hash, $item);
            }
        );

        return $hashTable->toCollection();
    }

    public static function of(array $items): Collection
    {
        return new static($items);
    }

    public static function empty(): Collection
    {
        return new static;
    }

    public static function list(...$items): Collection
    {
        return new static($items);
    }
}