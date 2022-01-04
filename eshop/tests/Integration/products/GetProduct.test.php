<?php

use App\Models\Category;
use App\Models\Product;
use function Pest\Laravel\get;
use function Tests\helpers\buildUrl;
use function Tests\helpers\printEndpoint;

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

$url = '/api/products/{id}';
beforeAll(function () use ($url) {
    printEndpoint('GET', $url);
});


it('returns 404 if {id} matches no product', function () use ($url) {
    $response = get(buildUrl($url, ['id' => 300]));
    $response->assertStatus(404);
});

it('returns product with id={id}', function () use ($url) {
    $product = Product::factory()
        ->for(Category::factory())
        ->create();

    $response = get(buildUrl($url, ['id' => $product->id]));
    expect($response->baseResponse->content())->toBeJson();
    $response->assertOk();

    expect($response->json())
        ->toEqualCanonicalizing($product->toArray());
});
