<?php



namespace Tests\Integration\Products;

use App\Helpers;
use App\Models\Category;
use App\Models\Image;
use App\Models\Product;
use App\Models\Property;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\MyTestCase;

class PostProductTest extends MyTestCase
{
    public function getUrl()
    {
        return '/api/products';
    }


    /**
     * @testdox creates a product without properties and images
     */
    public function testCreatesAProductWithoutPropertiesAndImages()
    {
        $this->actAsUserWithPermission('add-product');
        $category = Category::factory()->create();
        $product = Product::factory([
            'category_id' => $category->id,
        ])->make();
        $response = $this->rpost($product->toArray());
        $body = $this->getResponseBodyAsArray($response);

        $this->assertMatchArray(
            $product->toArray(),
            $body['data'],
        );
        $this->assertArrayHasKey(
            'images',
            $body['data'],
        );
        $this->assertArrayHasKey(
            'properties',
            $body['data'],
        );
        $this->assertEmpty($body['data']['images']);
        $this->assertEmpty($body['data']['properties']);
        $response->assertOk();
    }


    /**
     * @testdox creates a product with properties
     */
    public function testCreatesAProductWithProperties()
    {
        $this->actAsUserWithPermission('add-product');
        $properties = Property::factory()
            ->count(3)
            ->for(Category::factory())
            ->create();
        $categoryId = $properties->last()->category->id;
        $response = $this->rpost(Product::factory([
            'category_id' => $categoryId,
            'property_ids' => $properties->map(fn ($category) => $category->id)->toArray(),
        ])->make()->toArray());
        $body = $this->getResponseBodyAsArray($response);
        $this->assertArrayHasKey(
            'images',
            $body['data'],
        );
        $this->assertArrayHasKey(
            'properties',
            $body['data'],
        );
        $response->assertOk();
        $this->assertMatchArray(
            Product::all()
                ->last()
                ->properties
                ->map(fn ($p) => $p->id)
                ->toArray(),
            $properties
                ->map(fn ($p) => $p->id)
                ->toArray(),

        );
    }


    /**
     * @testdox creates a product with new properties
     */
    public function testCreatesAProductWithNewProperties()
    {
        $this->actAsUserWithPermission('add-product');
        $category = Category::factory()->create();
        $properties = Property::factory([
            'category_id' => $category->id,
        ])->count(3)
            ->make();
        $response = $this->rpost(Product::factory([
            'category_id' => $category->id,
            'new_properties' => $properties
                ->map(fn ($item) => collect($item)->only('title')['title'])
                ->toArray(),
        ])->make()->toArray());

        $response->assertOk();
        $body = $this->getResponseBodyAsArray($response);
        $product = Product::find($response['data']['id']);

        $this->assertMatchArray(
            $properties
                ->map(fn ($item) => collect($item)->only('category_id', 'title'))
                ->toArray(),
            $product->properties
                ->map(fn ($item) => collect($item)->only('category_id', 'title'))
                ->toArray()
        );
    }


    /**
     * @testdox creates a product with new_images
     */
    public function testCreatesAProductWithNewImages()
    {
        $this->actAsUserWithPermission('add-product');
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
        $response = $this->rpost($product->toArray());
        $response->assertOk();

        $body = $this->getResponseBody($response);
        $productId = $body->data->id;
        $newProduct = Product::find($productId);
        // expect($newProduct->images->toArray())->toBeArray();
        $this->assertIsArray($newProduct->images->toArray());
        // expect($newProduct->images->toArray())->toHaveCount(count($images));
        $this->assertCount(
            count($images),
            $newProduct->images->toArray(),
        );
        $newProductImages = $newProduct->images->toArray();
        foreach ($newProductImages as $image) {
            #@php-ignore //for vs-code linter
            Storage::disk('local')->assertExists($image['path']);
        }
    }


    /**
     * @testdox creates a product with image_ids
     */
    public function testCreatesAProductWithImageIds()
    {
        $this->actAsUserWithPermission('add-product');
        $images = Image::factory()->count(3)->create();
        $imageIds = $images->map(fn ($image) => $image->id);

        $response = $this->rpost(Product::factory([
            'image_ids' => $imageIds,
        ])->make()->toArray());
        $response->assertOk();

        $productId = $response->json()['data']['id'];
        $postImages = collect($response->json()['data']['images'])
            ->map(fn ($i) => $i['id'])->toArray();
        $newProductImages = Product::find($productId)->images
            ->map(fn ($i) => $i['id'])->toArray();
        $this->assertIsArray($postImages);
        $this->assertMatchArray($imageIds->toArray(), $postImages);
        $this->assertmatchArray($imageIds->toArray(), $newProductImages);
        // expect($postImages)->toBeArray();
        // expect($postImages)->toMatchArray($imageIds);
        // expect($newProductImages)->toMatchArray($imageIds);
    }


    /**
     * @testdox returns 400 if image_ids is not an array of forein keys
     */
    public function testReturns400IfImageIdsIsNotAnArrayOfForeinKeys()
    {
        $this->actAsUserWithPermission('add-product');
        $image = Image::factory()->create();
        $response = $this->rpost(Product::factory([
            'image_ids' => [$image->id, 2, 3],
        ])->make()->toArray());

        $error = $this->getResponseBody($response);
        $errorMessage = $error->image_ids[0];
        $this->assertStringNotContainsString($image->id, $errorMessage);
        $this->assertStringContainsString("2", $errorMessage);
        $this->assertStringContainsString("3", $errorMessage);
        $response->assertStatus(400);
    }



    /**
     * @testdox returns 400 if new properties title already exists for the category
     */
    public function testReturns400IfNewPropertiesTitleAlreadyExistsForTheCategory()
    {
        $this->actAsUserWithPermission('add-product');
        $category = Category::factory()->create();
        $existingProperty = Property::factory(['category_id' => $category->id])->create();
        $properties = Property::factory(['category_id' => $category->id])->count(3)->make();
        $response = $this->rpost(Product::factory([
            'category_id' => $category->id,
            'new_properties' => $properties
                ->map(fn ($item) => collect($item)->only('title')['title'])
                ->add($existingProperty->title)
                ->toArray(),
        ])->make()->toArray());
        $response->assertStatus(400);
    }

    public function provider_testReturns400IfInputDataIsNotValid()
    {
        return [
            ['title', ''], //title => required - presented as empty string in request
            ['title', null], //title => required - not present in request
            ['title', 'a'], //title => min:3 
            ['description', 'first second'], // description => minWord:3
            ['quantity', ''], // quantity => required - presented as empty string in request
            ['quantity', null], // quantity => required - not presented in request
            ['quantity', -10], // quantity => gte:0
            ['price', ''], // price => required - presented as empty string in request
            ['price', null], // price => required - not presented in request
        ];
    }
    /**
     * @dataProvider provider_testReturns400IfInputDataIsNotValid
     * @testdox returns 400 if input data is not valid
     */
    public function testReturns400IfInputDataIsNotValid($key, $value)
    {
        $this->actAsUserWithPermission('add-product');
        $product = collect(Product::factory([$key => $value])->make());
        if (is_null($value)) {
            $product = $product->collect()->forget($key);
        }
        $response = $this->rpost($product->toArray());
        $response->assertStatus(400);
    }


    /**
     * @testdox returns 400 if new_images is not an array of files -> 'new_images.*' => ['file']
     */
    public function testReturns400IfNewImagesIsNotAnArrayOfFilesNewImagesFile()
    {
        $this->actAsUserWithPermission('add-product');
        $product = Product::factory()->make();
        $response = $this->rpost(array_merge(
            $product->toArray(),
            ['new_images' => ['shite', 'might']]
        ));
        $response->assertStatus(400);
    }


    /**
     * @testdox returns 400 if category_id is not a foreign key(does not map to any real category record) -> new ForeignKeyExists
     */
    public function testReturns400IfCategoryIdIsNotAForeignKeyDoesNotMapToAnyRealCategoryRecordNewForeignkeyexists()
    {
        $this->actAsUserWithPermission('add-product');
        $response = $this->rpost(Product::factory(['category_id' => 322])->make()->toArray());
        $response->assertStatus(400);
        $body = $this->getResponseBody($response);
        $errorMessage = $body->category_id[0];
        $this->assertStringContainsString('322', $errorMessage);
    }


    /**
     * @testdox returns 400 if property_ids is not an array of foreign keys -> new ForeignKeyExists
     */
    public function testReturns400IfPropertyIdsIsNotAnArrayOfForeignKeysNewForeignkeyexists()
    {
        $this->actAsUserWithPermission('add-product');
        $propertyCount = 3;
        $response = $this->rpost(Product::factory([
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
        $errorMessage = $response->json()['property_ids'][0];
        $this->assertStringContainsString('properties', $errorMessage);
        $this->assertStringContainsString('4', $errorMessage);
        $this->assertStringContainsString('5', $errorMessage);
        $this->assertStringContainsString('9', $errorMessage);
    }


    /**
     * @testdox it returns 401 if user is not authenticated
     */
    public function testItReturns401IfUserIsNotAuthenticated()
    {
        $product = Product::factory()->make();
        $response = $this->rpost($product->toArray());
        $response->assertUnauthorized();
    }


    /**
     * @testdox it returns 403 if user is not permitted
     */
    public function testItReturns403IfUserIsNotPermitted()
    {
        $this->actAsUser();
        $product = Product::factory()->make();
        $response = $this->rpost($product->toArray());
        $response->assertForbidden();
    }
}
