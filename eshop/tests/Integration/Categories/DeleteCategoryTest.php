<?php


namespace Tests\Integration\Categories;

use App\Models\Category;
use Tests\MyTestCase;


/**
* @testdox DELETE /api/categories/{id}
*/

class DeleteCategoryTest extends MyTestCase
{
    public function getUrl()
    {
        return '/api/categories/{id}';
    }

    
    /**
    * @testdox delete - returns 404 if no category with id={id} exists
    */
    
    public function testDeleteReturns404IfNoCategoryWithIdIdExists() {
        $this->actAsUserWithPermission('delete-category-any');
        $response = $this->delete($this->url(['id' => 300]));
        $response->assertStatus(404);
    
    }

    
    /**
    * @testdox delete - returns 200 if category is deleted successfully
    */
    
    public function testDeleteReturns200IfCategoryIsDeletedSuccessfully() {
        $this->actAsUserWithPermission('delete-category-any');
        $category = Category::factory()->create();
        $response = $this->delete($this->url(['id' => $category->id]));
        expect(Category::find($category->id))->toBeNull();
        $response->assertOk();
    
    }

    
    /**
    * @testdox it returns 403 if user not permitted
    */
    
    public function testItReturns403IfUserNotPermitted() {
        $this->actAsUser();
        $category = Category::factory()->create();
        $response = $this->delete($this->url('id', $category->id));
        $response->assertForbidden();    
    
    }

    
    /**
    * @testdox it returns 401 if user not authenticated
    */
    
    public function testItReturns401IfUserNotAuthenticated() {
        $category = Category::factory()->create();
        $response = $this->delete($this->url('id', $category->id));
        $response->assertUnauthorized(); 
    
    }


}