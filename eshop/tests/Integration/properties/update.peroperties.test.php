<?php

use function Pest\Laravel\patch;
use function Tests\helpers\actAsUser;
use function Tests\helpers\actAsUserWithPermission;
use function Tests\helpers\printEndpoint;
use function Tests\helpers\setupAuthorization;
use function Tests\helpers\u;

use App\Models\Product;
use App\Models\Property;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
$url = '/api/properties/{id}';
beforeAll(function () use ($url) {
    printEndpoint('PATCH', $url);
});

setupAuthorization(fn ($closure) => beforeEach($closure));

it('updates a property', function ($key, $value) use ($url) {
    actAsUserWithPermission('edit-property-any');
    $property = Property::factory()->create();
    $response = patch(u($url, 'id', $property->id), [
        $key => $value,
    ]);
    $response->assertOk();
    expect(Property::find($property->id)->$key)->toEqual($value);
})->with([
    ['title', 'new-title'],
    ['is_visible', false],
    ['is_visible', true],
]);

it('checks validation rules', function ($key, $value) use ($url) {
    actAsUserWithPermission('edit-property-any');
    $property = Property::factory()->create();
    $response = patch(u($url, 'id', $property->id), [
        $key => $value,
    ]);
    $response->assertStatus(400);
})->with([
    ['is_visible', 3], //is_visible => boolean(true|false|1|0)
    ['title', 1], //title => string
    ['title', 'ai'], //title => min:3
    ['category_id', 123], //category_id => ForeinKeyExists
]);

it('returns 401 if user is not authenticated', function () use ($url) {
    $item = Property::factory()->create();
    $response = patch(
        u($url, 'id', $item->id),
        Property::factory()->make()->toArray()
    );
    $response->assertUnauthorized();
});
it('returns 403 if user is not permitted', function () use ($url) {
    actAsUser();
    $item = Property::factory()->create();
    $response = patch(
        u($url, 'id', $item->id),
        Property::factory()->make()->toArray()
    );
    $response->assertForbidden();
});
