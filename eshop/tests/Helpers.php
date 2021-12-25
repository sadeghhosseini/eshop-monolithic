<?php

namespace Tests\helpers;

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

class Result
{
    private array $models = [];
    private array $items = [];

    function pushModel($model)
    {
        $this->models[] = $model;
    }
    private function lastModel()
    {
        return $this->models[count($this->models) - 1];
    }
    private function lastItem()
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
            return $this->lastModel();
        }

        if ($name == 'lastItem') {
            return $this->lastItem();
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

function mit($message, $closure, $httpVerb = null, $path = null)
{
    $colorizedGroup = colorize([
        [$httpVerb ?? '', '95'],
        ' ',
        [$path ?? '', '92'],
        [' ', '39']
    ]);
    test("${colorizedGroup} - it ${message}", $closure);
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
    $keys = array_map(function($key) {
        return "{{$key}}";
    }, $keys);
    $values = array_values($labelValues);

    return str_replace(
        $keys,
        $values,
        $url
    );
}
