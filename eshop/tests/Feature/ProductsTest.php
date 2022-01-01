<?php

use App\Models\Category;
use App\Models\Image;
use App\Models\Product;
use App\Models\Property;

use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Tests\helpers\buildUrl;
use function Tests\helpers\colorize;
use function Tests\helpers\createRecords;
use function Tests\helpers\group;
use function Tests\helpers\mit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);


function selectKeys($arr, ...$keys)
{
    $result = [];
    foreach ($arr as $key => $value) {
        if (in_array($key, $keys)) {
            $result[$key] = $value;
        }
    }
    return $result;
}

function removekeys($arr, ...$keys)
{
    $result = [];
    foreach ($arr as $key => $value) {
        if (!in_array($key, $keys)) {
            $result[$key] = $value;
        }
    }
    return $result;
}

//READ
group('GET', '/api/products', function ($verb, $url) {
    mit('returns status 200 if no products exist', function () use ($verb, $url) {
        $response = get($url);
        $response->assertOk();
    }, $verb, $url);

    mit('returns empty array if no products exist', function () use ($verb, $url) {
        $response = get($url);
        $products = json_decode($response->baseResponse->content());
        expect($products)->toBeArray();
        expect(count($products))->toEqual(0);
    }, $verb, $url);

    mit('returns status 200 + all products in db', function () use ($url) {
        $productCount = 100;
        $products = Product::factory()->count($productCount)->create();
        $response = get($url);
        $response->assertOk();
        $response->assertJsonCount($productCount);
        expect($response->json())
            ->toMatchArray($products->toArray());
    }, $verb, $url);
});


group('GET', '/api/products/{id}', function ($verb, $url) {
    mit('returns 404 if {id} matches no product', function () use ($url) {
        $response = get(buildUrl($url, ['id' => 300]));
        $response->assertStatus(404);
    }, $verb, $url);

    mit('returns product with id={id}', function () use ($url) {
        $product = Product::factory()
            ->for(Category::factory())
            ->create();

        $response = get(buildUrl($url, ['id' => $product->id]));
        expect($response->baseResponse->content())->toBeJson();
        $response->assertOk();

        expect($response->json())
            ->toEqualCanonicalizing($product->toArray());
        /* $body = $response->baseResponse->content();
        expect($body)->toBeJson();
        expect($body)->json()
            ->title->ToEqual($product->title)
            ->description->ToEqual($product->description)
            ->price->ToEqual($product->price)
            ->quantity->toEqual($product->quantity)
            ->id->toEqual($product->id)
            ->category_id->toEqual($product->category_id); */
    }, $verb, $url);
});


//POST
group('POST', '/api/products/', function ($verb, $url) {
    mit('creates a product without properties and images', function () use ($url) {
        $category = Category::factory()->create();
        $product = Product::factory([
            'category_id' => $category->id,
        ])->make();
        $response = post($url, $product->toArray());
        expect($response->json())
            ->toMatchArray($product->toArray());
        $response->assertOk();
    }, $verb, $url);

    mit('creates a product with properties', function ()  use ($url) {
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

    mit('create a product with new properties', function ()  use ($url) {
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

    mit('returns 400 if new properties title already exists for the category', function () use ($url) {
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

    /**
     * asserts image file getting uploaded and saved on disk
     * asserts records are added in products_images table
     */
    mit('creates a product with new_images', function ()  use ($url) {
        $category = Category::factory()->create();
        Storage::fake('images');
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
    
    mit('returns 400 if image_ids is not an array of forein keys', function () use ($url) {
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
    
    mit('creates a product with image_ids', function () use ($url) {
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

    mit('returns 400 if input data is not valid', function ($key, $value) use ($url) {
        $product = collect(Product::factory([$key => $value])->make());
        if (is_null($value)) {
            $product = $product->collect()->forget($key);
        }
        $response = post($url, $product->toArray());
        $response->assertStatus(400);
    }, $verb, $url, [
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

    mit("returns 400 if new_images is not an array of files -> 'new_images.*' => ['file']", function () use ($url) {
        $product = Product::factory()->make();
        $response = post($url, array_merge(
            $product->toArray(),
            ['new_images' => ['shite', 'might']]
        ));
        $response->assertStatus(400);
    }, $verb, $url);

    mit('returns 400 if category_id does not map to any real category record -> new ForeignKeyExists', function () use ($url) {
        $response = post($url, Product::factory(['category_id' => 322])->make()->toArray());
        $response->assertStatus(400);
        expect($response->baseResponse->content())
            ->json()
            ->category_id->each(function ($m) {
                $m->toContain('322');
            });
    }, $verb, $url);
    
    mit('returns 400 if fks in property_ids do not map to any real property records -> new ForeignKeyExists', function () use ($url) {
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
    }, $verb, $url);
});
