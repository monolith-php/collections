<?php namespace Monolith\Collections;

final class CollectionTypeError extends CollectionException
{
    public static function cannotMergeDifferentTypes($one, $two)
    {
        $oneType = is_object($one) ? get_class($one) : gettype($one);
        $oneCount = $one->count();
        $twoType = is_object($two) ? get_class($two) : gettype($two);
        $twoCount = $two->count();

        $message=<<<EOF
Can not merge collection objects which have different types.

Attempted to merge a collection of type {$oneType} containing {$oneCount} items with\n
a collection of {$twoType} containing {$twoCount} elements.
EOF;

        return new static($message);
    }

    public static function canNotAddItemOfIncorrectType($item, string $collectionType, TypedCollection $collection)
    {
        $itemType = is_object($item) ? get_class($item) : gettype($item);
        $collectionClass = get_class($collection);

        $message=<<<EOF
Can not add an item of type {$itemType} to the typed collection {$collectionClass}.

Collection will only accept items of type {$collectionType}.
EOF;

        return new static($message);
    }
}