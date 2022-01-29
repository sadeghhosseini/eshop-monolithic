<?php

use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Tests\helpers\actAsUser;
use function Tests\helpers\getResponseBodyAsArray;
use function Tests\helpers\printEndpoint;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
uses(RefreshDatabase::class);
$url = '/api/carts/items';
beforeAll(function () use ($url) {
    printEndpoint('POST', $url);
});

Tests\helpers\setupAuthorization(fn($closure) => beforeEach($closure));

it('add an item to cart', function() use ($url) {
    $user = actAsUser();
    $cart = Cart::factory(['customer_id' => $user->id])->create();
    $product = Product::factory()->create();
    $data = [
        'product_id' => $product->id,
        'quantity' => random_int(1, 50),
    ];
    $response = post($url, $data);
    $response->assertOk();
    expect($cart->items->last()->pivot->product_id)->toEqual($product->id);
});

it('add items to cart', function() use ($url) {
    $user = actAsUser();
    $cart = Cart::factory(['customer_id' => $user->id])->create();
    $products = Product::factory()->count(10)->create();
    $data = $products->map(function($product) {
        return [
            'product_id' => $product->id,
            'quantity' => random_int(1, 10),
        ];
    })->toArray();
    $response = post($url, $data);
    $response->assertOk();
    expect(count($cart->items))->toEqual(10);
});

