<?php namespace spec\Monolith\Collections;

use Monolith\Collections\CannotMergeCollectionsOfDifferentType;
use Monolith\Collections\MutableCollection;
use PhpSpec\ObjectBehavior;

class MutableCollectionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(MutableCollection::class);
    }

    function it_can_be_initialized_with_items()
    {
        $items = range(1, 10);
        $this->beConstructedWith($items);
        $this->count()->shouldBe(10);
    }

    function it_can_be_initialized_by_a_variadic_list()
    {
        $this->beConstructedThrough('list', [1, 2, 3]);
        $this->equals(new MutableCollection([1, 2, 3]))->shouldBe(true);
    }

    function it_can_count_the_collection()
    {
        $this->add('hats');
        $this->add('cats');
        $this->add('dogs');
        $this->count()->shouldBe(3);
    }

    function it_can_add_items_to_the_collection()
    {
        $this->add(1);
        $this->head()->shouldBe(1);
    }

    function it_can_run_a_function_with_each_item()
    {
        $this->add(1);
        $this->add(3);
        $this->add(5);

        $i = 0;
        $this->each(function ($item) use (&$i) {
            $i += $item;
        });

        expect($i)->shouldBe(9);
    }

    function it_can_apply_a_function_then_flatten_the_resulting_array()
    {
        $this->beConstructedWith([1, 2, 3]);

        $flatMap = $this->flatMap(function($item) {
            return [$item, $item+10, $item+20];
        });

        $flatMap->equals(new MutableCollection([1, 11, 21, 2, 12, 22, 3, 13, 23]))->shouldBe(true);
    }

    function it_can_compare_two_collections_by_their_contents_with_contravariance()
    {
        $this->beConstructedWith([1, 2, 3]);

        $same = new MutableCollection([1, 2, 3]);
        $different = new MutableCollection([2, 3, 1]);
        $sameButWithDifferentType = new MutableCollectionStub([1, 2, 3]);

        $this->equals($same)->shouldBe(true);
        $this->equals($different)->shouldBe(false);
        $this->equals($sameButWithDifferentType)->shouldBe(false);
    }

    function it_can_apply_a_function_to_each_item_and_return_a_new_collection_with_the_results()
    {
        $this->beConstructedWith([1, 2, 3]);

        $mapped = $this->map(function ($item) {
            return $item + 1;
        });

        $mapped->equals(new MutableCollection([2, 3, 4]))->shouldBe(true);
    }

    function it_can_apply_a_function_to_each_item_and_return_a_single_value_reduction()
    {
        $this->beConstructedWith([1, 2, 3]);

        $reduced = $this->reduce(function ($item, $accumulation) {
            return $item . ' ' . $accumulation;
        }, '');

        $reduced->shouldBe(' 1 2 3');
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
        $this->tail()->equals(new MutableCollection([2, 3]))->shouldBe(true);
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

        $this->merge(new MutableCollection([4, 5, 6]));

        $this->equals(new MutableCollection([1, 2, 3, 4, 5, 6]))->shouldBe(true);
    }

    function it_can_only_merge_collections_that_share_a_type()
    {
        $this->beConstructedWith([1, 2, 3]);
        $this->shouldThrow(CannotMergeCollectionsOfDifferentType::class)->during('merge', [new MutableCollectionStub]);
    }

    function it_can_reverse_the_order_of_items_in_the_collection()
    {
        $this->beConstructedWith([1, 2, 3]);
        $this->reverse();
        $this->equals(new MutableCollection([3, 2, 1]))->shouldBe(true);
    }

    function it_can_be_iterated_over_in_normal_php_operations()
    {
        $this->beConstructedWith([1, 2, 3]);

        // phpspec is a little annoying here, so just loop the actual object
        $items = [];
        foreach ($this->getWrappedObject() as $item) {
            $items[] = $item;
        }

        $this->equals(new MutableCollection($items))->shouldBe(true);
    }

    function it_can_tell_if_its_empty()
    {
        $emptyCollection = new MutableCollection;
        expect($emptyCollection)->isEmpty()->shouldBe(true);

        $unemptyCollection = new MutableCollection([1, 2]);
        expect($unemptyCollection)->isEmpty()->shouldBe(false);
    }
}
