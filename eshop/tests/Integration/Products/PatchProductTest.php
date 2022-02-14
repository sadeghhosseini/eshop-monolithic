<?php


namespace Tests\Integration\Products;

use App\Helpers;
use App\Models\Category;
use App\Models\Image;
use App\Models\Product;
use App\Models\Property;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Tests\MyTestCase;

class PatchProductTest extends MyTestCase
{
    public function getUrl()
    {
        return '/api/products/{id}';
    }

    public function dataset_testUpdatesSimpleProductFields()
    {
        return [
            ['title'],
            ['description'],
            ['quantity'],
            ['price'],
        ];
    }

    /**
     * @dataProvider dataset_testUpdatesSimpleProductFields
     * @testdox updates simple product fields
     */
    public function testUpdatesSimpleProductFields($key)
    {
        $this->actAsUserWithPermission('edit-product-any');
        $product = Product::factory()->create();
        $value = Product::factory()->make()->$key;
        $response = $this->rpatch(['id', $product->id], [$key => $value]);
        $data = $this->getResponseBody($response)->data;
        // expect($body->$key)->toEqual($value);
        $this->assertEquals($value, $data->$key);
    }


    /**
     * @testdox updates category_id
     */
    public function testUpdatesCategoryId()
    {
        $this->actAsUserWithPermission('edit-product-any');
        $category = Category::factory()->create();
        $product = Product::factory()->create();
        $response = $this->rpatch(['id', $product->id], [
            'category_id' => $category->id,
        ]);
        $data = $this->getResponseBody($response)->data;
        $this->assertEquals(
            $category->id,
            $data->category_id,
        );
    }


    /**
     * @testdox updates image_ids by adding new image_ids
     */
    public function testUpdatesImageIdsByAddingNewImageIds()
    {
        $this->actAsUserWithPermission('edit-product-any');
        $this->updateIdsByAdding(Image::class, 'image_ids', 'images');
    }


    /**
     * @testdox updates image_ids by adding new image_ids and removing some old image_ids
     */
    public function testUpdatesImageIdsByAddingNewImageIdsAndRemovingSomeOldImageIds()
    {
        $this->actAsUserWithPermission('edit-product-any');
        $this->updateIdsByAddingAndRemoving(Image::class, 'image_ids', 'images');
    }


    /**
     * @testdox updates property_ids by adding new propertyIds
     */
    public function testUpdatesPropertyIdsByAddingNewPropertyids()
    {
        $this->actAsUserWithPermission('edit-product-any');
        $this->updateIdsByAdding(Property::class, 'property_ids', 'properties');
    }


    /**
     * @testdox updates property_ids by adding new property_ids and removing some old property_ids
     */
    public function testUpdatesPropertyIdsByAddingNewPropertyIdsAndRemovingSomeOldPropertyIds()
    {
        $this->actAsUserWithPermission('edit-product-any');
        $this->updateIdsByAddingAndRemoving(Property::class, 'property_ids', 'properties');
    }


    /**
     * @testdox updates a product with new_images
     */
    public function testUpdatesAProductWithNewImages()
    {
        $this->actAsUserWithPermission('edit-product-any');
        $this->updateWithNewImages();
    }


    /**
     * @testdox updates a product with new_images and existing image_ids
     */
    public function testUpdatesAProductWithNewImagesAndExistingImageIds()
    {
        $this->actAsUserWithPermission('edit-product-any');
        $this->updateWithNewImages(Image::factory()->count(10)->create()->map(fn ($image) => $image->id)->toArray());
    }


    /**
     * @testdox updates a product with new properties
     */
    public function testUpdatesAProductWithNewProperties()
    {
        $this->actAsUserWithPermission('edit-product-any');
        $this->updateWithNewProperties();
    }


    /**
     * @testdox updates a product with new properties and existing property_ids
     */
    public function testUpdatesAProductWithNewPropertiesAndExistingPropertyIds()
    {
        $this->actAsUserWithPermission('edit-product-any');
        $this->updateWithNewProperties(Property::factory()->count(5)->create()->map(fn ($property) => $property->id)->toArray());
    }


    /**
     * @testdox returns 400 if new image_ids are not valid foreign keys
     */
    public function testReturns400IfNewImageIdsAreNotValidForeignKeys()
    {
        $this->actAsUserWithPermission('edit-product-any');
        $this->updateIdsByAddingNonValidForeignKey(Image::class, 'image_ids', 'images');
    }


    /**
     * @testdox returns 400 if new property_ids are not valid foreign keys
     */
    public function testReturns400IfNewPropertyIdsAreNotValidForeignKeys()
    {
        $this->actAsUserWithPermission('edit-product-any');
        $this->updateIdsByAddingNonValidForeignKey(Image::class, 'property_ids', 'properties');
    }


    /**
     * @testdox returns 401 if user is not authenticated
     */
    public function testReturns401IfUserIsNotAuthenticated()
    {
        $product = Product::factory()->create();
        $response = $this->rpatch(
            ['id', $product->id],
            Product::factory()->make()->toArray()
        );
        $response->assertUnauthorized();
    }


    /**
     * @testdox returns 403 if user is not permitted
     */
    public function testReturns403IfUserIsNotPermitted()
    {
        $this->actAsUser();
        $product = Product::factory()->create();
        $response = $this->rpatch(
            ['id', $product->id],
            Product::factory()->make()->toArray()
        );
        $response->assertForbidden();
    }




    /* helpers */

    private function updateIdsByAdding($ModelClass, $columnName, $relationField)
    {
        $newIds = $ModelClass::factory()->count(3)->create()->map(fn ($item) => $item->id);
        $product = Product::factory()
            ->has($ModelClass::factory()->count(5))->create();
        $ids = $product->$relationField->map(fn ($item) => $item->id);
        $response = $this->rpatch(['id', $product->id], [
            $columnName => [...$ids, ...$newIds],
        ]);
        $data = $this->getResponseBody($response)->data;
        $responseItemIds = collect($data->$relationField)->map(fn ($item) => $item->id)->toArray();
        $expecteItemIds = [...$ids, ...$newIds];
        // expect($responseItemIds)->toMatchArray($expecteItemIds);
        $this->assertMatchArray(
            $expecteItemIds,
            $responseItemIds,
        );
        $productImageIds = Product::find($product->id)->$relationField->map(fn ($item) => $item->id)->toArray();
        // expect($productImageIds)->toMatchArray([...$ids, ...$newIds]);
        $this->assertMatchArray(
            [...$ids, ...$newIds],
            $productImageIds,
        );
    }

    private function updateIdsByAddingAndRemoving($ModelClass, $columnName, $relationField)
    {
        $newIds = $ModelClass::factory()->count(3)->create()->map(fn ($item) => $item->id);
        $product = Product::factory()
            ->has($ModelClass::factory()->count(5))->create();
        $itemIds = $product->$relationField->map(fn ($item) => $item->id)->toArray();
        $itemIds = array_slice($itemIds, 0, 3);
        $response = $this->rpatch(['id', $product->id], [
            $columnName => [...$itemIds, ...$newIds],
        ]);
        $data = $this->getResponseBody($response)->data;
        $responseItemIds = collect($data->$relationField)->map(fn ($item) => $item->id)->toArray();
        $expectedItemIds = [...$itemIds, ...$newIds];
        expect($responseItemIds)->toMatchArray($expectedItemIds);
        $this->assertMatchArray(
            $responseItemIds,
            $expectedItemIds,
        );
        $productItemIds = Product::find($product->id)->$relationField->map(fn ($item) => $item->id)->toArray();
        // expect($productItemIds)->toMatchArray([...$itemIds, ...$newIds]);
        $this->assertMatchArray(
            [...$itemIds, ...$newIds],
            $productItemIds,
        );
    }

    private function updateIdsByAddingNonValidForeignKey($ModelClass, $columnName, $relationField)
    {
        $product = Product::factory()
            ->has($ModelClass::factory()->count(5))->create();
        $itemIds = $product->$relationField->map(fn ($item) => $item->id);
        $response = $this->rpatch(['id', $product->id], [
            $columnName => [...$itemIds, 55],
        ]);
        $response->assertStatus(400);
        $error = $this->getResponseBody($response);
        // expect($error->$columnName)->toBeArray();
        // expect($error->$columnName[0])->toContain('55');
        $this->assertIsArray($error->$columnName);
        $errorMessage = $error->$columnName[0];
        $this->assertStringContainsString('55', $errorMessage);
    }

    private function updateWithNewImages($existingImageIds = [])
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
        $response = $this->rpatch(['id', $productToBeUpdated->id], $product->toArray());
        $response->assertOk();

        $data = $this->getResponseBody($response)->data;
        $productId = $data->id;
        $newProduct = Product::find($productId);
        // expect($newProduct->images->toArray())->toBeArray();
        // expect($newProduct->images->toArray())->toHaveCount(count($images) + count($existingImageIds));
        
        $this->assertIsArray($newProduct->images->toArray());
        $this->assertCount(count($images) + count($existingImageIds), $newProduct->images->toArray());

        $newProductImages = $newProduct->images->filter(fn ($image) => !in_array($image->id, $existingImageIds))->toArray();
        foreach ($newProductImages as $image) {
            #@php-ignore //for vs-code linter
            Storage::disk('local')->assertExists($image['path']);
        }
    }

    private function updateWithNewProperties($existingPropertyIds = [])
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create();
        $properties = Property::factory([
            'category_id' => $category->id,
        ])->count(3)
            ->make();
        $response = $this->rpatch(['id', $product->id], Product::factory([
            'category_id' => $category->id,
            'new_properties' => $properties
                ->map(fn ($item) => collect($item)->only('title')['title'])
                ->toArray(),
            ...(empty($existingPropertyIds) ? [] : ['property_ids' => $existingPropertyIds]),
        ])->make()->toArray());

        $response->assertOk();
        $data = $this->getResponseBodyAsArray($response)['data'];
        $product = Product::find($data['id']);
        $this->assertCount(
            count($properties->toArray()) + count($existingPropertyIds),
            $product->properties->toArray(),
        );

        $this->assertEqualArray(
            [
                ...(collect($existingPropertyIds ?? [])->map(fn ($id) => Property::select('title', 'category_id')->find($id))->toArray()),
                ...$properties
                    ->map(fn ($item) => collect($item)->only('category_id', 'title'))
                    ->toArray(),
            ],
            $product->properties
                ->map(fn ($item) => collect($item)->only('category_id', 'title'))
                ->toArray(),
            uniqueKey: 'title',
        );
        
    }
}
