<?php

use App\Models\Category;
use App\Models\Image;
use App\Models\Product;
use App\Models\Property;

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


it ('gets all the properties of certain product', function () use ($url) {
    $product = Product::factory()->has(Property::factory()->count(5))->create();
    $response = get("/api/products/$product->id/properties");
    $response->assertOk();
    expect($response->json())->toMatchArray($product->properties->toArray());
});

it ('gets all the images of certain product', function () use ($url) {
    $product = Product::factory()->has(Image::factory()->count(5))->create();
    $response = get("/api/products/$product->id/images");
    $response->assertOk();
    expect($response->json())->toMatchArray($product->images->toArray());
});

it ('gets the category of certain product', function () use ($url) {
    $product = Product::factory()->for(Category::factory())->create();
    $response = get("/api/products/$product->id/category");
    $response->assertOk();
    expect($response->json())->toMatchArray($product->category->toArray());
});