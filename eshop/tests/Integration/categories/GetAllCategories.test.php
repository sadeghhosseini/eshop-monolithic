<?php

use App\Models\Category;
use function Pest\Laravel\get;
use function Tests\helpers\printEndpoint;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

$url = '/api/categories';
beforeAll(function() use ($url) {
    printEndpoint('GET', $url);
});

it('returns 200 if no categories exist', function () use ($url) {
    $response = get($url);
    $response->assertStatus(200);
});

it('returns empty array, if category table is empty', function ()  use ($url) {
    $response = get($url);
    $response->assertExactJson([]);
});

it('returns all the category records in db', function ()  use ($url) {
    $count = 1;
    $categories = Category::factory()
        ->count($count)
        ->create();

    $response = get($url);
    $response->assertJsonCount($count);

    $responseItemsAsArray = $response->json();
    $expectedItemsAsArray = $categories->toArray();
    expect($responseItemsAsArray)
        ->toEqualCanonicalizing($expectedItemsAsArray);
    
});