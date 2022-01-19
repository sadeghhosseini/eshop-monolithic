<?php

use App\Models\Comment;
use App\Models\Product;

use function Pest\Laravel\post;
use function Tests\helpers\actAsUserWithPermission;
use function Tests\helpers\printEndpoint;
use function Tests\helpers\u;

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
$url = '/api/products/{productId}/comments';
beforeAll(function () use ($url) {
    printEndpoint('POST', $url);
});

Tests\helpers\setupAuthorization(fn($closure) => beforeEach($closure));

it('creates a comment for a post', function () use ($url) {
    actAsUserWithPermission('add-comment');
    $product = Product::factory()->create();
    $comment = Comment::factory([
        'product_id' => $product->id,
    ])->make();
    $response = post(u($url, 'productId', $product->id), $comment->toArray());
    $response->assertOk();
    expect($product->comments()->get()->last()->toArray())
        ->toMatchArray($comment->toArray());
});
it('returns 400 if inputs are invalid', function ($key, $value) use ($url) {
    actAsUserWithPermission('add-comment');
    $product = Product::factory()->create();
    $comment = Comment::factory([
        'product_id' => $product->id,
        $key => $value,
    ])->make();
    $response = post(u($url, 'productId', $product->id), $comment->toArray());
    $response->assertStatus(400);
    
})->with([
    ['content', ''],//content => required
]);
it('returns 404 if product does not exist', function ($key, $value) use ($url) {
    actAsUserWithPermission('add-comment');
    $comment = Comment::factory([
        'product_id' => 1,
        $key => $value,
    ])->make();
    $response = post(u($url, 'productId', 1), $comment->toArray());
    $response->assertStatus(404);
    
})->with([
    ['content', ''],//content => required
]);
