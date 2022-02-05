<?php



namespace Tests\Comments;

use App\Models\Comment;
use App\Models\Product;
use Tests\MyTestCase;


/**
 * @testdox POST /api/products/{productId}/comments
 */

class PostCommentTest extends MyTestCase
{
    public function getUrl()
    {
        return '/api/products/{productId}/comments';
    }


    /**
     * @testdox creates a comment for a post
     */

    public function testCreatesACommentForAPost()
    {
        $user = $this->actAsUserWithPermission('add-comment');
        $product = Product::factory()->create();
        $comment = Comment::factory([
            'product_id' => $product->id,
            'commenter_id' => $user->id,
        ])->make();
        $response = $this->post(
            $this->url('productId', $product->id),
            $comment->makeHidden('commenter_id')->toArray()
        );
        $response->assertOk();
        expect($product->comments()->get()->last()->toArray())
            ->toMatchArray($comment->toArray());
    }



    public function dataset_testReturns400IfInputsAreInvalid()
    {
        return [
            ['content', ''], //content => required
        ];
    }
    /**
     * @dataProvider dataset_testReturns400IfInputsAreInvalid
     * @testdox returns 400 if inputs are invalid
     */
    public function testReturns400IfInputsAreInvalid($key, $value)
    {
        $this->actAsUserWithPermission('add-comment');
        $product = Product::factory()->create();
        $comment = Comment::factory([
            'product_id' => $product->id,
            $key => $value,
        ])->make();
        $response = $this->post($this->url('productId', $product->id), $comment->toArray());
        $response->assertStatus(400);
    }

    
    public function dataset_testReturns404IfProductDoesNotExist() {
        return [
            ['content', ''],//content => required
        ];
    }
    /**
     * @dataProvider dataset_testReturns404IfProductDoesNotExist
     * @testdox returns 404 if product does not exist
     */
    public function testReturns404IfProductDoesNotExist($key, $value)
    {
        $this->actAsUserWithPermission('add-comment');
        $comment = Comment::factory([
            'product_id' => 1,
            $key => $value,
        ])->make();
        $response = $this->post($this->url('productId', 1), $comment->toArray());
        $response->assertStatus(404);
    }
}
