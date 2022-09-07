<?php namespace spec\Monolith\Collections;

use PhpSpec\ObjectBehavior;
use Monolith\Collections\Dictionary;
use spec\Monolith\Collections\Stubs\SubtypedDictionaryStub;

class DictionarySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Dictionary::class);
    }
    
    function it_can_be_constructed_from_keys_and_values()
    {
        $this->beConstructedThrough('fromKeysAndValues', [[0], [1]]);
        $this->get(0)->shouldBe(1);        
    }

    function it_can_be_constructed_as_a_dictionary_of_items()
    {
        $this->beConstructedThrough('of', [[0 => 1]]);
        $this->get(0)->shouldBe(1);
    }

    function it_can_be_constructed_as_an_empty_dictionary()
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

    function it_returns_new_dicts_when_adding_new_values_to_keys()
    {
        $newDict = $this->add('dogs', 'flavor');
        $this->get('dogs')->shouldBe(null);

        $newDict->get('dogs')->shouldBe('flavor');
    }

    function it_returns_new_dicts_when_removing_values_by_key()
    {
        $this->beConstructedWith(['dogs' => 'flavor']);

        $newDict = $this->remove('dogs');
        $this->get('dogs')->shouldBe('flavor');

        $newDict->get('dogs')->shouldBe(null);
    }

    function it_can_return_the_first_item_matching_a_positive_callback_result()
    {
        $this->beConstructedWith([1 => 'a', 2 => 'b', 3 => 'c']);
        $value = $this->first(
            function ($key, $value) {
                return $key == 2;
            }
        );
        $value->shouldBe('b');
    }

    function it_can_serialize_to_an_associative_array()
    {
        $this->beConstructedWith(
            [
                'dogs' => 'flavor',
                'cats' => 'levers',
            ]
        );

        $this->toArray()->shouldBe(
            [
                'dogs' => 'flavor',
                'cats' => 'levers',
            ]
        );
    }

    function it_can_count_its_items()
    {
        $this->beConstructedWith(
            [
                'dogs' => 'flavor',
                'cats' => 'levers',
            ]
        );

        $this->count()->shouldBe(2);

        // countable array
        expect(count($this->getWrappedObject()))->shouldBe(2);
    }

    function it_returns_new_dicts_when_merging_with_other_dicts()
    {
        $this->beConstructedWith(
            [
                'dogs' => 'flavor',
                'cats' => 'levers',
            ]
        );

        $newDict = $this->merge(
            new Dictionary(
                [
                    'loops' => 'groove',
                ]
            )
        );

        $this->get('loops')->shouldBe(null);

        $newDict->get('dogs')->shouldBe('flavor');
        $newDict->get('cats')->shouldBe('levers');
        $newDict->get('loops')->shouldBe('groove');
    }

    function it_can_be_copied()
    {
        $this->beConstructedWith(
            [
                'dogs' => 'flavor',
                'cats' => 'levers',
            ]
        );

        $newDict = $this->copy();

        $newDict->get('dogs')->shouldBe('flavor');
        $newDict->get('cats')->shouldBe('levers');
    }

    function it_can_be_iterated_over()
    {
        $this->beConstructedWith(
            [
                'dogs' => 'flavor',
                'cats' => 'levers',
            ]
        );

        foreach ($this->getWrappedObject() as $key => $value) {
            if ($key == 'dogs') {
                expect($value)->shouldBe('flavor');
            }
            if ($key == 'cats') {
                expect($value)->shouldBe('levers');
            }
        }
    }

    function it_can_walk_over_items()
    {
        $this->beConstructedWith(
            [
                'dogs' => 'flavor',
            ]
        );

        $this->each(
            function ($key, $value) {
                expect($value)->shouldBe('flavor');
                expect($key)->shouldBe('dogs');
            }
        );
    }


    function it_can_apply_a_function_to_each_item_and_return_a_new_dict_with_the_results()
    {
        $this->beConstructedWith(
            [
                'a' => 1,
                'b' => 2,
                'c' => 3,
            ]
        );

        $mapped = $this->map(
            function ($letter, $number) {
                return [++$letter => $number + 1];
            }
        );

        $mapped->toArray()->shouldBe(
            [
                'b' => 2,
                'c' => 3,
                'd' => 4,
            ]
        );
    }

    function it_can_reduce_a_dictionary_to_a_value()
    {
        $this->beConstructedWith(
            [
                'a' => 1,
                'b' => 2,
                'c' => 3,
            ]
        );

        $value = $this->reduce(
            function ($key, $value, $carry) {
                return $carry . $key . $value;
            },
            'hi'
        );
        
        $value->shouldBe('hia1b2c3');
    }

    function it_can_output_collections_containing_only_keys()
    {
        $this->beConstructedWith(
            [
                'a' => 1,
                'b' => 2,
                'c' => 3,
            ]
        );

        $this->keys()->toArray()->shouldBe(
            [
                'a',
                'b',
                'c',
            ]
        );
    }

    function it_can_output_collections_containing_only_values()
    {
        $this->beConstructedWith(
            [
                'a' => 1,
                'b' => 2,
                'c' => 3,
            ]
        );

        $this->values()->toArray()->shouldBe(
            [
                1,
                2,
                3,
            ]
        );
    }

    function it_can_filter_values_based_on_a_fitness_function()
    {
        $this->beConstructedWith(
            [
                'a' => 1,
                'b' => 2,
                'c' => 3,
            ]
        );

        $filtered = $this->filter(
            function ($value, $key) {
                return $value != 2;
            }
        );

        $filtered->toArray()->shouldBe(
            [
                'a' => 1,
                'c' => 3,
            ]
        );
    }

    function it_can_filter_keys_based_on_a_fitness_function()
    {
        $this->beConstructedWith(
            [
                'a' => 1,
                'b' => 2,
                'c' => 3,
            ]
        );

        $filtered = $this->filter(
            function ($value, $key) {
                return $key != 'a';
            }
        );

        $filtered->toArray()->shouldBe(
            [
                'b' => 2,
                'c' => 3,
            ]
        );
    }

    function it_can_dump_keys_and_return_a_collection()
    {
        $this->beConstructedWith(
            [
                'a' => 1,
                'b' => 2,
                'c' => 3,
            ]
        );

        $this->toCollection()->toArray()->shouldBe(
            [
                0 => 1,
                1 => 2,
                2 => 3,
            ]
        );
    }

    function it_returns_subtyped_collections_from_factory_methods()
    {
        $newDict = SubtypedDictionaryStub::of([]);
        expect($newDict)->shouldHaveType(SubtypedDictionaryStub::class);
    }

    function it_provides_php_array_access()
    {
        $this->beConstructedWith([1 => 2, 3 => 4]);

        # offset get
        $this[1]->shouldBe(2);

        # offset exists
        $dict = $this->getWrappedObject();

        expect(
            isset($dict[1])
        )->shouldBe(true);

        expect(
            isset($dict[100])
        )->shouldBe(false);
    }

    function it_knows_if_it_is_empty()
    {
        $this->beConstructedWith([]);
        $this->isEmpty()->shouldBe(true);
    }

    function it_knows_if_it_is_not_empty()
    {
        $this->beConstructedWith([1 => 2, 3 => 4]);
        $this->isEmpty()->shouldBe(false);
    }
}