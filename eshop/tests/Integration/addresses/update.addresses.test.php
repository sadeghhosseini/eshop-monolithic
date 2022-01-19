<?php

use App\Models\Address;
use App\Models\User;

use function Pest\Laravel\patch;
use function Tests\helpers\actAsUser;
use function Tests\helpers\actAsUserWithPermission;
use function Tests\helpers\printEndpoint;
use function Tests\helpers\u;

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
$url = '/api/addresses/{id}';
beforeAll(function () use ($url) {
    printEndpoint('PATCH', $url);
});

Tests\helpers\setupAuthorization(fn($closure) => beforeEach($closure));

it('updates address', function ($key) use ($url) {
    $user = actAsUserWithPermission('edit-address-own');
    $address = Address::factory(['customer_id' => $user->id])->create();
    $response = patch(u($url, 'id', $address->id), [
        ...$address->makeHidden($key)->toArray(),
        $key => Address::factory()->make()->$key,
    ]);
    $response->assertOk();

    $newAddress = Address::find($address->id);
    expect(
        $address->makeHidden($key)->toArray()
    )->toMatchArray(
        $newAddress->makeHidden($key)->toArray()
    );
    expect($address->$key)->toEqual($newAddress->$key);
})->with([
    ['province'],
    ['city'],
    ['rest_of_address'],
    ['postal_code'],
]);

it('returns 400 if inputs are invalid', function ($key, $value) use ($url) {
    $user = actAsUserWithPermission('edit-address-own');
    $address = Address::factory(['customer_id' => $user->id])->create();
    $response = patch(u($url, 'id', $address->id), [
        ...$address->toArray(),
        $key => $value,
    ]);
    $response->assertStatus(400);
})->with([
    ['province', 'a'],//province => min:2
    ['province', 132423423424],//province => string
    ['city', 'a'],//city => min:2
    ['city', 1123234],//city => string
    ['rest_of_address', 234234],//rest_of_address => string
]);

it('returns 401 if user is not authenticated', function () use ($url) {
    $item = Address::factory()->create();
    $response = patch(
        u($url, 'id', $item->id),
        Address::factory()->make()->toArray()
    );
    $response->assertUnauthorized();
});
it('returns 403 if user is not permitted', function () use ($url) {
    actAsUser();
    $item = Address::factory()->create();
    $response = patch(
        u($url, 'id', $item->id),
        Address::factory()->make()->toArray()
    );
    $response->assertForbidden();
});