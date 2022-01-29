<?php

use function Pest\Laravel\get;
use function Tests\helpers\actAsUser;
use function Tests\helpers\actAsUserWithPermission;
use function Tests\helpers\getResponseBodyAsArray;
use function Tests\helpers\printEndpoint;
use function Tests\helpers\u;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
$url = '/api/orders/{id}';
beforeAll(function () use ($url) {
    printEndpoint('GET', $url);
});



Tests\helpers\setupAuthorization(fn ($closure) => beforeEach($closure));

it("returns user's own orders if user has view-order-own", function () use ($url) {
    $user = actAsUserWithPermission('view-order-own');
    $products = Product::factory()->count(10)->create();
    $order = Order::factory(['customer_id' => $user->id])->create();
    $order->items()->attach(
        $products->mapWithKeys(function ($product) {
            return [
                $product->id => [
                    'quantity' => random_int(1, 10),
                    'title' => $product->title,
                    'description' => $product->description,
                    'price' => $product->price,
                ]
            ];
        })
    );

    $response = get(u($url, 'id', $order->id));
    $response->assertOk();
    $body = getResponseBodyAsArray($response);
    $products->each(function ($product) use ($body) {
        $order = $body;
        $items = $body->items;
        $foundProduct = collect($items)->filter(function ($item) use ($product) {
            return $item->id == $product->id;
        })->first();
        expect($product->only(
            'title',
            'description',
            'price',
        ))->toMatchArray(collect($foundProduct)->only(
            'title',
            'description',
            'price',
        ));
    });
});

it("returns 403 if user is trying to get others orders", function () use ($url) {
    $user = actAsUserWithPermission('view-order-any');
    $products = Product::factory()->count(10)->create();
    $order = Order::factory()->create(); //another user's order
    $order->items()->attach(
        $products->mapWithKeys(function ($product) {
            return [
                $product->id => [
                    'quantity' => random_int(1, 10),
                    'title' => $product->title,
                    'description' => $product->description,
                    'price' => $product->price,
                ]
            ];
        })
    );

    $response = get(u($url, 'id', $order->id));
    $response->assertOk();
    $body = getResponseBodyAsArray($response);
    $products->each(function ($product) use ($body) {
        $order = $body;
        $items = $body->items;
        $foundProduct = collect($items)->filter(function ($item) use ($product) {
            return $item->id == $product->id;
        })->first();
        expect($product->only(
            'title',
            'description',
            'price',
        ))->toMatchArray(collect($foundProduct)->only(
            'title',
            'description',
            'price',
        ));
    });
});

it("returns own order if user has view-order-any", function () use ($url) {
    $user = actAsUserWithPermission('view-order-any');
    $orders = Order::factory(['customer_id' => $user->id])->create(); //own order

    $response = get(u($url, 'id', $orders->id));
    $response->assertOk();
    $body = getResponseBodyAsArray($response);
    expect($body->id)->toEqual($orders->id);
});

it("returns another user's order if user has view-order-any", function () use ($url) {
    $user = actAsUserWithPermission('view-order-any');
    $orders = Order::factory()->create(); //another user's order

    $response = get(u($url, 'id', $orders->id));
    $response->assertOk();
    $body = getResponseBodyAsArray($response);
    expect($body->id)->toEqual($orders->id);
});


it('returns 403 if user is not owner and has view-order-own permission', function () use ($url) {
    $user = actAsUserWithPermission('view-order-own');
    $orders = Order::factory()->create(); //another user's order
    $response = get(u($url, 'id', $orders->id));
    $response->assertForbidden(); 
});

it('returns 403 if user does not have any permission', function () use ($url) {
    $user = actAsUser();
    $orders = Order::factory()->create(); //another user's order
    $response = get(u($url, 'id', $orders->id));
    $response->assertForbidden(); 
});

it('returns 401 if user not authenticated', function () use ($url) {
    $orders = Order::factory()->create(); //another user's order
    $response = get(u($url, 'id', $orders->id));
    $response->assertUnauthorized(); 
});

