<?php

use function Pest\Laravel\get;
use function Tests\helpers\actAsUser;
use function Tests\helpers\actAsUserWithPermission;
use function Tests\helpers\getResponseBodyAsArray;
use function Tests\helpers\printEndpoint;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
$url = '/api/orders';
beforeAll(function () use ($url) {
    printEndpoint('GET', $url);
});


Tests\helpers\setupAuthorization(fn ($closure) => beforeEach($closure));

it("returns all orders of the user with view-order-own or view-order-any", function ($role) use ($url) {
    $user = actAsUserWithPermission($role);
    $products[] = Product::factory()->count(5)->create();
    $orders[] = Order::factory(['customer_id' => $user->id])->create();
    $orders[0]->items()->attach(
        $products[0]->mapWithKeys(function ($product) {
            return [
                $product->id => [
                    'title' => $product->title,
                    'description' => $product->description,
                    'price' => $product->price,
                    'quantity' => random_int(1, 10),
                ]
            ];
        })
    );
    $products[] = Product::factory()->count(5)->create();
    $orders[] = Order::factory(['customer_id' => $user->id])->create();
    $orders[1]->items()->attach(
        $products[1]->mapWithKeys(function ($product) {
            return [
                $product->id => [
                    'title' => $product->title,
                    'description' => $product->description,
                    'price' => $product->price,
                    'quantity' => random_int(1, 10),
                ]
            ];
        })
    );

    $response = get($url);
    $response->assertOk();
    $body = getResponseBodyAsArray($response);
    expect(collect($body)->count())->toEqual(2);
    for ($i = 0; $i < count($orders); ++$i) {
        collect($orders[$i]->items)
            ->each(function ($item) use ($products, $i) {
                expect($item->pivot->product_id)
                    ->toBeIn($products[$i]->map(function ($pr) {
                        return $pr->id;
                    })->toArray());
            });
    }
})->with(['view-order-own', 'view-order-any']);

it("returns all orders if permission is view-order-any", function () use ($url) {
    $user = actAsUserWithPermission('view-order-any');
    $orders[] = Order::factory(['customer_id' => $user->id])->create();
    $orders[] = Order::factory()->create();//another user

    $response = get($url);
    $response->assertOk();
    $body = getResponseBodyAsArray($response);
    expect(collect($body)->count())->toEqual(2);
});
