<?php namespace spec\Monolith\Collections;

use Monolith\Collections\MutableDict;
use PhpSpec\ObjectBehavior;

class MutableDictSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(MutableDict::class);
    }

    function it_can_be_initialized_as_an_empty_dictionary()
    {
        $this->beConstructedThrough('empty', []);
        $this->count()->shouldBe(0);
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

    function it_can_add_new_values_to_keys()
    {
        $this->add('dogs', 'flavor');
        $this->get('dogs')->shouldBe('flavor');
    }

    function it_can_remove_values_by_key()
    {
        $this->add('dogs', 'flavor');
        $this->remove('dogs');
        $this->get('dogs')->shouldBe(null);
    }

    function it_can_serialize_to_an_associative_array()
    {
        $this->add('dogs', 'flavor');
        $this->add('cats', 'levers');

        $this->toArray()->shouldBe([
            'dogs' => 'flavor',
            'cats' => 'levers',
        ]);
    }

    function it_can_be_merged_with_other_mutable_maps()
    {
        $this->add('dogs', 'flavor');
        $this->add('cats', 'levers');

        $this->merge(new MutableDict([
            'loops' => 'groove',
        ]));

        $this->get('dogs')->shouldBe('flavor');
        $this->get('cats')->shouldBe('levers');
        $this->get('loops')->shouldBe('groove');
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

    function it_can_be_copied()
    {
        $this->add('dogs', 'flavor');
        $this->add('cats', 'levers');

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
            if ($key == 'dogs') {
                expect($value)->shouldBe('flavor');
            }
            if ($key == 'cats') {
                expect($value)->shouldBe('levers');
            }
        }
    }

    function it_can_apply_a_function_to_each_item_and_return_a_new_dict_with_the_results()
    {
        $this->beConstructedWith([
                'a' => 1,
                'b' => 2,
                'c' => 3,
            ]
        );

        $mapped = $this->map(function ($number, $letter) {
            return [++$letter => $number + 1];
        });

        $mapped->toArray()->shouldBe([
            'b' => 2,
            'c' => 3,
            'd' => 4,
        ]);
    }

    function it_can_filter_values_based_on_a_fitness_function()
    {
        $this->beConstructedWith([
            'a' => 1,
            'b' => 2,
            'c' => 3,
        ]);

        $filtered = $this->filter(function ($value, $key) {
            return $value != 2;
        });

        $filtered->toArray()->shouldBe([
            'a' => 1,
            'c' => 3,
        ]);
    }

    function it_can_filter_keys_based_on_a_fitness_function()
    {
        $this->beConstructedWith([
            'a' => 1,
            'b' => 2,
            'c' => 3,
        ]);

        $filtered = $this->filter(function ($value, $key) {
            return $key != 'a';
        });

        $filtered->toArray()->shouldBe([
            'b' => 2,
            'c' => 3,
        ]);
    }
}