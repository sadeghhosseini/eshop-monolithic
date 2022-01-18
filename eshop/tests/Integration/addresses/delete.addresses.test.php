<?php

use App\Models\Address;
use App\Models\User;

use function Pest\Laravel\delete;
use function Tests\helpers\actAsUserWithPermission;
use function Tests\helpers\printEndpoint;
use function Tests\helpers\u;

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
$url = '/api/addresses/{id}';
beforeAll(function () use ($url) {
    printEndpoint('DELETE', $url);
});

Tests\helpers\setupAuthorization(fn($closure) => beforeEach($closure));

it('deletes an address', function () use ($url) {
    $user = actAsUserWithPermission('delete-address-own');
    $address = Address::factory([
        'customer_id' => $user->id,
    ])->create();
    $response = delete(u($url, 'id', $address->id));
    $response->assertOk();
    expect(Address::where('id', $address->id)->exists())
        ->toBeFalse();
});

it('returns 404 if address does not exist', function () use ($url) {
    $user = actAsUserWithPermission('delete-address-own');
    $response = delete(u($url, 'id', 1));
    $response->assertStatus(404);
});