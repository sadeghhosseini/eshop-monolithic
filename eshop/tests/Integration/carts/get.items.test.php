<?php

use function Pest\Laravel\get;
use function Tests\helpers\actAsUser;
use function Tests\helpers\getResponseBodyAsArray;
use function Tests\helpers\printEndpoint;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
uses(RefreshDatabase::class);
$url = '/api/carts/items';
beforeAll(function () use ($url) {
    printEndpoint('GET', $url);
});

Tests\helpers\setupAuthorization(fn($closure) => beforeEach($closure));


it('returns all the cart items for the authenticated user', function() use ($url) {
    $user = actAsUser();
    $cart = Cart::factory(['customer_id' => $user->id])->create();
    $products = Product::factory()->count(10)->create();
    $attachInput = collect($products)->mapWithKeys(function($item) {
        return [$item->id => ['quantity' => random_int(5, 30)]];
    })->toArray();
    $cart->items()->attach($attachInput);
    $response = get($url);
    $response->assertOk();

    $body = getResponseBodyAsArray($response);
    expect(count($body))->toEqual(count($cart->items));
});