<?php

use App\Models\Address;
use App\Models\User;

use function Pest\Laravel\get;
use function Tests\helpers\printEndpoint;
use function Tests\helpers\u;

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
$url = '/api/addresses/{id}';
beforeAll(function () use ($url) {
    printEndpoint('GET', $url);
});


it('gets an address by id', function () use ($url) {
    $address = Address::factory()->create();
    $response = get(u($url, 'id', $address->id));
    $response->assertOk();
    expect($response->json())->toMatchArray($address->toArray());
});

it('gets returns 404 if address not found', function () use ($url) {
    $response = get(u($url, 'id', 1));
    $response->assertStatus(404);
});
