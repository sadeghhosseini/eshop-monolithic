<?php

use App\Models\Category;
use App\Models\Image;
use App\Models\Product;
use App\Models\Property;
use function Pest\Laravel\post;
use function Tests\helpers\actAsUser;
use function Tests\helpers\actAsUserWithPermission;
use function Tests\helpers\printEndpoint;
use function Tests\helpers\setupAuth;
use function Tests\helpers\setupAuthorization;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);


$url = '/api/products';
beforeAll(function () use ($url) {
    printEndpoint('POST', $url);
});

setupAuthorization(fn($closure) => beforeEach($closure));

it('creates a product without properties and images', function () use ($url) {
    // Laravel\Sanctum\Sanctum::actingAs(App\Models\User::factory()->create()->givePermissionTo('add-product'));
    actAsUserWithPermission('add-product');
    $category = Category::factory()->create();
    $product = Product::factory([
        'category_id' => $category->id,
    ])->make();
    $response = post($url, $product->toArray());
    expect($response->json())
        ->toMatchArray($product->toArray());
    $response->assertOk();
});

it('creates a product with properties', function ()  use ($url) {
    actAsUserWithPermission('add-product');
    $properties = Property::factory()
        ->count(3)
        ->for(Category::factory())
        ->create();
    $categoryId = $properties->last()->category->id;
    $response = post($url, Product::factory([
        'category_id' => $categoryId,
        'property_ids' => $properties->map(fn ($category) => $category->id)->toArray(),
    ])->make()->toArray());
    $response->assertOk();
    expect(
        $properties
            ->map(fn ($p) => $p->id)
            ->toArray()
    )->toMatchArray(
        Product::all()
            ->last()
            ->properties
            ->map(fn ($p) => $p->id)
            ->toArray()
    );
});

it('creates a product with new properties', function ()  use ($url) {
    actAsUserWithPermission('add-product');
    $category = Category::factory()->create();
    $properties = Property::factory([
        'category_id' => $category->id,
    ])->count(3)
        ->make();
    $response = post($url, Product::factory([
        'category_id' => $category->id,
        'new_properties' => $properties
            ->map(fn ($item) => collect($item)->only('title')['title'])
            ->toArray(),
    ])->make()->toArray());

    $response->assertOk();
    $product = Product::find($response->json()['id']);
    expect(
        $product->properties
            ->map(fn ($item) => collect($item)->only('category_id', 'title'))
            ->toArray()
    )->toMatchArray(
        $properties
            ->map(fn ($item) => collect($item)->only('category_id', 'title'))
            ->toArray()
    );
});

/**
 * asserts image file getting uploaded and saved on disk
 * asserts records are added in products_images table
 */
it('creates a product with new_images', function ()  use ($url) {
    actAsUserWithPermission('add-product');
    $category = Category::factory()->create();
    Storage::fake('local');
    $images = [
        UploadedFile::fake()->image('shite.png'),
        UploadedFile::fake()->image('might.png'),
    ];
    $product = Product::factory([
        'category_id' => $category->id,
        'new_images' => $images,
    ])->make();
    $response = post($url, $product->toArray());
    $response->assertOk();

    $productId = json_decode($response->baseResponse->content())->id;
    $newProduct = Product::find($productId);
    expect($newProduct->images->toArray())->toBeArray();
    expect($newProduct->images->toArray())->toHaveCount(count($images));
    $newProductImages = $newProduct->images->toArray();
    foreach ($newProductImages as $image) {
        #@php-ignore //for vs-code linter
        Storage::disk('local')->assertExists($image['path']);
    }
});

it('creates a product with image_ids', function () use ($url) {
    actAsUserWithPermission('add-product');
    $images = Image::factory()->count(3)->create();
    $imageIds = $images->map(fn ($image) => $image->id);

    $response = post($url, Product::factory([
        'image_ids' => $imageIds,
    ])->make()->toArray());
    $response->assertOk();

    $productId = $response->json()['id'];
    $postImages = collect($response->json()['images'])
        ->map(fn ($i) => $i['id'])->toArray();
    $newProductImages = Product::find($productId)->images
        ->map(fn ($i) => $i['id'])->toArray();
    expect($postImages)->toBeArray();
    expect($postImages)->toMatchArray($imageIds);
    expect($newProductImages)->toMatchArray($imageIds);
});

it('returns 400 if image_ids is not an array of forein keys', function () use ($url) {
    actAsUserWithPermission('add-product');
    $image = Image::factory()->create();
    $response = post($url, Product::factory([
        'image_ids' => [$image->id, 2, 3],
    ])->make()->toArray());
    expect($response->baseResponse->content())->json()
        ->image_ids->each(function ($m) use ($image) {
            $m->not->toContain("$image->id");
            $m->toContain("2");
            $m->toContain("3");
        });
    $response->assertStatus(400);
});

it('returns 400 if new properties title already exists for the category', function () use ($url) {
    actAsUserWithPermission('add-product');
    $category = Category::factory()->create();
    $existingProperty = Property::factory(['category_id' => $category->id])->create();
    $properties = Property::factory(['category_id' => $category->id])->count(3)->make();
    $response = post($url, Product::factory([
        'category_id' => $category->id,
        'new_properties' => $properties
            ->map(fn ($item) => collect($item)->only('title')['title'])
            ->add($existingProperty->title)
            ->toArray(),
    ])->make()->toArray());
    $response->assertStatus(400);
});

it('returns 400 if input data is not valid', function ($key, $value) use ($url) {
    actAsUserWithPermission('add-product');
    $product = collect(Product::factory([$key => $value])->make());
    if (is_null($value)) {
        $product = $product->collect()->forget($key);
    }
    $response = post($url, $product->toArray());
    $response->assertStatus(400);
})->with([
    ['title', ''], //title => required - presented as empty string in request
    ['title', null], //title => required - not present in request
    ['title', 'a'], //title => min:3 
    ['description', 'first second'], // description => minWord:3
    ['quantity', ''], // quantity => required - presented as empty string in request
    ['quantity', null], // quantity => required - not presented in request
    ['quantity', -10], // quantity => gte:0
    ['price', ''], // price => required - presented as empty string in request
    ['price', null], // price => required - not presented in request
]);

it("returns 400 if new_images is not an array of files -> 'new_images.*' => ['file']", function () use ($url) {
    actAsUserWithPermission('add-product');
    $product = Product::factory()->make();
    $response = post($url, array_merge(
        $product->toArray(),
        ['new_images' => ['shite', 'might']]
    ));
    $response->assertStatus(400);
});

it('returns 400 if category_id is not a foreign key(does not map to any real category record) -> new ForeignKeyExists', function () use ($url) {
    actAsUserWithPermission('add-product');
    $response = post($url, Product::factory(['category_id' => 322])->make()->toArray());
    $response->assertStatus(400);
    expect($response->baseResponse->content())
        ->json()
        ->category_id->each(function ($m) {
            $m->toContain('322');
        });
});

it('returns 400 if property_ids is not an array of foreign keys -> new ForeignKeyExists', function () use ($url) {
    actAsUserWithPermission('add-product');
    $propertyCount = 3;
    $response = post($url, Product::factory([
        'property_ids' => Property::factory()
            ->count(3)
            ->create()
            ->map(fn ($p) => $p->id)
            ->add($propertyCount + 1)
            ->add($propertyCount + 2)
            ->add($propertyCount + 6)
            ->toArray(),
    ])->make()->toArray());
    $response->assertStatus(400);
    expect($response->baseResponse->content())
        ->json()
        ->property_ids->each(function ($m) {
            $m->toContain('properties');
            $m->toContain('4, 5, 9');
        });
});

it('it returns 401 if user is not authenticated', function() use ($url) {
    $product = Product::factory()->make();
    $response = post($url, $product->toArray());
    $response->assertUnauthorized();
});

it('it returns 403 if user is not permitted', function() use ($url) {
    actAsUser();
    $product = Product::factory()->make();
    $response = post($url, $product->toArray());
    $response->assertForbidden();
});
