<?php

use function Pest\Laravel\post;
use function Tests\helpers\actAsUserWithPermission;
use function Tests\helpers\printEndpoint;
use function Tests\helpers\setupAuthorization;

use App\Models\Property;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
$url = '/api/properties';
beforeAll(function () use ($url) {
    printEndpoint('POST', $url);
});

setupAuthorization(fn($closure) => beforeEach($closure));

it('creates a property', function () use ($url) {
    actAsUserWithPermission('add-properties');
    $property = Property::factory(['is_visible' => true])->make();
    $response = post($url, $property->toArray());
    $response->assertOk();
    expect(
        collect($response->json())->except(
            'id',
            'created_at',
            'updated_at'
        )->toArray()
    )->toMatchArray($property->toArray());
});

it('checks validation rules', function ($key, $value) use ($url) {
    actAsUserWithPermission('add-properties');
    $property = Property::factory([
        $key => $value,
    ])->make();
    $response = post($url, $property->toArray());
    $response->assertStatus(400);
})->with([
    ['is_visible', 3],//is_visible => boolean(true|false|1|0)
    ['title', 1],//title => string
    ['title', ''],//title => required
    ['title', 'ai'],//title => min:3
    ['category_id', 123],//category_id => ForeinKeyExists
    ['category_id', ''],//category_id => required
]);
