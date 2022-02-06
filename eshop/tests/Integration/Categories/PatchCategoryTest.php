<?php


namespace Tests\Integration\Categories;

use App\Models\Category;
use Tests\MyTestCase;


/**
* @testdox PATCH /api/categories/{id}
*/

class PatchCategoryTest extends MyTestCase
{
    public function getUrl()
    {
        return '/api/categories/{id}';
    }

    
    
    /**
    * @testdox returns 400 if input is not valid
    */
    
    public function testReturns400IfInputIsNotValid() {
        $this->actAsUserWithPermission('edit-category-any');
        $category = Category::factory()->create();
        $response = $this->patch(
            $this->url(['id' => $category->id]),
            ['title' => '']
        );
        $response->assertStatus(400);
    
    }

    
    /**
    * @testdox returns 404 if no category with id={id} exists
    */
    
    public function testReturns404IfNoCategoryWithIdIdExists() {
        $this->actAsUserWithPermission('edit-category-any');
        $response = $this->patch($this->url(['id' => 32]));
        $response->assertStatus(404);
    
    }

    
    /**
    * @testdox returns updated category record
    */
    
    public function testReturnsUpdatedCategoryRecord() {
        $this->actAsUserWithPermission('edit-category-any');
        $category = Category::factory()->create();
    
        $newTitle = 'updated-title';
        $response = $this->patch($this->url(['id' => $category->id]), ['title' => $newTitle]);
        $category->title = $newTitle;
        $response->assertOk();
        expect($response->json()['data'])
            ->toMatchArray($category->toArray());
    
    }

    
    /**
    * @testdox it returns 403 if user not permitted
    */
    
    public function testItReturns403IfUserNotPermitted() {
        $this->actAsUser();
        $category = Category::factory()->create();
        $response = $this->patch(
            $this->url('id', $category->id),
            Category::factory()->make()->only('title')
        );
        $response->assertForbidden();
    
    }

    
    /**
    * @testdox it returns 401 if user not authenticated
    */
    
    public function testItReturns401IfUserNotAuthenticated() {
        $category = Category::factory()->create();
        $response = $this->patch(
            $this->url('id', $category->id),
            Category::factory()->make()->only('title')
        );
        $response->assertUnauthorized();
    
    }

}