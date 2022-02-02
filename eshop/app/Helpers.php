<?php

namespace App;

use Illuminate\Testing\TestResponse;

use function Tests\helpers\getResponseBody;

class Helpers
{
    public static function die(TestResponse|array|string $data)
    {
        if (is_array($data)) {
            echo "\n\n**dying\n";
            print_r($data);
            echo "\n\n **died\n";
        } else if ($data instanceof TestResponse) {
            echo "\n\n**dying\n";
            print_r(getResponseBody($data));
            echo "\n\n **died\n";
        } else {
            echo "\n\n **dying\n";
            echo $data;
            echo "\n\n**died\n";
        }
        die;
    }
}
