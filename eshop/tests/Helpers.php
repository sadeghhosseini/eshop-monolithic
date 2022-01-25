<?php

namespace Tests\helpers;

use Illuminate\Testing\TestResponse;

function colorize($items)
{
    $result = "";
    foreach ($items as $item) {
        if (is_array($item) && count($item) == 2) {
            $text = $item[0];
            $color = $item[1];
            $result .= "\033[${color}m${text}";
        } else {
            $text = $item[0];
            $result .= "\033[39m$text";
        }
    }
    return $result;
}

function actAsUserWithPermission($permission) {
    return \Laravel\Sanctum\Sanctum::actingAs(\App\Models\User::factory()->create()->givePermissionTo($permission));
}

function actAsUser() {
    return \Laravel\Sanctum\Sanctum::actingAs(\App\Models\User::factory()->create());
}


/**
 * @param $closure fn($closure) => beforeEach($closure)
 */
function setupAuthorization($closure)
{
    /* $filename = Backtrace::file();
    return new BeforeEachCall(TestSuite::getInstance(), $filename, function() {
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->registerPermissions();
    }); */
    $closure(function() {
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->registerPermissions();
    });
}

/**
 * 
 * @property array lastItem
 * @property object lastModel
 * @property array(object) models
 * @property array(array) items
 */
class Result
{
    private array $models = [];
    private array $items = [];

    function pushModel($model)
    {
        $this->models[] = $model;
    }
    function getLastModel()
    {
        return $this->models[count($this->models) - 1];
    }
    function getLastItem()
    {
        return $this->items[count($this->items) - 1];
    }
    function getModels()
    {
        return $this->models;
    }
    function getItems()
    {
        return $this->items;
    }
    function setItems($items)
    {
        $this->items = $items;
    }

    function __get($name)
    {
        if ($name == 'lastModel') {
            return $this->getLastModel();
        }

        if ($name == 'lastItem') {
            return $this->getLastItem();
        }

        if ($name == 'models') {
            return $this->getModels();
        }

        if ($name == 'items') {
            return $this->getItems();
        }
    }
}

function createRecords($CategoryClass, array $records): Result
{
    $result = new Result();
    $result->setItems($records);

    foreach ($records as $category) {
        $result->pushModel($CategoryClass::factory($category)->create());
    }
    return $result;
}

function printEndpoint($httpVerb, $path)
{
    $colorizedGroup = colorize([
        [$httpVerb ?? '', '95'],
        ' ',
        [$path ?? '', '92'],
        [' ', '39']
    ]);
    echo $colorizedGroup;
}

function u($url, ...$params)
{
    $labelValues = [];
    if (is_array($params[0])) {
        $labelValues = $params[0];
    } else {
        for ($i = 0; $i < count($params); $i += 2) {
            $labelValues[$params[$i]] = $params[$i + 1];
        }
    }
    return buildUrl($url, $labelValues);
}

function mit($message, $closure, $httpVerb = null, $path = null, $with = null)
{
    $colorizedGroup = colorize([
        [$httpVerb ?? '', '95'],
        ' ',
        [$path ?? '', '92'],
        [' ', '39']
    ]);

    if ($with) {
        test("${colorizedGroup} - it ${message}", $closure)->with($with);
    } else {
        test("${colorizedGroup} - it ${message}", $closure);
    }
}

function endpoint(string $endpoint, $callback)
{
    $colorizedText = colorize([
        ['+++', '95'],
        ['endpoint', '36'],
        [' -> ', '91'],
        [$endpoint, '92'],
    ]);
    it("is ${colorizedText}", function () use ($colorizedText) {
        expect(true)->toBeTrue();
    });
    $callback();
}

function group($verb, $url, $closure)
{
    $closure($verb, $url);
}

function buildUrl($url, $labelValues)
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


function getResponseBodyAsArray(TestResponse $response) {
    return json_decode($response->baseResponse->content());
}