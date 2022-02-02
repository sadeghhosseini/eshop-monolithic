<?php

use App\Models\Category;
use function Pest\Laravel\get;
use function Tests\helpers\buildUrl;
use function Tests\helpers\printEndpoint;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

$url = '/api/categories/{id}';
beforeAll(function() use ($url) {
    printEndpoint('GET', $url);
});



it('returns 404 if no category with the id of {id} exists', function () use ($url) {
    $response = get(buildUrl($url, ['id' => 1]));
    expect($response->baseResponse->content())->toBeJson();
    $response->assertStatus(404);
});

it('returns the category with id = {id}', function () use ($url) {
    $category = Category::factory()->create();
    $response = get(buildUrl($url, ['id' => $category->id]));
    expect($response->baseResponse->content())->toBeJson();
    $response->assertOk();
    
    $expectedItemsAsArray = $category->toArray();
    expect($response->json())->toEqualCanonicalizing($expectedItemsAsArray);
});