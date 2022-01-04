<?php

use App\Models\Product;
use function Pest\Laravel\get;
use function Tests\helpers\printEndpoint;

use Illuminate\Foundation\Testing\RefreshDatabase;


uses(RefreshDatabase::class);
$url = '/api/products';
beforeAll(function () use ($url) {
    printEndpoint('GET', $url);
});

it('returns status 200 if no products exist', function () use ($url) {
    $response = get($url);
    $response->assertOk();
});

it('returns empty array if no products exist', function () use ($url) {
    $response = get($url);
    $products = json_decode($response->baseResponse->content());
    expect($products)->toBeArray();
    expect(count($products))->toEqual(0);
});

it('returns status 200 + all products in db', function () use ($url) {
    $productCount = 100;
    $products = Product::factory()->count($productCount)->create();
    $response = get($url);
    $response->assertOk();
    $response->assertJsonCount($productCount);
    expect($response->json())
        ->toMatchArray($products->toArray());
});
