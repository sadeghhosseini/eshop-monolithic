<?php

use App\Models\Comment;
use App\Models\Product;

use function Pest\Laravel\get;
use function Tests\helpers\printEndpoint;
use function Tests\helpers\u;

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
$url = '/api/comments/{id}';
beforeAll(function () use ($url) {
    printEndpoint('GET', $url);
});

it("get a comment with it's direct replies(direct child comments)", function ($count) use ($url) {
    $comment = Comment::factory();
    if ($count > 0) {
        $comment = $comment->has(
            Comment::factory()->count($count),
            'replies',
        );
    }
    $comment = $comment->create();

    $response = get(u($url, 'id', $comment->id));
    $response->assertOk();
    $body = json_decode($response->baseResponse->content());
    expect(
        collect($body)->last()->replies
    )->toHaveCount($count);
})->with([0, 10, 5]);
