<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

uses(Tests\TestCase::class)->in('Feature');
uses(Tests\TestCase::class)->in('Integration');
/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

expect()->extend('toEqualAll', function ($expectedItems, $expectationClosure) {
    /** @var Model[] $expectedItems */
    /** @var  TestResponse $response */
    $response = $this->value;
    $responseItems = json_decode($response->baseResponse->content());
    foreach ($expectedItems as $expectedItem) {
        $respItem = array_filter($responseItems, function ($value) use ($expectedItem) {
            return $expectedItem->id == $value->id;
        });
        if (empty($respItem)) {
            // expect($expectedItem->id)->toEqual(null);
            throw new Exception(
                "response item with id of {$expectedItem->id} does not exist"
            );
        }
        $respItem = array_pop($respItem);
        if ($expectationClosure) {
            $expectationClosure($respItem, $expectedItem);
        }
    }
});

/**
 * @param $comparisonKey is a string that specifies the column or field on which the comparison must get done
 *  e.g
 * $a = [0 => ['id' => 1, 'title' => 'one'], 1 => ['id' => 2, 'title' => 'two', 'description' => 'this is nice']]
 * $b = [0 => ['id' => 2, 'title' => 'two'], 1 => ['id' => 1, 'title' => 'one']]
 * for the preceding example either of the columns id|title can be passed as $comparisonKey
 */
expect()->extend('toEqualArray', function ($expectedItems, $comparisonKey, $expectationClosure) {
    /** @var Model[] $expectedItems */
    /** @var  TestResponse $response */
    $inputItems = $this->value;

    foreach($inputItems as $inpItem) {
        $foundItems = array_filter($expectedItems, function($expItem) use($inpItem, $comparisonKey) {
            return ($expItem?->$comparisonKey ?? $expItem[$comparisonKey]) == ($inpItem?->$comparisonKey ?? $inpItem[$comparisonKey]);
        });

        if (empty($foundItems)) {
            throw new Exception(
                "response item with id of {$inpItem->id} does not exist"
            );
        }

        $foundItem = array_pop($foundItems);
        if ($expectationClosure) {
            $expectationClosure($inpItem, $foundItem);
        }

    }

    
});
expect()->extend('toEqualAll', function ($expectedItems, $expectationClosure) {
    /** @var Model[] $expectedItems */
    /** @var  TestResponse $response */
    $response = $this->value;
    $responseItems = json_decode($response->baseResponse->content());
    foreach ($expectedItems as $expectedItem) {
        $respItem = array_filter($responseItems, function ($value) use ($expectedItem) {
            return $expectedItem->id == $value->id;
        });
        if (empty($respItem)) {
            // expect($expectedItem->id)->toEqual(null);
            throw new Exception(
                "response item with id of {$expectedItem->id} does not exist"
            );
        }
        $respItem = array_pop($respItem);
        if ($expectationClosure) {
            $expectationClosure($respItem, $expectedItem);
        }
    }
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}
