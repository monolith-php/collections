<?php namespace spec\Monolith\Collections;

use PhpSpec\ObjectBehavior;
use Monolith\Collections\Collection;
use Monolith\Collections\CollectionTypeError;
use spec\Monolith\Collections\Stubs\SpecificTypeStub;

class TypedCollectionSpec extends ObjectBehavior {

    function let() {
        $this->beAnInstanceOf(TestTypedCollection::class);
        $this->beConstructedWith([
            new SpecificTypeStub(),
            new SpecificTypeStub()
        ]);
    }

    function it_can_be_constructed_with_elements_of_the_correct_type() {
        $this->beConstructedWith([
            new SpecificTypeStub(),
            new SpecificTypeStub()
        ]);
        $this->shouldHaveType(TestTypedCollection::class);
    }

    function it_cannot_be_constructed_with_elements_of_another_type() {
        $this->beConstructedWith([
            new SpecificTypeStub(),
            new \stdClass(),
        ]);
        $this->shouldThrow(CollectionTypeError::class)->duringInstantiation();
    }

    function it_can_add_a_value_of_the_correct_type() {
        $this->add(new SpecificTypeStub())->count()
            ->shouldBe(3);
        $this->add(new SpecificTypeStub())->count()
            ->shouldBe(3);
    }

    function it_cannot_add_a_value_of_another_type() {
        $this->shouldThrow(CollectionTypeError::class)
            ->during('add', [new \stdClass()]);
    }

    function it_can_map_to_a_typed_collection() {
        $this->map(function($i) { return $i; })
            ->shouldHaveType(TestTypedCollection::class);
    }

    function it_can_fall_back_to_generic_collections_when_mapping_to_other_types() {
        $this->map(function($i) { return 1; })
            ->shouldHaveType(Collection::class);
    }
}