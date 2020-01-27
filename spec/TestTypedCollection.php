<?php namespace spec\Monolith\Collections;

use Monolith\Collections\TypedCollection;
use spec\Monolith\Collections\Stubs\SpecificTypeStub;

final class TestTypedCollection extends TypedCollection
{
    protected $collectionType = SpecificTypeStub::class;
}