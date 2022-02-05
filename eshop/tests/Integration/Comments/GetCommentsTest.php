<?php


namespace Tests\Integration\Comments;

use App\Models\Comment;
use App\Models\Product;
use Tests\MyTestCase;


/**
 * @testdox GET /api/products/{id}/comments
 */

class GetCommentsTest extends MyTestCase
{
    public function getUrl()
    {
        return '/api/products/{id}/comments';
    }


    
    /**
    * @testdox gets all comments with their reply count - number of direct children comments
    */
    
    public function testGetsAllCommentsWithTheirReplyCountNumberOfDirectChildrenComments() {
        $this->actAsUser();
        $product = Product::factory()->create();
        $commentWithReply = Comment::factory([
            'product_id' => $product->id,
        ])->has(
            Comment::factory([
                'product_id' => $product->id,
            ])->count(5),
            'replies',
        )->create();
        $commentWithoutReply = Comment::factory([
            'product_id' => $product->id,
        ])->create();
    
        $response = $this->get($this->url('id', $product->id));
        $response->assertOk();
        $body = json_decode($response->baseResponse->content());
        expect(count($body))->toEqual(2);
        expect($body[0]->replies_count)->toEqual(5);
        expect($body[1]->replies_count)->toEqual(0);
    
    }
}
