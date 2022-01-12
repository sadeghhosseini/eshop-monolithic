<?php

use function Pest\Laravel\get;
use function Tests\helpers\printEndpoint;

use App\Models\Property;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
$url = '/api/properties';
beforeAll(function () use ($url) {
    printEndpoint('GET', $url);
});


it('gets all properties', function() use ($url) {
    $properties = Property::factory()->count(20)->create();
    $response = get($url);
    $response->assertOk();
    expect($response->json())->toMatchArray($properties->toArray());
});