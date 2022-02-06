<?php


namespace Tests\Integration\Categories;

use App\Models\Category;
use Tests\MyTestCase;


/**
 * @testdox GET /api/categories/{id}
 */
class GetCategoryTest extends MyTestCase
{
    public function getUrl()
    {
        return '/api/categories/{id}';
    }


    /**
     * @testdox returns 404 if no category with the id of {id} exists
     */
    public function testReturns404IfNoCategoryWithTheIdOfIdExists()
    {
        $response = $this->get($this->url(['id' => 1]));
        expect($response->baseResponse->content())->toBeJson();
        $response->assertStatus(404);
    }


    /**
     * @testdox returns the category with id = {id}
     */
    public function testReturnsTheCategoryWithIdEqualsId()
    {

        $category = Category::factory()->create();
        $response = $this->get($this->url(['id' => $category->id]));
        expect($response->baseResponse->content())->toBeJson();
        $response->assertOk();

        $expectedItemsAsArray = $category->toArray();
        expect(
            $response->json()['data']
        )->toEqualCanonicalizing($expectedItemsAsArray);
    }
}
