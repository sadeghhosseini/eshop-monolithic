<?php

use App\Helpers;
use App\Models\Address;
use App\Models\User;

use function Pest\Laravel\post;
use function Tests\helpers\actAsUser;
use function Tests\helpers\actAsUserWithPermission;
use function Tests\helpers\printEndpoint;

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
$url = '/api/addresses';
beforeAll(function () use ($url) {
    printEndpoint('POST', $url);
});

Tests\helpers\setupAuthorization(fn($closure) => beforeEach($closure));

it('creates address', function () use ($url) {
    actAsUserWithPermission('add-address-own');
    $address = Address::factory()->make()->makeHidden('customer_id')->toArray();
    $response = post($url, $address);
    $response->assertCreated();
    expect(
        Address::find($response->json()['data']['id'])->toArray()
    )->toMatchArray(
        $address
    );
});

it('returns 400 if inputs are invalid', function ($key, $value) use ($url) {
    actAsUserWithPermission('add-address-own');
    $address = Address::factory([
        $key => $value,
    ])->make()->makeHidden('customer_id')->toArray();
    $response = post($url, $address);
    $response->assertStatus(400);
})->with([
    ['province', ''],//province => required
    ['city', ''],//city => required
    ['rest_of_address', ''],//rest_of_address => required
    ['postal_code', ''],//postal_code => required
]);

it('returns 401 if not logged in', function() use ($url) {
    $address = Address::factory()->make()->makeHidden('customer_id');
    $response = post($url, $address->toArray());
    $response->assertStatus(401);
});

it('returns 401 if user is not authenticated', function () use ($url) {
    $item = Address::factory()->make();
    $response = post(
        $url,
        $item->toArray()
    );
    $response->assertUnauthorized();
});
it('returns 403 if user is not permitted', function () use ($url) {
    actAsUser();
    $item = Address::factory()->make();
    $response = post(
        $url,
        $item->toArray()
    );
    $response->assertForbidden();
});