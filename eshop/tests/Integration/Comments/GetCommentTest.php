<?php


namespace Tests\Integration\Comments;

use App\Helpers;
use Tests\MyTestCase;
use App\Models\Comment;

/**
 * @testdox GET /api/comments/{id}
 */
class GetCommentTest extends MyTestCase
{
    public function getUrl()
    {
        return '/api/comments/{id}';
    }


    public function dataset_testGetACommentWithItSDirectRepliesDirectChildComments()
    {
        return [
            [10],
            [0],
            [5]
        ];
    }
    /**
     * @dataProvider dataset_testGetACommentWithItSDirectRepliesDirectChildComments
     * @testdox get a comment with it's direct replies -direct child comments-
     */

    public function testGetACommentWithItSDirectRepliesDirectChildComments($count)
    {
        $this->actAsUser();
        $comment = Comment::factory();
        if ($count > 0) {
            $comment = $comment->has(
                Comment::factory()->count($count),
                'replies',
            );
        }
        $comment = $comment->create();

        $response = $this->get($this->url('id', $comment->id));
        $response->assertOk();
        $body = $this->getResponseBody($response);

        $this->assertCount(
            $count,
            $body->data->replies
        );

    }
}
