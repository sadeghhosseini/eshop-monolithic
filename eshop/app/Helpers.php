<?php

namespace App;

use Illuminate\Testing\TestResponse;

use function Tests\helpers\getResponseBody;

class Helpers
{
    public static function die(TestResponse|array|string $data, $shouldDie = true)
    {
        if (is_array($data)) {
            echo $shouldDie ? "\n\n**dying\n" : "";
            print_r($data);
            echo $shouldDie ? "\n\n **died\n" : "";
        } else if ($data instanceof TestResponse) {
            echo $shouldDie ? "\n\n**dying\n" : "";
            print_r(static::getResponseBody($data));
            echo $shouldDie ? "\n\n **died\n" : "";
        } else {
            echo $shouldDie ? "\n\n **dying\n" : "";
            echo $data;
            echo $shouldDie ? "\n\n**died\n" : "";
        }
        if ($shouldDie) {
            die;
        }
    }

    private static function getResponseBody(TestResponse $response): mixed
    {
        return json_decode($response->baseResponse->content());
    }

    public static function isAssociativeArray($arr)
    {
        if (!is_array($arr)) {
            return false;
        }
        if (array() === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}
