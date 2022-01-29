<?php

use function Pest\Laravel\delete;
use function Tests\helpers\actAsUser;
use function Tests\helpers\printEndpoint;
use function Tests\helpers\u;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
uses(RefreshDatabase::class);
$url = '/api/carts/items/{id}';
beforeAll(function () use ($url) {
    printEndpoint('DELETE', $url);
});


Tests\helpers\setupAuthorization(fn($closure) => beforeEach($closure));


it("it deletes an item from user's cart", function() use ($url) {
    $user = actAsUser();
    $cart = Cart::factory(['customer_id' => $user->id])->create();
    $product = Product::factory([])->create();
    $cart->items()->attach($product->id, ['quantity' => 3, 'cart_id' => $cart->id]);
    $response = delete(u($url, 'id', $product->id));
    $response->assertOk();
    expect($cart->items)->toBeEmpty();
});