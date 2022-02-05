<?php

namespace Tests;

use App\Helpers;
use App\Models\User;
use Illuminate\Testing\TestResponse;

trait AddsHelpers
{
    public abstract function getUrl();

    public function actAsUserWithPermission($permission): User
    {
        return \Laravel\Sanctum\Sanctum::actingAs(\App\Models\User::factory()->create()->givePermissionTo($permission));
    }

    function setupAuthorization()
    {
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->registerPermissions();
    }

    private function isAssociativeArray(array $arr)
    {
        if (array() === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    public function url(...$params)
    {
        return $this->u($this->getUrl(), ...$params);
    }

    public function u($url, ...$params)
    {
        $labelValues = [];
        if (is_array($params[0])) {
            $labelValues = $params[0];
        } else {
            for ($i = 0; $i < count($params); $i += 2) {
                $labelValues[$params[$i]] = $params[$i + 1];
            }
        }
        return $this->buildUrl($url, $labelValues);
    }

    public function buildUrl($url, $labelValues)
    {
        $keys = array_keys($labelValues);
        $keys = array_map(function ($key) {
            return "{{$key}}";
        }, $keys);
        $values = array_values($labelValues);

        return str_replace(
            $keys,
            $values,
            $url
        );
    }

    public function actAsUser(): User
    {
        return \Laravel\Sanctum\Sanctum::actingAs(\App\Models\User::factory()->create());
    }

    public function getResponseBody(TestResponse $response): mixed
    {
        return json_decode($response->baseResponse->content());
    }

    public function rpost($data = [])
    {
        return $this->post($this->getUrl(), $data);
    }

    public function rpatch(array $params = [], $data = [])
    {
        return $this->patch($this->makeUrl($this->getUrl(), $params), $data);
    }

    public function rget(array $params = [])
    {
        return $this->get($this->makeUrl($this->getUrl(), $params));
    }

    public function rdelete(array $params = [])
    {
        return $this->delete($this->makeUrl($this->getUrl(), $params));
    }

    private function makeUrl($url, $params)
    {
        if (empty($params)) {
            return $url;
        }

        $labelValues = [];
        if ($this->isAssociativeArray($params)) {
            $labelValues = $params;
        } else { #non-associative array
            /*
            * convert to associative array
            * ['id', 3, 'title', 'book'] is 
            * converted to  ['id' => 3, 'title' => 'book']
            */
            for ($i = 0; $i < count($params); $i += 2) {
                $labelValues[$params[$i]] = $params[$i + 1];
            }
        }
        return $this->buildUrl($url, $labelValues);
    }
}
