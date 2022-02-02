<?php

use function Pest\Laravel\post;
use function Tests\helpers\actAsUser;
use function Tests\helpers\getResponseBody;
use function Tests\helpers\printEndpoint;

use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
$url = '/api/orders';
beforeAll(function () use ($url) {
    printEndpoint('POST', $url);
});

Tests\helpers\setupAuthorization(fn ($closure) => beforeEach($closure));

it('creats a new order', function () use ($url) {
    $user = actAsUser();
    $address = Address::factory(['customer_id' => $user->id])->create();
    $cart = Cart::factory(['customer_id' => $user->id])->create();
    $cartItemsCount = 5;
    $products = Product::factory()->count($cartItemsCount)->create();
    $dataToAttach = collect($products)->mapWithKeys(function ($product) {
        return [
            $product->id => [
                'quantity' => random_int(1, 50),
            ]
        ];
    });
    $cart->items()->attach($dataToAttach);
    $response = post($url, ['address_id' => $user->addresses->first()->id]);
    $response->assertOk();
    $order = Order::where('customer_id', $user->id)->first();
    expect($order)->not()->toBeNull();

    expect($order->items->count())->toEqual($cartItemsCount);

    expect(
        $cart->items->isEmpty()
    )->toBeTrue();
    expect($order->address->city)->toEqual($address->city);
    expect($order->address->province)->toEqual($address->province);
    expect($order->address->postal_code)->toEqual($address->postal_code);
    expect($order->address->rest_of_address)->toEqual($address->rest_of_address);
});
it('returns 400 if wrong address_id is provided', function () use ($url) {
    $user = actAsUser();
    $address = Address::factory()->create(); //another user's address
    $cart = Cart::factory(['customer_id' => $user->id])->create();
    $cartItemsCount = 5;
    $products = Product::factory()->count($cartItemsCount)->create();
    $dataToAttach = collect($products)->mapWithKeys(function ($product) {
        return [
            $product->id => [
                'quantity' => random_int(1, 50),
            ]
        ];
    });
    $cart->items()->attach($dataToAttach);
    $response = post($url, ['address_id' => $address->id]);
    $response->assertStatus(400);
});

it('returns 400 if cart is empty', function () use ($url) {
    $user = actAsUser();
    $cart = Cart::factory(['customer_id' => $user->id])->create();
    $response = post($url);
    $response->assertStatus(400);
});
