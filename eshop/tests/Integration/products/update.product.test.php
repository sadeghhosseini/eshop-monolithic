<?php

use App\Models\Category;
use App\Models\Image;
use App\Models\Product;
use App\Models\Property;
use function Pest\Laravel\patch;
use function Tests\helpers\buildUrl;
use function Tests\helpers\printEndpoint;
use function Tests\helpers\u;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;

uses(RefreshDatabase::class);

$url = '/api/products/{id}';
beforeAll(function () use ($url) {
    printEndpoint('PATCH', $url);
});

function decode(TestResponse $response)
{
    return json_decode($response->baseResponse->content());
}

function updateIdsByAdding($url, $ModelClass, $columnName, $relationField)
{
    $newIds = $ModelClass::factory()->count(3)->create()->map(fn ($item) => $item->id);
    $product = Product::factory()
        ->has($ModelClass::factory()->count(5))->create();
    $ids = $product->$relationField->map(fn ($item) => $item->id);
    $response = patch(u($url, 'id', $product->id), [
        $columnName => [...$ids, ...$newIds],
    ]);
    $body = decode($response);
    $responseItemIds = collect($body->$relationField)->map(fn ($item) => $item->id)->toArray();
    $expecteItemIds = [...$ids, ...$newIds];
    expect($responseItemIds)->toMatchArray($expecteItemIds);
    $productImageIds = Product::find($product->id)->$relationField->map(fn ($item) => $item->id)->toArray();
    expect($productImageIds)->toMatchArray([...$ids, ...$newIds]);
}

function updateIdsByAddingAndRemoving($url, $ModelClass, $columnName, $relationField)
{
    $newIds = $ModelClass::factory()->count(3)->create()->map(fn ($item) => $item->id);
    $product = Product::factory()
        ->has($ModelClass::factory()->count(5))->create();
    $itemIds = $product->$relationField->map(fn ($item) => $item->id)->toArray();
    $itemIds = array_slice($itemIds, 0, 3);
    $response = patch(u($url, 'id', $product->id), [
        $columnName => [...$itemIds, ...$newIds],
    ]);
    $body = decode($response);
    $responseItemIds = collect($body->$relationField)->map(fn ($item) => $item->id)->toArray();
    $expectedItemIds = [...$itemIds, ...$newIds];
    expect($responseItemIds)->toMatchArray($expectedItemIds);
    $productItemIds = Product::find($product->id)->$relationField->map(fn ($item) => $item->id)->toArray();
    expect($productItemIds)->toMatchArray([...$itemIds, ...$newIds]);
}

function updateIdsByAddingNonValidForeignKey($url, $ModelClass, $columnName, $relationField)
{
    $product = Product::factory()
        ->has($ModelClass::factory()->count(5))->create();
    $itemIds = $product->$relationField->map(fn ($item) => $item->id);
    $response = patch(u($url, 'id', $product->id), [
        $columnName => [...$itemIds, 55],
    ]);
    $response->assertStatus(400);
    expect(decode($response)->$columnName)->toBeArray();
    expect(decode($response)->$columnName[0])->toContain('55');
}

function updateWithNewImages($url, $existingImageIds = [])
{
    $category = Category::factory()->create();
    $productToBeUpdated = Product::factory()->create();
    Storage::fake('images');
    $images = [
        UploadedFile::fake()->image('shite.png'),
        UploadedFile::fake()->image('might.png'),
    ];

    $data = [
        'category_id' => $category->id,
        'new_images' => $images,
    ];
    if (!empty($existingImageIds)) {
        $data['image_ids'] = $existingImageIds;
    }
    $product = Product::factory($data)->make();
    $response = patch(u($url, 'id', $productToBeUpdated->id), $product->toArray());
    $response->assertOk();

    $productId = json_decode($response->baseResponse->content())->id;
    $newProduct = Product::find($productId);
    expect($newProduct->images->toArray())->toBeArray();
    expect($newProduct->images->toArray())->toHaveCount(count($images) + count($existingImageIds));
    $newProductImages = $newProduct->images->filter(fn ($image) => !in_array($image->id, $existingImageIds))->toArray();
    foreach ($newProductImages as $image) {
        #@php-ignore //for vs-code linter
        Storage::disk('local')->assertExists($image['path']);
    }
}

function updateWithNewProperties($url, $existingPropertyIds = [])
{
    $category = Category::factory()->create();
    $product = Product::factory()->create();
    $properties = Property::factory([
        'category_id' => $category->id,
    ])->count(3)
        ->make();
    $response = patch(u($url, 'id', $product->id), Product::factory([
        'category_id' => $category->id,
        'new_properties' => $properties
            ->map(fn ($item) => collect($item)->only('title')['title'])
            ->toArray(),
        ...(empty($existingPropertyIds) ? [] : ['property_ids' => $existingPropertyIds]),
    ])->make()->toArray());

    $response->assertOk();
    $product = Product::find($response->json()['id']);

    expect(
        $product->properties->toArray()
    )->toHaveCount(count($properties->toArray()) + count($existingPropertyIds));
    
    expect(
        $product->properties
            ->map(fn ($item) => collect($item)->only('category_id', 'title'))
            ->toArray()
    )->toEqualArray([
        ...(collect($existingPropertyIds ?? [])->map(fn ($id) => Property::select('title', 'category_id')->find($id))->toArray()),
        ...$properties
            ->map(fn ($item) => collect($item)->only('category_id', 'title'))
            ->toArray(),
    ], 'title', function($a, $b) {
        expect($a['title'])->toEqual($b['title']);
    });
}

it('updates simple product fields', function ($key) use($url){
    $product = Product::factory()->create();
    $value = Product::factory()->make()->$key;
    $response = patch(u($url, 'id', $product->id), [$key => $value]);
    $body = json_decode($response->baseResponse->content());
    expect($body->$key)->toEqual($value);
})->with([
    ['title'],
    ['description'],
    ['quantity'],
    ['price'],
]);

it('updates category_id', function () use ($url){
    $category = Category::factory()->create();
    $product = Product::factory()->create();
    $response = patch(u($url, 'id', $product->id), [
        'category_id' => $category->id,
    ]);
    $body = json_decode($response->baseResponse->content());
    expect($body->category_id)->toEqual($category->id);
});

it('updates image_ids by adding new image_ids', function () use ($url){
    updateIdsByAdding($url, Image::class, 'image_ids', 'images');
});

it('updates image_ids by adding new image_ids and removing some old image_ids', function () use ($url) {
    updateIdsByAddingAndRemoving($url, Image::class, 'image_ids', 'images');
});

it('updates property_ids by adding new propertyIds', function () use ($url) {
    updateIdsByAdding($url, Property::class, 'property_ids', 'properties');
});

it('updates property_ids by adding new property_ids and removing some old property_ids', function () use ($url) {
    updateIdsByAddingAndRemoving($url, Property::class, 'property_ids', 'properties');
});

it('updates a product with new_images', function ()  use ($url) {
    updateWithNewImages($url);
});

it('updates a product with new_images and existing image_ids', function ()  use ($url) {
    updateWithNewImages($url, Image::factory()->count(10)->create()->map(fn ($image) => $image->id)->toArray());
});

it('updates a product with new properties', function ()  use ($url) {
    updateWithNewProperties($url);
});

it('updates a product with new properties and existing property_ids', function ()  use ($url) {
    updateWithNewProperties($url, Property::factory()->count(5)->create()->map(fn ($property) => $property->id)->toArray());
});

it('returns 400 if new image_ids are not valid foreign keys', function () use ($url) {
    updateIdsByAddingNonValidForeignKey($url, Image::class, 'image_ids', 'images');
});

it('returns 400 if new property_ids are not valid foreign keys', function () use ($url) {
    updateIdsByAddingNonValidForeignKey($url, Image::class, 'property_ids', 'properties');
});
