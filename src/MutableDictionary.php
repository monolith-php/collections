<?php namespace Monolith\Collections;

use Closure;
use Countable;
use ArrayIterator;
use IteratorAggregate;

final class MutableDictionary implements IteratorAggregate, Countable
{
    private function __construct(
        private Collection $keyLookupTable,
        private readonly Closure $keyHashFunction,
        private array $items = [],
    ) {
    }

    public function has(mixed $key): bool
    {
        $keyIndex = $this->keyLookupTable->indexFor($key);
        return array_key_exists($keyIndex, $this->items);
    }

    public function add(mixed $key, mixed $value): void
    {
        $newKeyLookupTable = self::addKeyToLookupTable(
            $this->keyLookupTable,
            $this->keyHashFunction,
            $key
        );

        $newItems = $this->items;
        $newItems[$newKeyLookupTable->indexFor($key)] = $value;

        $this->keyLookupTable = $newKeyLookupTable;
        $this->items = $newItems;
    }

    public function get(mixed $key)
    {
        return $this->items[$this->keyLookupTable->indexFor($key)] ?? null;
    }

    public function remove(mixed $key): void
    {
        $keyIndex = $this->keyLookupTable->indexFor($key);

        unset($this->items[$keyIndex]);

        $this->keyLookupTable = $this->keyLookupTable->filter(
            fn($tableKey) => ($this->keyHashFunction)($tableKey) !== ($this->keyHashFunction)($key)
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

    public function count(): int
    {
        return count($this->items);
    }

    public function merge(MutableDictionary $that): void
    {
        $this->reindexKeys(0);
        $thatCopy = $that->copy();
        $thatCopy->reindexKeys($this->count());

        $this->keyLookupTable = $this->keyLookupTable->merge(
            $that->keyLookupTable
        );

        $this->items = array_merge($this->items, $that->items);
    }

    public function copy(): self
    {
        return clone $this;
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

            $newItems[$newKeyLookupTable->indexFor($resultKey)] = $resultValue;
        }

        return new self(
            $newKeyLookupTable,
            $this->keyHashFunction,
            $newItems
        );
    }

    public function filter(?callable $f = null): self
    {
        $newItems = [];

        foreach ($this->items as $keyIndex => $value) {
            $key = $this->keyLookupTable->index($keyIndex);

            if ($f($value, $key)) {
                $newItems[$keyIndex] = $value;
            }
        }
        return new self(
            $this->keyLookupTable,
            $this->keyHashFunction,
            $newItems
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
            $keyedItems[$keyLookupTable->indexFor($key)] = $value;
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

    public function reindexKeys(
        int $keyOffset = 0
    ): void {
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

        $this->keyLookupTable = Collection::of($newKeyLookupTable);
        $this->items = $newItems;
    }
}
