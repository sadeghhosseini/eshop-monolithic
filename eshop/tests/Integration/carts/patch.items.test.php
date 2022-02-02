<?php

use function Pest\Laravel\get;
use function Pest\Laravel\patch;
use function Pest\Laravel\post;
use function Tests\helpers\actAsUser;
use function Tests\helpers\getResponseBody;
use function Tests\helpers\printEndpoint;
use function Tests\helpers\u;

use App\Helpers;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
$url = '/api/carts/items/{id}';
beforeAll(function () use ($url) {
    printEndpoint('PATCH', $url);
});

Tests\helpers\setupAuthorization(fn ($closure) => beforeEach($closure));

it("update an item's quantity to cart", function () use ($url) {
    $user = actAsUser();
    $cart = Cart::factory(['customer_id' => $user->id])->create();
    $product = Product::factory()->create();
    $data = [
        'product_id' => $product->id,
        'quantity' => 5,
    ];
    $cart->items()->attach($product->id, ['quantity' => 5]);
    $newData = [
        'product_id' => $product->id,
        'quantity' => 13,
    ];
    $response = patch(u($url, 'id', $product->id), $newData);
    $response->assertOk();
    expect($cart->items->last()->pivot->quantity)->toEqual($newData['quantity']);
});

it('add item to cart - mass update', function () use ($url) {
    $user = actAsUser();
    $cart = Cart::factory(['customer_id' => $user->id])->create();
    $products = Product::factory()->count(10)->create();
    $data = $products->mapWithKeys(function ($product) {
        return [$product->id => [
            'product_id' => $product->id,
            'quantity' => random_int(1, 10),
        ]];
    })->toArray();
    $cart->items()->attach($data);
    $newData = $products->map(function ($product) {
        return [
            'product_id' => $product->id,
            'quantity' => random_int(11, 20),
        ];
    })->toArray();
    $response = patch(u($url, 'id', ''), $newData);
    $response->assertOk();
    foreach ($newData as $item) {
        expect(
            $cart->items()->where('product_id', $item['product_id'])->first()->pivot->quantity
        )->toEqual(
            $item['quantity']
        );
    }
});


it('returns 400 if product is not already in the cart', function () use ($url) {
    $user = actAsUser();
    $cart = Cart::factory(['customer_id' => $user->id])->create();
    $product = Product::factory()->create();
    $cart->items()->attach($product->id, ['quantity' => 3]);
    $productNotInCart = Product::factory()->create();
    $data = [
        'product_id' => $productNotInCart->id,
        'quantity' => 5,
    ];
    $response = patch(u($url, 'id', $data['product_id']), $data);
    $response->assertStatus(400);
});

it('returns 400 if product is not already in the cart - mass update', function () use ($url) {
    $user = actAsUser();
    $cart = Cart::factory(['customer_id' => $user->id])->create();
    $product = Product::factory()->create();
    $cart->items()->attach($product->id, ['quantity' => 3]);
    $productNotInCart = Product::factory()->create();
    $data = [
        [
            'product_id' => $productNotInCart->id,
            'quantity' => 5,
        ],
        [
            'product_id' => $product->id,
            'quantity' => 9,
        ]
    ];
    $response = patch(u($url, 'id', ''), $data);
    $response->assertStatus(400);
});
