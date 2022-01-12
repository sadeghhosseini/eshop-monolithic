<?php

use App\Models\Address;
use App\Models\User;

use function Pest\Laravel\delete;
use function Tests\helpers\printEndpoint;
use function Tests\helpers\u;

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
$url = '/api/addresses/{id}';
beforeAll(function () use ($url) {
    printEndpoint('DELETE', $url);
});


it('deletes an address', function () use ($url) {
    $address = Address::factory()->create();
    $response = delete(u($url, 'id', $address->id));
    $response->assertOk();
    expect(Address::where('id', $address->id)->exists())
        ->toBeFalse();
});

it('returns 404 if address does not exist', function () use ($url) {
    $response = delete(u($url, 'id', 1));
    $response->assertStatus(404);
});