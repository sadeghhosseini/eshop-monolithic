<?php

namespace Tests;

use App\Helpers;
use App\Models\User;
use Exception;
use Illuminate\Testing\Assert;
use Illuminate\Testing\TestResponse;
use stdClass;

trait AssertHelpers
{
    public function assertEqualArray($inputItems, $expectedItems, $comparisonKey, $expectationClosure)
    {
        /** @var Model[] $expectedItems */
        /** @var  TestResponse $response */

        foreach ($inputItems as $inpItem) {
            $foundItems = array_filter($expectedItems, function ($expItem) use ($inpItem, $comparisonKey) {
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
    }


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
}
