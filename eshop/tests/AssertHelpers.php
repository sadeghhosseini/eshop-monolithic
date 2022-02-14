<?php

namespace Tests;

use App\Helpers;
use App\Models\User;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Testing\Assert;
use Illuminate\Testing\TestResponse;
use stdClass;

trait AssertHelpers
{
    public function toEqualAll($expectedItems, $expectationClosure)
    {
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
    }

    public function assertMatchArray(array $expected, array $actual)
    {
        foreach ($expected as $key => $value) {
            Assert::assertArrayHasKey($key, $actual);

            Assert::assertEquals(
                $value,
                $actual[$key],
                sprintf(
                    'Failed asserting that an array has a key %s with the value %g.',
                    $key,
                    $actual[$key],
                ),
            );
        }

        return $this;
    }
    /**
     * @param boolean $strict if true, $expected and $actual must have the exact same $key and $values
     *      if false, only those items on $expected which have a corresponding key in $actual will be checked for euqality
     * @param array $expected
     * @param array $actual
     */
    public function assertMatchSubsetOfArray(array $expected, array $actual, bool $strict = false)
    {
        if ($expected instanceof stdClass || $actual instanceof stdClass) {
            throw new Exception('inputs of assertMatchSubsetOfArray must be arrays, instances of stdClass given');
        }
        foreach ($expected as $key => $value) {
            if (!$strict && !array_key_exists($key, $actual)) {
                continue;
            }
            Assert::assertArrayHasKey($key, $actual);
            if (
                is_array($value) && Helpers::isAssociativeArray($value) &&
                is_array($actual[$key]) && Helpers::isAssociativeArray($actual[$key])
            ) {
                $this->assertMatchSubsetOfArray($value, $actual[$key]);
            } else {
                if ($value instanceof stdClass || $actual[$key] instanceof stdClass) {
                    throw new Exception('inputs of assertMatchSubsetOfArray must be arrays, instances of stdClass given');
                }
                Assert::assertEquals(
                    $value,
                    $actual[$key],
                    sprintf(
                        'Failed asserting that an array has a key %s with the value %g.',
                        $key,
                        $actual[$key],
                    ),
                );
            }
        }

        return $this;
    }

    public function assertEqualsFields(mixed $expected, mixed $actual, $fields)
    {
        foreach ($fields as $field) {
            Assert::assertEquals(
                $expected?->$field ?? $expected[$field],
                $actual?->$field ?? $actual[$field],
                sprintf(
                    'Failed -> expected: %g, actual:%g',
                    $expected?->$field ?? $expected[$field],
                    $actual?->$field ?? $actual[$field],
                )
            );
        }
    }

    private function isArrayOrCollectionOfObjectsOrAssociativeArrays(Collection|array $items)
    {
        $itemsArray = ($items instanceof Collection) ? $items->toArray() : $items;
        foreach ($itemsArray as $item) {
            if (!is_object($item) && !Helpers::isAssociativeArray($item)) {
                return false;
            }
        }
        return true;
    }

    private function getFieldValue($item, $field)
    {
        $value = null;
        if ($item instanceof Collection) {
            $value = $item->get($field);
        } else if (Helpers::isAssociativeArray($item)) {
            $value = $item[$field];
        } else {
            $value = $item->$field;
        }
        return $value;
    }

    private function assertIsSubsetOf(
        Collection $expectedCollection,
        Collection $actualCollection,
        $uniqueKey,
        $fieldsToCheckEquality,
        $expectedTitle = 'expected',
        $actualTitle = 'actual',
    ) {
        $expectedCollection->each(function ($expectedItem) use ($actualCollection, $fieldsToCheckEquality, $uniqueKey, $expectedTitle, $actualTitle) {
            $foundActualItem = $actualCollection->filter(function ($actualItem) use ($expectedItem, $uniqueKey) {
                $expectedId = $expectedItem->$uniqueKey ?? $expectedItem[$uniqueKey] ?? null;
                $actualId = $actualItem->$uniqueKey ?? $actualItem[$uniqueKey] ?? null;
                if (!$expectedId || !$actualId) {
                    return false;
                }
                return $expectedId == $actualId;
            });
            Assert::assertFalse(
                $foundActualItem->isEmpty(),
                sprintf(
                    "Item with id of %s is missing from $actualTitle",
                    $expectedItem[$uniqueKey] ?? null
                )
            );
            foreach ($fieldsToCheckEquality as $field) {
                $expectedField = $this->getFieldValue($expectedItem, $field);
                $actualField = $this->getFieldValue($foundActualItem->first(), $field);
                Assert::assertNotNull(
                    // $this->coalesce(fn () => $expectedItem->$field, $expectedItem[$field]),
                    // $expectedItem->$field ?? $expectedItem[$field],
                    $expectedField,
                    // isset($expectedItem->$field) ? $expectedItem->$field : $expectedItem[$field],
                    sprintf("item of $expectedTitle does not contain %s key", $field),
                );
                Assert::assertNotNull(
                    // $this->coalesce(fn () => $foundActualItem->$field, $expectedItem[$field]),
                    // $foundActualItem->$field ?? $foundActualItem[$field],
                    $actualField,
                    sprintf("item of $actualTitle does not contain %s key", $field)
                );
                Assert::assertEquals(
                    // $this->coalesce(fn () => $expectedItem->$field, $expectedItem[$field]),
                    // $this->coalesce(fn () => $foundActualItem->$field, $expectedItem[$field]),
                    // $expectedItem->$field ?? $expectedItem[$field],
                    $expectedField,
                    // $foundActualItem->$field ?? $foundActualItem[$field],
                    $actualField,
                    sprintf(
                        "items with id of %s in both $expectedTitle and $actualTitle does not have the same value for %s",
                        // $expectedItem->$uniqueKey ?? $expectedItem[$uniqueKey],
                        $this->getFieldValue($expectedItem, $uniqueKey),
                        $field,
                    ),
                );
            }
        });
    }
    public function assertEqualArray(
        Collection|array $expected,
        Collection|array $actual,
        $fieldsToCheckEquality = [],
        $uniqueKey = 'id',
        bool $exactEquality = false
    ) {
        if (!$this->isArrayOrCollectionOfObjectsOrAssociativeArrays($expected)) {
            throw new Exception('assertEqual accepts array|collection of objects|associative-arrays as an argument for $expected');
        }

        if (!$this->isArrayOrCollectionOfObjectsOrAssociativeArrays($actual)) {
            throw new Exception('assertEqual accepts array|collection of objects|associative-arrays as an argument for $actual');
        }

        $expectedCollection = ($expected instanceof Collection) ? $expected : collect($expected);
        $actualCollection = ($actual instanceof Collection) ? $actual : collect($actual);

        /* if ($exactEquality) {
            Assert::assertEquals(
                $expectedCollection->count(),
                $actualCollection->count(),
                "expected and actual do not have the same length",
            );
        } */

        $this->assertIsSubsetOf(
            $expectedCollection,
            $actualCollection,
            $uniqueKey,
            $fieldsToCheckEquality,
        );


        if ($exactEquality) {
            $this->assertIsSubsetOf(
                $actualCollection,
                $expectedCollection,
                $uniqueKey,
                $fieldsToCheckEquality,
                'actual',
                'expected',
            );
        }
    }
}
