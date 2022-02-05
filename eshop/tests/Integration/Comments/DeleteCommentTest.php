<?php


namespace Tests\Integration\Comments;

use App\Models\Comment;
use App\Models\Product;
use Tests\MyTestCase;


/**
 * @testdox DLEETE /api/comments/{id}
 */

class DeleteCommentTest extends MyTestCase
{
    public function getUrl()
    {
        return '/api/comments/{id}';
    }



    /**
     * @testdox deletes a comment and all it's children and descendants
     */

    public function testDeletesACommentAndAllItSChildrenAndDescendants()
    {
        $user = $this->actAsUserWithPermission('delete-comment-any');
        $product = Product::factory()->create();
        $grnadParentComment = Comment::factory(['product_id' => $product->id])
            ->create();
        $parentComment = Comment::factory([
            'product_id' => $product->id,
            'parent_id' => $grnadParentComment->id,
        ])->create();
        $uncleComment = Comment::factory([
            'product_id' => $product->id,
            'parent_id' => $grnadParentComment->id,
        ])->create();
        $childComment = Comment::factory([
            'product_id' => $product->id,
            'parent_id' => $parentComment->id,
        ])->create();

        $response = $this->delete($this->url('id', $grnadParentComment->id));
        $response->assertOk();
        expect(Comment::where('id', $grnadParentComment->id)->exists())->toBeFalse();
        expect(Comment::where('id', $parentComment->id)->exists())->toBeFalse();
        expect(Comment::where('id', $uncleComment->id)->exists())->toBeFalse();
        expect(Comment::where('id', $childComment->id)->exists())->toBeFalse();
    }


    /**
     * @testdox deletes only a comment and all it's children and descendants
     */

    public function testDeletesOnlyACommentAndAllItSChildrenAndDescendants()
    {
        $user = $this->actAsUserWithPermission('delete-comment-any');
        $product = Product::factory()->create();
        $grnadParentComment = Comment::factory(['product_id' => $product->id])
            ->create();
        $parentComment = Comment::factory([
            'product_id' => $product->id,
            'parent_id' => $grnadParentComment->id,
        ])->create();
        $uncleComment = Comment::factory([
            'product_id' => $product->id,
            'parent_id' => $grnadParentComment->id,
        ])->create();
        $childComment = Comment::factory([
            'product_id' => $product->id,
            'parent_id' => $parentComment->id,
        ])->create();

        $response = $this->delete($this->url('id', $parentComment->id));
        $response->assertOk();
        expect(Comment::where('id', $parentComment->id)->exists())->toBeFalse();
        expect(Comment::where('id', $uncleComment->id)->exists())->toBeTrue();
        expect(Comment::where('id', $grnadParentComment->id)->exists())->toBeTrue();
        expect(Comment::where('id', $childComment->id)->exists())->toBeFalse();
    }
}
