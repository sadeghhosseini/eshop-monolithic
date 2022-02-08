<?php


namespace Tests\Integration\Categories;

use App\Helpers;
use App\Models\Category;
use App\Models\Product;
use Tests\MyTestCase;


/**
* @testdox GET /api/categories
*/

class GetCategoriesTest extends MyTestCase
{
    public function getUrl()
    {
        return '/api/categories';
    }

    
    /**
    * @testdox returns 200 if no categories exist
    */
    
    public function testReturns200IfNoCategoriesExist() {
        $response = $this->get($this->getUrl());
        $response->assertStatus(200);
    
    }

    
    /**
    * @testdox returns empty array if category table is empty
    */
    
    public function testReturnsEmptyArrayIfCategoryTableIsEmpty() {
        $response = $this->get($this->getUrl());
        $response->assertOk();
        $body = (array) $this->getResponseBody($response);
        expect($body['data'])->toBeEmpty();
    
    }

    
    /**
    * @testdox returns all the category records in db
    */
    
    public function testReturnsAllTheCategoryRecordsInDb() {
        $count = 1;
        $categories = Category::factory()
        ->count($count)
        ->create();
        
        $response = $this->get($this->getUrl());
        $response->assertJsonCount($count);
        
        $responseItemsAsArray = $response->json();
        $expectedItemsAsArray = $categories->toArray();
        $this->assertMatchSubsetOfArray(
            $expectedItemsAsArray,
            $responseItemsAsArray['data'],
        );
    
    }

}