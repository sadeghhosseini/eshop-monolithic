<?php


namespace Tests\Integration\Products;

use App\Models\Product;
use Tests\MyTestCase;

class GetProductsTests extends MyTestCase
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
        $products = json_decode($response->baseResponse->content());
        expect($products)->toBeArray();
        expect(count($products))->toEqual(0);
    
    }

    
    /**
    * @testdox returns status 200 if no products exist
    */
    public function testReturnsStatus200IfNoProductsExist() {
        $productCount = 100;
        $products = Product::factory()->count($productCount)->create();
        $response = $this->rget();
        $response->assertOk();
        $response->assertJsonCount($productCount);
        expect($response->json())
            ->toMatchArray($products->toArray());
    
    }

}