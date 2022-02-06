<?php


namespace Tests\Integration\Comments;

use App\Helpers;
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
        $body = $this->getResponseBody($response);
        $this->assertCount(2, $body->data);
        // expect(count($body->data))->toEqual(2);
        $this->assertEquals(5, $body->data[0]->replies_count);
        $this->assertEquals(0, $body->data[1]->replies_count);
        // expect($body->data[0]->replies_count)->toEqual(5);
        // expect($body->data[1]->replies_count)->toEqual(0);
    
    }
}
