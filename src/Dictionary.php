<?php namespace Monolith\Collections;

use Closure;
use Countable;
use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;

final class Dictionary implements IteratorAggregate, Countable, ArrayAccess
{
    private function __construct(
        private readonly Collection $keyLookupTable,
        private readonly Closure $keyHashFunction,
        private readonly array $items = [],
    ) {
    }

    public function containsMatch(callable $predicate): bool
    {
        $foundItem = $this->first($predicate);

        return ! is_null($foundItem);
    }

    public function has(mixed $key): bool
    {
        return array_key_exists(
            self::keyIndexForKey(
                $this->keyLookupTable,
                $this->keyHashFunction,
                $key
            ),
            $this->items
        );
    }

    public function add(mixed $key, mixed $value): self
    {
        $newKeyLookupTable = self::addKeyToLookupTable(
            $this->keyLookupTable,
            $this->keyHashFunction,
            $key
        );

        $newItems = $this->items;

        $keyIndex = self::keyIndexForKey(
            $newKeyLookupTable,
            $this->keyHashFunction,
            $key
        );

        $newItems[$keyIndex] = $value;

        return new self(
            $newKeyLookupTable,
            $this->keyHashFunction,
            $newItems,
        );
    }

    public function get(mixed $key)
    {
        $keyIndex = self::keyIndexForKey(
            $this->keyLookupTable,
            $this->keyHashFunction,
            $key
        );

        if (is_null($keyIndex)) {
            return null;
        }

        return $this->items[$keyIndex] ?? null;
    }

    public function remove(mixed $key): self
    {
        $newKeyLookupTable = $this->keyLookupTable;

        $newItems = $this->items;
        $keyIndex = self::keyIndexForKey(
            $newKeyLookupTable,
            $this->keyHashFunction,
            $key
        );

        unset($newItems[$keyIndex]);
        $newKeyLookupTable = $newKeyLookupTable->filter(
            fn($tableKey) => ($this->keyHashFunction)($tableKey) !== ($this->keyHashFunction)($key)
        );

        return new self(
            $newKeyLookupTable,
            $this->keyHashFunction,
            $newItems,
        );
    }

    public function toArray(): array
    {
        $array = [];

        foreach ($this->items as $keyIndex => $value) {
            $key = $this->keyLookupTable->index($keyIndex);

            if (is_object($key)) {
                $key = ($this->keyHashFunction)($key);
            }

            $array[$key] = $value;
        }

        return $array;
    }

    public function keys(): Collection
    {
        return new Collection(
            array_keys($this->toArray())
        );
    }

    public function values(): Collection
    {
        return new Collection(array_values($this->items));
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function merge(Dictionary $that): self
    {
        $dictOne = $this->reindexKeys(0);
        $dictTwo = $that->reindexKeys($dictOne->count());

        $newLookupTable = $dictOne->keyLookupTable->merge(
            $dictTwo->keyLookupTable
        );

        $newItems = array_merge($this->items, $that->items);

        return new self(
            $newLookupTable,
            $this->keyHashFunction,
            $newItems,
        );
    }

    public function copy(): self
    {
        return clone $this;
    }

    /**
     * Callable argument is $value, $key
     *
     * @param callable $f
     */
    public function each(callable $f)
    {
        foreach ($this->items as $keyIndex => $value) {
            $f($this->keyLookupTable->index($keyIndex), $value);
        }
    }

    /**
     * Don't forget to return [$key=>$value] to maintain associativity.
     *
     * @param callable $f
     * @return Dictionary
     * @throws DictionaryMapFunctionHasIncorrectReturnFormat
     */
    public function map(callable $f): self
    {
        $newItems = [];
        $newKeyLookupTable = $this->keyLookupTable;

        foreach ($this->items as $keyIndex => $value) {
            $key = $newKeyLookupTable->index($keyIndex);
            $result = $f($key, $value);

            $resultKey = key($result);
            $resultValue = $result[$resultKey];

            if (
                count($result) != 1 ||
                ! is_array($result)
            ) {
                throw new DictionaryMapFunctionHasIncorrectReturnFormat("When calling `map` on a Dict the function must always use this format: return [key=>value]. Received " . json_encode($result) . " instead.");
            }

            $newKeyLookupTable = self::addKeyToLookupTable(
                $newKeyLookupTable,
                $this->keyHashFunction,
                $resultKey
            );

            $newKeyIndex = self::keyIndexForKey(
                $newKeyLookupTable,
                $this->keyHashFunction,
                $resultKey
            );

            $newItems[$newKeyIndex] = $resultValue;
        }

        return new self(
            $newKeyLookupTable,
            $this->keyHashFunction,
            $newItems
        );
    }

    /**
     * Reduce function takes 3 arguments:
     *
     * function ($key, $value, $carry) {
     * }
     *
     * The value returned will be the carry during the next iteration.
     *
     * @param callable $f
     * @param $initialValue
     * @return mixed
     */
    public function reduce(callable $f, $initialValue): mixed
    {
        $carry = $initialValue;

        foreach ($this->items as $keyIndex => $value) {
            $key = $this->keyLookupTable->index($keyIndex);
            $carry = $f($key, $value, $carry);
        }

        return $carry;
    }

    /**
     * The arguments to the callback function are in the order of VALUE, KEY
     * @param callable|null $f
     * @return Dictionary
     */
    public function filter(?callable $f = null): self
    {
        $newItems = [];

        foreach ($this->items as $keyIndex => $value) {
            $key = $this->keyLookupTable->index($keyIndex);

            if ($f($value, $key)) {
                $newItems[$keyIndex] = $value;
            }
        }
        return new Dictionary(
            $this->keyLookupTable,
            $this->keyHashFunction,
            $newItems
        );
    }

    public function firstKey(callable $f)
    {
        foreach ($this->items as $keyIndex => $value) {
            $key = $this->keyLookupTable->index($keyIndex);

            if ($f($key, $value)) {
                return $key;
            }
        }
        return null;
    }

    public function first(callable $f)
    {
        foreach ($this->items as $keyIndex => $value) {
            $key = $this->keyLookupTable->index($keyIndex);

            if ($f($key, $value)) {
                return $value;
            }
        }
        return null;
    }

    public function flip(): self
    {
        return Dictionary::of(
            array_flip(
                $this->toArray()
            )
        );
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }

    public function toCollection(): Collection
    {
        return new Collection(array_values($this->items));
    }

    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    # Array Access

    /**
     * @inheritDoc
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new CannotWriteToImmutableDictionaryUsingArrayAccess();
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset(mixed $offset): void
    {
        throw new CannotWriteToImmutableDictionaryUsingArrayAccess();
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    public static function of(
        array $associativeArray,
        ?Closure $keyHashFunction = null
    ): self {
        $keyHashFunction ??= self::keyToSplObjectHash(...);

        $keyLookupTable = Collection::of(
            array_keys($associativeArray)
        )->reduce(
            fn(Collection $lookupTable, mixed $key) => self::addKeyToLookupTable(
                $lookupTable,
                $keyHashFunction,
                $key
            ),
            Collection::empty()
        );

        $keyedItems = [];

        foreach ($associativeArray as $key => $value) {
            $keyIndex = self::keyIndexForKey(
                $keyLookupTable,
                $keyHashFunction,
                $key
            );
            $keyedItems[$keyIndex] = $value;
        }

        return new self(
            $keyLookupTable,
            $keyHashFunction,
            $keyedItems
        );
    }

    public static function empty(
        ?Closure $keyHashFunction = null
    ): self {
        return new self(
            Collection::empty(),
            $keyHashFunction ?? self::keyToSplObjectHash(...),
            [],
        );
    }


    public static function fromKeysAndValues(
        array $keys,
        array $values
    ): self {
        return self::of(
            array_combine($keys, $values)
        );
    }

    public static function keyToSplObjectHash(
        mixed $key
    ): string {
        if (is_object($key)) {
            return spl_object_hash($key);
        }

        return (string) $key;
    }

    private static function addKeyToLookupTable(
        Collection $lookupTable,
        Closure $keyHashFunction,
        mixed $keyToAdd
    ): Collection {
        /*
         * key already exists
         */
        if (
            $lookupTable->containsMatch(
                fn($key) => $keyHashFunction($key) === $keyHashFunction($keyToAdd)
            )
        ) {
            return $lookupTable;
        }

        /*
         * add key
         */
        return $lookupTable->add($keyToAdd);
    }

    private static function keyIndexForKey(
        Collection $keyLookupTable,
        callable $keyHashFunction,
        mixed $key
    ): ?int {
        return $keyLookupTable->firstIndex(
            fn($item) => $keyHashFunction($item) == $keyHashFunction($key)
        );
    }

    public function reindexKeys(
        int $keyOffset = 0
    ): self {
        $newKeyLookupTable = [];
        $newItems = [];

        $findKeyForValue = function (array $array, mixed $valueToFind): ?int {
            foreach ($array as $key => $value) {
                if ($value === $valueToFind) {
                    return $key;
                }
            }

            return null;
        };

        foreach ($this->items as $keyIndex => $value) {
            $key = $this->keyLookupTable->index($keyIndex);

            $foundKey = false;
            foreach ($newKeyLookupTable as $lookupKey) {
                if (($this->keyHashFunction)($lookupKey) === ($this->keyHashFunction)($key)) {
                    $foundKey = true;
                }
            }

            if ( ! $foundKey) {
                $newKeyLookupTable[$keyOffset + count($newKeyLookupTable)] = $key;
            }

            $newItems[$findKeyForValue($newKeyLookupTable, $key)] = $value;
        }

        return new Dictionary(
            Collection::of($newKeyLookupTable),
            $this->keyHashFunction,
            $newItems
        );
    }
}