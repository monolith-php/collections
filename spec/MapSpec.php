<?php namespace spec\Monolith\Collections;

use Monolith\Collections\Map;
use PhpSpec\ObjectBehavior;

class MapSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Map::class);
    }

    function it_can_be_constructed_with_an_associative_array_of_items()
    {
        $this->beConstructedWith(['hats' => 'cats']);
        $this->get('hats')->shouldBe('cats');
    }

    function it_can_be_constructed_with_a_numerically_indexed_array_of_items()
    {
        $this->beConstructedWith(['hats', 'cats']);
        $this->get(0)->shouldBe('hats');
        $this->get(1)->shouldBe('cats');
    }

    function it_can_tell_you_if_a_key_exists()
    {
        $this->beConstructedWith(['hats' => 'cats']);
        $this->get('hats')->shouldBe('cats');
        $this->get('bats')->shouldBe(null);
    }

    function it_can_retrieve_values_by_key()
    {
        $this->beConstructedWith(['dogs' => 'robot']);
        $this->get('dogs')->shouldBe('robot');
    }

    function it_returns_new_maps_when_adding_new_values_to_keys()
    {
        $newMap = $this->add('dogs', 'flavor');
        $this->get('dogs')->shouldBe(null);

        $newMap->get('dogs')->shouldBe('flavor');
    }

    function it_returns_new_maps_when_removing_values_by_key()
    {
        $this->beConstructedWith(['dogs' => 'flavor']);

        $newMap = $this->remove('dogs');
        $this->get('dogs')->shouldBe('flavor');

        $newMap->get('dogs')->shouldBe(null);
    }

    function it_can_serialize_to_an_associative_array()
    {
        $this->beConstructedWith([
            'dogs' => 'flavor',
            'cats' => 'levers',
        ]);

        $this->toArray()->shouldBe([
            'dogs' => 'flavor',
            'cats' => 'levers',
        ]);
    }

    function it_can_count_its_items()
    {
        $this->beConstructedWith([
            'dogs' => 'flavor',
            'cats' => 'levers',
        ]);

        $this->count()->shouldBe(2);

        // countable array
        expect(count($this->getWrappedObject()))->shouldBe(2);
    }

    function it_returns_new_maps_when_merging_with_other_maps()
    {
        $this->beConstructedWith([
            'dogs' => 'flavor',
            'cats' => 'levers',
        ]);

        $newMap = $this->merge(new Map([
            'loops' => 'groove',
        ]));

        $this->get('loops')->shouldBe(null);

        $newMap->get('dogs')->shouldBe('flavor');
        $newMap->get('cats')->shouldBe('levers');
        $newMap->get('loops')->shouldBe('groove');
    }

    function it_can_be_copied()
    {
        $this->beConstructedWith([
            'dogs' => 'flavor',
            'cats' => 'levers',
        ]);

        $newMap = $this->copy();

        $newMap->get('dogs')->shouldBe('flavor');
        $newMap->get('cats')->shouldBe('levers');
    }

    function it_can_be_iterated_over()
    {
        $this->beConstructedWith([
            'dogs' => 'flavor',
            'cats' => 'levers',
        ]);

        foreach ($this->getWrappedObject() as $key => $value) {
            if ($key == 'dogs') expect($value)->shouldBe('flavor');
            if ($key == 'cats') expect($value)->shouldBe('levers');
        }
    }
}
