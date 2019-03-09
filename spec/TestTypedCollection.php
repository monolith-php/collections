<?php namespace spec\Monolith\Collections;

use Monolith\Collections\TypedCollection;

final class TestTypedCollection extends TypedCollection
{
    protected $collectionType = SpecificTypeStub::class;
}