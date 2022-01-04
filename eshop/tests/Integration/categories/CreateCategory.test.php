<?php

use App\Models\Category;
use function Pest\Laravel\post;
use function Tests\helpers\printEndpoint;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
$url = '/api/categories';
beforeAll(function() use ($url) {
    printEndpoint('POST', $url);
});



it('creates a category without parent', function () use ($url) {
    $category = Category::factory()->make();
    $response = post($url, $category->toArray());
    $response->assertOk();
    $expectedItemsAsArray = $category->makeHidden('parent_id')->toArray();
    expect($response->json())
        ->toMatchArray($expectedItemsAsArray);
});

it('creates a category with parent', function () use ($url) {
    $parentCategory = Category::factory()->create();
    $category = Category::factory(['parent_id' => $parentCategory->id])->make();
    $response = post($url, $category->toArray());
    $response->assertOk();
    $expectedResponseItemAsArray = $response->json();
    $expectedItemsAsArray = $category->toArray();
    expect($expectedResponseItemAsArray)
        ->toMatchArray($expectedItemsAsArray);
});

it('returns 400 if input data is not valid', function () use ($url) {
    $category = Category::factory(['title' => ''])->make();
    $response = post($url, $category->toArray());
    $response->assertStatus(400);
});