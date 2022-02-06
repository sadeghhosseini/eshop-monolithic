<?php



namespace Tests\Integration\Comments;

use App\Helpers;
use App\Models\Comment;
use App\Models\Product;
use Faker\Extension\Helper;
use Tests\MyTestCase;


/**
 * @testdox PATCH /api/comments/{id}
 */
class PatchCommentTest extends MyTestCase
{
    public function getUrl()
    {
        return '/api/comments/{id}';
    }


    /**
     * @testdox updates comment
     */

    public function testUpdatesComment()
    {
        $user = $this->actAsUserWithPermission('edit-comment-own');
        $product = Product::factory()->create();
        $comment = Comment::factory([
            'product_id' => $product->id,
            'commenter_id' => $user->id,
        ])->create();
        $newComment = Comment::factory([
            'product_id' => $product->id,
            'content' => Comment::factory()->make()->content,
        ])->make();
        $response = $this->patch($this->url('id', $comment->id), $newComment->toArray());
        $response->assertOk();
        $body = $this->getResponseBody($response);
        $fields = [
            'id',
            'content',
            'parent_id',
            'created_at',
            'updated_at',
        ];
        $this->assertEqualsCanonicalizing(
            collect($comment)->only($fields)->toArray(),
            collect($body->data)->only($fields)->toArray(),
        );
    }


    public function dataset_testReturns400IfInputsNotValid()
    {
        return [
            ['content', ''], //requied
            ['product_id', ''], //required
            ['product_id', 32], //ForeignKeyExists
        ];
    }
    /**
     * @dataProvider dataset_testReturns400IfInputsNotValid
     * @testdox returns 400 if inputs not valid
     */
    public function testReturns400IfInputsNotValid($key, $value)
    {
        $user = $this->actAsUserWithPermission('edit-comment-own');
        $product = Product::factory()->create();
        $comment = Comment::factory([
            'product_id' => $product->id,
        ])->create();
        $newComment = Comment::factory([
            'product_id' => $product->id,
            'content' => Comment::factory()->make()->content,
            $key => $value,
        ])->make();
        $response = $this->patch($this->url('id', $comment->id), $newComment->toArray());
        $response->assertStatus(400);
    }
}
