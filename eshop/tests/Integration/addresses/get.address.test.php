<?php

use App\Helpers;
use App\Models\Address;
use App\Models\User;

use function Pest\Laravel\get;
use function Tests\helpers\actAsUser;
use function Tests\helpers\actAsUserWithPermission;
use function Tests\helpers\printEndpoint;
use function Tests\helpers\u;

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
$url = '/api/addresses/{id}';
beforeAll(function () use ($url) {
    printEndpoint('GET', $url);
});

Tests\helpers\setupAuthorization(fn($closure) => beforeEach($closure));
it('gets an address by id', function () use ($url) {
    $user = actAsUserWithPermission('view-address-own');
    $address = Address::factory(['customer_id' => $user->id])->create();
    $response = get(u($url, 'id', $address->id));
    $response->assertOk();
    expect($response->json()['data'])->toMatchArray($address->toArray());
});

it('gets returns 404 if address not found', function () use ($url) {
    $user = actAsUserWithPermission('view-address-own');
    $response = get(u($url, 'id', 1));
    $response->assertStatus(404);
});

it('returns 401 if user is not authenticated', function () use ($url) {
    $item = Address::factory()->create();
    $response = get(u($url, 'id', $item->id));
    $response->assertUnauthorized();
});

it('returns 403 if user is not permitted', function () use ($url) {
    actAsUser();
    $item = Address::factory()->create();
    $response = get(u($url, 'id', $item->id));
    $response->assertForbidden();
});

it('returns 403 if user has view-address-own permission but is not the owner', function () use ($url) {
    actAsUserWithPermission('view-address-own');
    $item = Address::factory()->create();
    $response = get(u($url, 'id', $item->id));
    $response->assertForbidden();
});
it('returns 200 if user has view-address-any permission and is not the owner', function () use ($url) {
    actAsUserWithPermission('view-address-any');
    $item = Address::factory()->create();
    $response = get(u($url, 'id', $item->id));
    $response->assertOk();
});
it('returns 200 if user has view-address-any permission and is the owner', function () use ($url) {
    $user = actAsUserWithPermission('view-address-any');
    $item = Address::factory(['customer_id' => $user->id])->create();
    $response = get(u($url, 'id', $item->id));
    $response->assertOk();
});
it('returns 200 if user has both view-address-any and view-address-own permission and is not the owner', function () use ($url) {
    $user = actAsUserWithPermission('view-address-any');
    $item = Address::factory()->create();
    $response = get(u($url, 'id', $item->id));
    $response->assertOk();
});
it('returns 200 if user has both view-address-any and view-address-own permission and is the owner', function () use ($url) {
    $user = actAsUserWithPermission('view-address-any');
    $item = Address::factory(['customer_id' => $user->id])->create();
    $response = get(u($url, 'id', $item->id));
    $response->assertOk();
});