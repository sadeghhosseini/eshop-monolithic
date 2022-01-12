<?php

use function Pest\Laravel\get;
use function Tests\helpers\printEndpoint;
use function Tests\helpers\u;

use App\Models\Property;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
$url = '/api/properties/{id}';
beforeAll(function () use ($url) {
    printEndpoint('GET', $url);
});


it('gets property by id', function() use ($url) {
    $property = Property::factory()->create();
    $response = get(u($url, 'id', $property->id));
    $response->assertOk();
    expect($response->json())->title->toEqual($property->title);
});
it('returns 404 if property does not exist', function() use ($url) {
    $response = get(u($url, 'id', 1));
    $response->assertStatus(404);
});
