<?php namespace spec\Monolith\Collections;

use PhpSpec\ObjectBehavior;
use Monolith\Collections\Dictionary;
use Monolith\Collections\Collection;
use Monolith\Collections\CollectionTypeError;
use spec\Monolith\Collections\Stubs\CollectionStub;

class CollectionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Collection::class);
    }

    function it_can_be_initialized_as_a_collection_of_items()
    {
        $this->beConstructedThrough('of', [[1, 2, 3]]);
        $this->count()->shouldBe(3);
    }

    function it_can_be_initialized_with_items()
    {
        $items = range(1, 10);
        $this->beConstructedWith($items);
        $this->count()->shouldBe(10);
    }

    function it_can_be_initialized_as_an_empty_collection()
    {
        $this->beConstructedThrough('empty', []);
        $this->count()->shouldBe(0);
    }

    function it_can_be_initialized_by_a_variadic_list()
    {
        $this->beConstructedThrough('list', [1, 2, 3]);
        $this->equals(new Collection([1, 2, 3]))->shouldBe(true);
    }

    function it_can_count_the_collection()
    {
        $collection = $this->add('hats')->add('cats')->add('dogs');
        $collection->count()->shouldBe(3);
    }

    function it_can_add_items_to_the_collection()
    {
        $this->add(1)->head()->shouldBe(1);
        $this->add(0)->head()->shouldBe(0);
    }

    function it_can_not_be_mutated_when_adding_items_to_the_collection()
    {
        $this->add(1);
        $this->count()->shouldBe(0);
    }

    function it_can_apply_a_function_to_each_item()
    {
        $i = 0;
        $collection = $this->add(1)->add(3)->add(5);

        $collection->each(
            function ($item) use (&$i) {
                $i += $item;
            }
        );

        expect($i)->shouldBe(9);
    }

    function it_can_apply_a_function_then_flatten_the_resulting_array()
    {
        $this->beConstructedWith([1, 2, 3]);

        $flatMap = $this->flatMap(
            function ($item) {
                return [$item, $item + 10, $item + 20];
            }
        );

        $flatMap->equals(new Collection([1, 11, 21, 2, 12, 22, 3, 13, 23]))->shouldBe(true);
    }

    function it_can_compare_two_collections_by_their_contents_with_contravariance()
    {
        $this->beConstructedWith([1, 2, 3]);

        $same = new Collection([1, 2, 3]);
        $different = new Collection([2, 3, 1]);
        $sameButWithDifferentType = new CollectionStub([1, 2, 3]);

        $this->equals($same)->shouldBe(true);
        $this->equals($different)->shouldBe(false);
        $this->equals($sameButWithDifferentType)->shouldBe(false);
    }

    function it_can_apply_a_function_to_each_item_and_return_a_new_collection_with_the_results()
    {
        $this->beConstructedWith([1, 2, 3]);

        $mapped = $this->map(
            function ($item) {
                return $item + 1;
            }
        );

        $mapped->equals(new Collection([2, 3, 4]))->shouldBe(true);
    }

    function it_can_apply_a_function_to_each_item_supplying_the_key_and_return_a_new_collection_with_the_results()
    {
        $this->beConstructedWith([1, 2, 3]);

        $mapped = $this->mapKeyValues(
            function ($index, $item) {
                return $item - $index;
            }
        );

        $mapped->equals(new Collection([1, 1, 1]))->shouldBe(true);
    }

    function it_can_apply_a_function_to_each_item_and_return_a_single_value_reduction()
    {
        $this->beConstructedWith([1, 2, 3]);

        $reduced = $this->reduce(
            function ($item, $accumulation) {
                return $item . ' ' . $accumulation;
            }, ''
        );

        $reduced->shouldBe(' 1 2 3');
    }

    function it_can_map_values_to_a_dict()
    {
        $this->beConstructedWith([1, 2, 3]);

        $dict = $this->toDictionary();

        $dict->shouldHaveType(Dictionary::class);

        $dict->toArray()->shouldBe(
            [
                0 => 1,
                1 => 2,
                2 => 3,
            ]
        );
    }

    function it_can_filter_a_collection_based_on_a_fitness_function()
    {
        $this->beConstructedWith([1, 2, 3]);
        $filtered = $this->filter(
            function ($num) {
                return $num != 2;
            }
        );
        $filtered->toArray()->shouldBe([1, 3]);
    }

    function it_can_filter_empty_values_out_of_the_collection()
    {
        $this->beConstructedWith([1, null, 3]);

        $this->toArray()->shouldBe([1, null, 3]);

        $filtered = $this->filter();

        $filtered->toArray()->shouldBe([1, 3]);
    }

    function it_can_return_the_first_item_in_the_collection()
    {
        $this->beConstructedWith([1, 2, 3]);
        $this->head()->shouldBe(1);
    }

    function it_returns_null_when_returning_the_first_item_of_an_empty_collection()
    {
        $this->beConstructedWith([]);
        $this->head()->shouldBe(null);
    }

    function it_can_return_a_collection_of_all_but_the_first_item_in_the_collection()
    {
        $this->beConstructedWith([1, 2, 3]);
        $this->tail()->equals(new Collection([2, 3]))->shouldBe(true);
    }

    function it_can_convert_the_collection_of_items_to_an_array_of_items()
    {
        $this->beConstructedWith([1, 2, 3]);
        $this->toArray()->shouldBe([1, 2, 3]);
    }

    function it_can_create_a_duplicate_collection()
    {
        $this->beConstructedWith([1, 2, 3]);

        $newCopy = $this->copy();

        $this->shouldNotBe($newCopy);
    }

    function it_can_merge_together_multiple_collections()
    {
        $this->beConstructedWith([1, 2, 3]);

        $merged = $this->merge(new Collection([4, 5, 6]));

        $merged->equals(new Collection([1, 2, 3, 4, 5, 6]))->shouldBe(true);
    }

    function it_can_only_merge_collections_that_share_a_type()
    {
        $this->beConstructedWith([1, 2, 3]);
        $this->shouldThrow(CollectionTypeError::class)->during('merge', [new CollectionStub]);
    }

    function it_can_reverse_the_order_of_items_in_the_collection()
    {
        $this->beConstructedWith([1, 2, 3]);
        $this->reverse()->equals(new Collection([3, 2, 1]))->shouldBe(true);
    }

    function it_can_be_iterated_over_in_normal_php_operations()
    {
        $this->beConstructedWith([1, 2, 3]);

        // phpspec is a little annoying here, so just loop the actual object
        $items = [];
        foreach ($this->getWrappedObject() as $item) {
            $items[] = $item;
        }

        $this->equals(new Collection($items))->shouldBe(true);
    }

    function it_can_tell_if_its_empty()
    {
        $emptyCollection = new Collection;
        expect($emptyCollection)->isEmpty()->shouldBe(true);

        $unemptyCollection = new Collection([1, 2]);
        expect($unemptyCollection)->isEmpty()->shouldBe(false);
    }

    function it_can_return_the_first_item_matching_a_positive_callback_result()
    {
        $this->beConstructedWith([1, 2, 3]);
        $item = $this->first(
            function ($item) {
                return $item == 2;
            }
        );
        $item->shouldBe(2);
    }

    function it_can_optionally_compare_equality_with_a_comparison_function()
    {
        $this->beConstructedWith([1, 2, 3, 4]);

        $different = new Collection([2, 3, 4, 5]);

        $this->equals($different)->shouldBe(false);

        $this->equals(
            $different, function ($one, $two) {
            return true;
        }
        )->shouldBe(true);

        $this->equals(
            $different, function ($one, $two) {
            return false;
        }
        )->shouldBe(false);

        $this->equals(
            $different, function ($one, $two) {
            return $one == $two - 1;
        }
        )->shouldBe(true);
    }

    function it_can_implode_scalar_items_with_a_delimiter()
    {
        $this->beConstructedWith([1, 2, 3, 4]);

        $this->implode(': ')->shouldBe('1: 2: 3: 4');
        $this->implode('z ')->shouldBe('1z 2z 3z 4');
    }

    function it_provides_php_array_access()
    {
        $this->beConstructedWith([1, 2, 3, 4]);

        # offset get
        $this[0]->shouldBe(1);

        # offset exists
        $collection = $this->getWrappedObject();

        expect(
            isset($collection[1])
        )->shouldBe(true);

        expect(
            isset($collection[100])
        )->shouldBe(false);
    }

    function it_can_sort_a_collection_with_a_sort_function()
    {
        $this->beConstructedWith([1, 2, 3, 4]);

        $new = $this->sort(
            function ($a, $b) {
                if ($a > $b) {
                    return -1;
                }
                if ($b > $a) {
                    return 1;
                }
                return 0;
            }
        );

        $new->toArray()->shouldBe([4, 3, 2, 1]);
    }

    function it_can_zip_two_collections_into_a_dictionary()
    {
        $this->beConstructedWith([1, 2, 3, 4]);

        $zipped = $this->zip(new Collection(['a', 'b', 'c', 'd']));

        $zipped->shouldBeAnInstanceOf(Dictionary::class);

        $zipped[0]->shouldBe(
            [
                1,
                'a',
            ]
        );

        $zipped[1]->shouldBe(
            [
                2,
                'b',
            ]
        );

        $zipped[2]->shouldBe(
            [
                3,
                'c',
            ]
        );

        $zipped[3]->shouldBe(
            [
                4,
                'd',
            ]
        );
    }

    function it_pads_the_shorter_of_two_zipped_collections_with_null()
    {
        $this->beConstructedWith([1, 2, 3, 4]);

        $zipped = $this->zip(new Collection(['a', 'b']));

        $zipped[2]->shouldBe(
            [
                3,
                null,
            ]
        );

        $zipped[3]->shouldBe(
            [
                4,
                null,
            ]
        );

        $zipped = $this->zip(new Collection(['a', 'b', 'c', 'd', 'e', 'f']));

        $zipped[4]->shouldBe(
            [
                null,
                'e',
            ]
        );

        $zipped[5]->shouldBe(
            [
                null,
                'f',
            ]
        );
    }

    function it_can_remove_duplicate_items()
    {
        $this->beConstructedWith([1, 2, 2, 4]);
        $this->unique()->toArray()->shouldBe([1, 2, 4]);
    }

    function it_can_remove_duplicate_items_based_on_a_hash_function()
    {
        $this->beConstructedWith([1, 2, 3, 4]);

        $this->unique(
            function ($item) {
                return $item % 2;
            }
        )->toArray()->shouldBe([3, 4]);
    }
}
