<?php


namespace Tests\Integration\Products;

use App\Helpers;
use App\Models\Category;
use App\Models\Image;
use App\Models\Product;
use App\Models\Property;
use Tests\MyTestCase;

use function Tests\helpers\getResponseBody;

class GetProductTest extends MyTestCase
{
    public function getUrl()
    {
        return '/api/products/{id}';
    }


    /**
     * @testdox returns 404 if {id} matches no product
     */
    public function testReturns404IfIdMatchesNoProduct()
    {
        $response = $this->rget(['id' => 300]);
        $response->assertStatus(404);
    }


    /**
     * @testdox returns product with id={id}
     */
    public function testReturnsProductWithIdId()
    {
        $product = Product::factory()
            ->for(Category::factory())
            ->create();

        $response = $this->rget(['id' => $product->id]);
        expect($response->baseResponse->content())->toBeJson();
        $response->assertOk();

        $this->assertEqualsCanonicalizing(
            $product->toArray(),
            $this->getResponseBodyAsArray($response)['data'],
        );
    }


    /**
     * @testdox gets all the properties of certain product
     */
    public function testGetsAllThePropertiesOfCertainProduct()
    {
        $product = Product::factory()->has(Property::factory()->count(5))->create();
        $response = $this->get("/api/products/$product->id/properties");
        $response->assertOk();
        $this->assertMatchSubsetOfArray(
            $this->getResponseBodyAsArray($response)['data'],
            $product->properties->toArray(),
        );
    }


    /**
     * @testdox gets all the images of certain product
     */
    public function testGetsAllTheImagesOfCertainProduct()
    {
        $product = Product::factory()->has(Image::factory()->count(5))->create();
        $response = $this->get("/api/products/$product->id/images");
        $response->assertOk();
        $data = $this->getResponseBodyAsArray($response)['data'];
        $this->assertEqualArray(
            $product->images->toArray(),
            $data
        );
    }


    /**
     * @testdox gets the category of certain product
     */
    public function testGetsTheCategoryOfCertainProduct()
    {
        $product = Product::factory()->for(Category::factory())->create();
        $response = $this->get("/api/products/$product->id/category");
        $response->assertOk();
        // expect($response->json())->toMatchArray($product->category->toArray());
        $this->assertMatchSubsetOfArray(
            $this->getResponseBodyAsArray($response)['data'],
            $product->category->toArray(),
        );
    }
}
