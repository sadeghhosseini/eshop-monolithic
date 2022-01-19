<?php

use function Pest\Laravel\delete;
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
    printEndpoint('DELETE', $url);
});

setupAuthorization(fn($closure) => beforeEach($closure));

it('deletes a property', function () use ($url) {
    actAsUserWithPermission('delete-property-any');
    $property = Property::factory()->create();
    $response = delete(u($url, 'id', $property->id));
    $response->assertOk();
    expect(Property::where('id', $property->id)->exists())->toBeFalse();
});
it('deletes related product_properties records', function () use ($url) {
    actAsUserWithPermission('delete-property-any');
    $property = Property::factory()->create();
    $products = Product::factory()->count(3)
        ->has(Property::factory()->count(3))
        ->create();
    $products->each(function (Product $product) use ($property) {
        $product->properties()->attach($property->id);
    });

    $response = delete(u($url, 'id', $property->id));
    $response->assertOk();

    $products->each(function (Product $product) use ($property) {
        expect(
            $product->properties()
                ->wherePivot('property_id', $property->id)
                ->exists()
        )->toBeFalse();
    });
});


#delete
it('returns 401 if user is not authenticated', function () use ($url) {
    $item = Property::factory()->create();
    $response = delete(
        u($url, 'id', $item->id),
    );
    $response->assertUnauthorized();
});
it('returns 403 if user is not permitted', function () use ($url) {
    actAsUser();
    $item = Property::factory()->create();
    $response = delete(
        u($url, 'id', $item->id),
    );
    $response->assertForbidden();
});
