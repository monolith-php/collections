<?php namespace spec\Monolith\Collections;

use Monolith\Collections\CannotMergeCollectionsOfDifferentType;
use Monolith\Collections\Collection;
use PhpSpec\ObjectBehavior;

class CollectionSpec extends ObjectBehavior {

    function it_is_initializable() {

        $this->shouldHaveType(Collection::class);
    }

    function it_can_be_initialized_with_items() {

        $items = range(1, 10);
        $this->beConstructedWith($items);
        $this->count()->shouldBe(10);
    }

    function it_can_be_initialized_by_a_variadic_list() {
        $this->beConstructedThrough('list', [1, 2, 3]);
        $this->equals(new Collection([1, 2, 3]))->shouldBe(true);
    }

    function it_can_count_the_collection() {

        $collection = $this->add('hats')->add('cats')->add('dogs');
        $collection->count()->shouldBe(3);
    }

    function it_can_add_items_to_the_collection() {

        $this->add(1)->head()->shouldBe(1);
    }

    function it_can_not_be_mutated_when_adding_items_to_the_collection() {

        $this->add(1);
        $this->count()->shouldBe(0);
    }

    function it_can_apply_a_function_to_each_item() {

        $i = 0;
        $collection = $this->add(1)->add(3)->add(5);

        $collection->each(function ($item) use (&$i) {

            $i += $item;
        });

        expect($i)->shouldBe(9);
    }

    function it_can_compare_two_collections_by_their_contents_with_contravariance() {

        $this->beConstructedWith([1, 2, 3]);

        $same = new Collection([1, 2, 3]);
        $different = new Collection([2, 3, 1]);
        $sameButWithDifferentType = new CollectionStub([1, 2, 3]);

        $this->equals($same)->shouldBe(true);
        $this->equals($different)->shouldBe(false);
        $this->equals($sameButWithDifferentType)->shouldBe(false);
    }

    function it_can_apply_a_function_to_each_item_and_return_a_new_collection_with_the_results() {

        $this->beConstructedWith([1, 2, 3]);

        $mapped = $this->map(function ($item) {

            return $item + 1;
        });

        $mapped->equals(new Collection([2, 3, 4]))->shouldBe(true);
    }

    function it_can_apply_a_function_to_each_item_and_return_a_single_value_reduction() {

        $this->beConstructedWith([1, 2, 3]);

        $reduced = $this->reduce(function ($item, $accumulation) {

            return $item . ' ' . $accumulation;
        }, '');

        $reduced->shouldBe(' 1 2 3');
    }

    function it_can_return_the_first_item_in_the_collection() {

        $this->beConstructedWith([1, 2, 3]);
        $this->head()->shouldBe(1);
    }

    function it_can_return_a_collection_of_all_but_the_first_item_in_the_collection() {

        $this->beConstructedWith([1, 2, 3]);
        $this->tail()->equals(new Collection([2, 3]))->shouldBe(true);
    }

    function it_can_convert_the_collection_of_items_to_an_array_of_items() {

        $this->beConstructedWith([1, 2, 3]);
        $this->toArray()->shouldBe([1, 2, 3]);
    }

    function it_can_create_a_duplicate_collection() {

        $this->beConstructedWith([1, 2, 3]);

        $newCopy = $this->copy();

        $this->shouldNotBe($newCopy);
    }

    function it_can_merge_together_multiple_collections() {

        $this->beConstructedWith([1, 2, 3]);

        $merged = $this->merge(new Collection([4, 5, 6]));

        $merged->equals(new Collection([1, 2, 3, 4, 5, 6]))->shouldBe(true);
    }

    function it_can_only_merge_collections_that_share_a_type() {

        $this->beConstructedWith([1, 2, 3]);
        $this->shouldThrow(CannotMergeCollectionsOfDifferentType::class)->during('merge', [new CollectionStub]);
    }

    function it_can_reverse_the_order_of_items_in_the_collection() {

        $this->beConstructedWith([1, 2, 3]);
        $this->reverse()->equals(new Collection([3, 2, 1]))->shouldBe(true);
    }

    function it_can_be_iterated_over_in_normal_php_operations() {

        $this->beConstructedWith([1, 2, 3]);

        // phpspec is a little annoying here, so just loop the actual object
        $items = [];
        foreach ($this->getWrappedObject() as $item) {
            $items[] = $item;
        }

        $this->equals(new Collection($items))->shouldBe(true);
    }
}
