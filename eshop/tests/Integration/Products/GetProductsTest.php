<?php


namespace Tests\Integration\Products;

use App\Models\Category;
use App\Models\Product;
use Tests\MyTestCase;

class GetProductsTest extends MyTestCase
{
    public function getUrl()
    {
        return '/api/products';
    }

    
    /**
    * @testdox returns status 200 + all products in db
    */
    public function testReturnsStatus200AllProductsInDb() {
        $response = $this->rget();
        $response->assertOk();
    }

    
    /**
    * @testdox returns empty array if no products exist
    */
    public function testReturnsEmptyArrayIfNoProductsExist() {
        $response = $this->rget();
        $body = $this->getResponseBodyAsArray($response);
        // expect($products)->toBeArray();
        // expect(count($products))->toEqual(0);
        $this->assertArrayHasKey('data', $body);
        $this->assertEmpty($body['data']);
    }

    
    /**
    * @testdox returns status 200 if no products exist
    */
    public function testReturnsStatus200IfNoProductsExist() {
        $productCount = 100;
        $products = Product::factory()->count($productCount)->create();
        $response = $this->rget();
        $response->assertOk();
        $this->assertCount(
            $productCount,
            $this->getResponseBodyAsArray($response)['data'],
        );
        $this->assertMatchSubsetOfArray(
            $products->toArray(),
            $this->getResponseBodyAsArray($response),
        );
    }

}