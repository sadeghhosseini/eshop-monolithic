<?php


namespace Tests\Integration\Categories;

use App\Models\Category;
use Tests\MyTestCase;


/**
 * @testdox POST /api/categories
 */

class PostCategoryTest extends MyTestCase
{
    public function getUrl()
    {
        return '/api/categories';
    }


    /**
     * @testdox creates a category without parent
     */

    public function testCreatesACategoryWithoutParent()
    {
        $this->actAsUserWithPermission('add-category');
        $category = Category::factory()->make();
        $response = $this->post($this->getUrl(), $category->toArray());
        $response->assertCreated();
        $expectedItemsAsArray = $category->makeHidden('parent_id')->toArray();
        expect($response->json()['data'])
            ->toMatchArray($expectedItemsAsArray);
    }


    /**
     * @testdox creates a category with parent
     */

    public function testCreatesACategoryWithParent()
    {
        $this->actAsUserWithPermission('add-category');
        $parentCategory = Category::factory()->create();
        $category = Category::factory(['parent_id' => $parentCategory->id])->make();
        $response = $this->post($this->getUrl(), $category->toArray());
        $response->assertCreated();
        $expectedResponseItemAsArray = $response->json();
        $expectedItemsAsArray = $category->toArray();
        expect($expectedResponseItemAsArray['data'])
            ->toMatchArray($expectedItemsAsArray);
    }


    /**
     * @testdox returns 400 if input data is not valid
     */

    public function testReturns400IfInputDataIsNotValid()
    {
        $this->actAsUserWithPermission('add-category');
        $category = Category::factory(['title' => ''])->make();
        $response = $this->post($this->getUrl(), $category->toArray());
        $response->assertStatus(400);
    }


    /**
     * @testdox returns 403 if user is not permitted
     */

    public function testReturns403IfUserIsNotPermitted()
    {
        $user = $this->actAsUser();
        $result = $this->post($this->getUrl(), Category::factory()->make()->toArray());
        $result->assertForbidden(); //status = 403
    }


    /**
     * @testdox returns 401 if user is not authenticated
     */

    public function testReturns401IfUserIsNotAuthenticated()
    {
        $result = $this->post($this->getUrl(), Category::factory()->make()->toArray());
        $result->assertUnauthorized(); //status = 401
    }
}
