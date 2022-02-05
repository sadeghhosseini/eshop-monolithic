<?php

namespace Tests;

use App\Helpers;
use App\Models\User;
use Exception;
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
}
