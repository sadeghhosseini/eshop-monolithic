<?php

use App\Models\Address;
use App\Models\User;

use function Pest\Laravel\post;
use function Tests\helpers\printEndpoint;

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
$url = '/api/addresses';
beforeAll(function () use ($url) {
    printEndpoint('POST', $url);
});

it('creates address', function () use ($url) {
    $address = Address::factory()->make()->toArray();
    $response = post($url, $address);
    $response->assertOk();

    expect(
        Address::find($response->json()['id'])->toArray()
    )->toMatchArray(
        $address
    );
});

it('returns 400 if inputs are invalid', function ($key, $value) use ($url) {
    $address = Address::factory([
        $key => $value,
    ])->make()->toArray();
    $response = post($url, $address);
    $response->assertStatus(400);
})->with([
    ['province', ''],//province => required
    ['city', ''],//city => required
    ['rest_of_address', ''],//rest_of_address => required
    ['postal_code', ''],//postal_code => required
    ['customer_id', 22],//customer_id => ForeignKeyExists
]);
