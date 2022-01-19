<?php

use App\Models\Comment;
use App\Models\Product;

use function Pest\Laravel\patch;
use function Tests\helpers\actAsUserWithPermission;
use function Tests\helpers\printEndpoint;
use function Tests\helpers\u;

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
$url = '/api/comments/{id}';
beforeAll(function () use ($url) {
    printEndpoint('PATCH', $url);
});

Tests\helpers\setupAuthorization(fn($closure) => beforeEach($closure));

it("updates comment", function () use ($url) {
    $user = actAsUserWithPermission('edit-comment-own');
    $product = Product::factory()->create();
    $comment = Comment::factory([
        'product_id' => $product->id,
        'commenter_id' => $user->id,
    ])->create();
    $newComment = Comment::factory([
        'product_id' => $product->id,
        'content' => Comment::factory()->make()->content,
    ])->make();
    $response = patch(u($url, 'id', $comment->id), $newComment->toArray());
    $response->assertOk();
    $body = json_decode($response->baseResponse->content());
    expect($body)->toMatchArray($comment->toArray());
});
it("returns 400 if inputs not valid", function ($key, $value) use ($url) {
    $user = actAsUserWithPermission('edit-comment-own');
    $product = Product::factory()->create();
    $comment = Comment::factory([
        'product_id' => $product->id,
    ])->create();
    $newComment = Comment::factory([
        'product_id' => $product->id,
        'content' => Comment::factory()->make()->content,
        $key => $value,
    ])->make();
    $response = patch(u($url, 'id', $comment->id), $newComment->toArray());
    $response->assertStatus(400);
})->with([
    ['content', ''],//requied
    ['product_id', ''],//required
    ['product_id', 32],//ForeignKeyExists
]);
