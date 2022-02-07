<?php

namespace Tests;

use App\Helpers;
use App\Models\User;
use Exception;
use Illuminate\Testing\Assert;
use Illuminate\Testing\TestResponse;

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
    public function assertMatchSubsetOfArray(array $subset, array $superset)
    {
        foreach ($subset as $key => $value) {
            if (array_key_exists($key, $superset)) {
                Assert::assertArrayHasKey($key, $superset);
                // Helpers::die([$value, $superset[$key]], false);
                if (
                    is_array($value) && Helpers::isAssociativeArray($value) &&
                    is_array($superset[$key]) && Helpers::isAssociativeArray($superset[$key])
                ) {
                    $this->assertMatchSubsetOfArray($value, $superset[$key]);
                } else {
                    Assert::assertEquals(
                        $value,
                        $superset[$key],
                        sprintf(
                            'Failed asserting that an array has a key %s with the value %g.',
                            $key,
                            $superset[$key],
                        ),
                    );
                }
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
