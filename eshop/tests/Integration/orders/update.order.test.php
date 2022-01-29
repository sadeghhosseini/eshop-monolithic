<?php

use function Pest\Laravel\patch;
use function Tests\helpers\actAsUser;
use function Tests\helpers\actAsUserWithPermission;
use function Tests\helpers\getResponseBodyAsArray;
use function Tests\helpers\printEndpoint;
use function Tests\helpers\u;

use App\Models\Address;
use App\Models\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Models\OrderAddress;
use Illuminate\Foundation\Testing\RefreshDatabase;
uses(RefreshDatabase::class);
$url = '/api/orders/{id}';
beforeAll(function () use ($url) {
    printEndpoint('PATCH', $url);
});

Tests\helpers\setupAuthorization(fn($closure) => beforeEach($closure));

it('returns 401 if not logged in', function() use ($url) {
   
});

it('returns 403 if has no permission', function() use ($url) {
    $user = actAsUser();
    $order = Order::factory()->create();
    $response = patch(u($url, 'id', $order->id), [
        'status' => 'shipped',
    ]);
    $response->assertForbidden();

    $order = Order::factory(['customer_id' => $user->id])->create();
    $orderAddress = OrderAddress::factory(['order_id' => $order->id])->create();
    $newAddress = Address::factory(['customer_id' => $user->id])
    ->create();
    $response = patch(u($url, 'id', $order->id), [
        'address_id' => $newAddress->id,
    ]);
    $response->assertForbidden();
});

it('returns 403 if has edit-order__address__-own permission and is not the owner', function() use ($url) {
    $user = actAsUserWithPermission('edit-order(address)-own');
    $order = Order::factory()->create();//for another user
    $address = Address::factory(['customer_id' => $user->id])->create();
    $response = patch(u($url, 'id', $order->id), ['address_id' => $address->id]);
    $response->assertForbidden();
});

test("user cannot change his/her own order's address when order.status is shipped", function() use ($url) {
    $user = actAsUserWithPermission('edit-order(address)-own');
    $order = Order::factory(['customer_id' => $user->id])->create();
    $order->status = OrderStatusEnum::Shipped;
    $order->save();
    $address = Address::factory(['customer_id' => $user->id])->create();
    $response = patch(u($url, 'id', $address->id), ['address_id' => $address->id]);
    $response->assertForbidden();
});

test("user with permission: edit-order__status__-any can change any order's status", function() use ($url) {
    $user = actAsUserWithPermission('edit-order(status)-any');
    $order = Order::factory()->create();
    $response = patch(u($url, 'id', $order->id), [
        'status' => 'shipped',
    ]);
    $response->assertOk();
});

it("returns 400 if user has edit-order__status__-any permission but does not provide status in request", function() use ($url) {
    $user = actAsUserWithPermission('edit-order(status)-any');
    $order = Order::factory(['customer_id' => $user->id])->create();
    $response = patch(u($url, 'id', $order->id));
    $response->assertStatus(400);
});

it("returns 400 if user has edit-order__address__-own permission but does not provide address_id or address fields__province - city - rest_of_address - postal_code__ in request", function() use ($url) {
    $user = actAsUserWithPermission('edit-order(address)-own');
    $order = Order::factory(['customer_id' => $user->id])->create();
    $response = patch(u($url, 'id', $order->id));
    $response->assertStatus(400);
});

it("updates order's address using address_id", function() use ($url) {
    $user = actAsUserWithPermission('edit-order(address)-own');
    $order = Order::factory(['customer_id' => $user->id])->create();
    $orderAddress = OrderAddress::factory(['order_id' => $order->id])->create();
    $newAddress = Address::factory(['customer_id' => $user->id])
        ->create();
    $response = patch(u($url, 'id', $order->id), [
        'address_id' => $newAddress->id,
    ]);
    $response->assertOk();
    $body = getResponseBodyAsArray($response);
    expect(
        collect($body->address)->only(
            'province',
            'city',
            'rest_of_address',
            'postal_code',
        )->toArray()
    )->toMatchArray(
        $newAddress->only(
            'province',
            'city',
            'rest_of_address',
            'postal_code',
        )
    );
});


it("updates order's address using address fields", function() use ($url) {
    $user = actAsUserWithPermission('edit-order(address)-own');
    $order = Order::factory(['customer_id' => $user->id])->create();
    OrderAddress::factory(['order_id' => $order->id])->create();
    $newAddress = Address::factory(['customer_id' => $user->id])
        ->make();
    
    $response = patch(u($url, 'id', $order->id), $newAddress->makeHidden('customer_id')->toArray());
    $response->assertOk();
    $body = getResponseBodyAsArray($response);

    expect(
        collect($body->address)->only(
            'province',
            'city',
            'rest_of_address',
            'postal_code',
        )->toArray()
    )->toMatchArray(
        $newAddress->only(
            'province',
            'city',
            'rest_of_address',
            'postal_code',
        )
    );
});