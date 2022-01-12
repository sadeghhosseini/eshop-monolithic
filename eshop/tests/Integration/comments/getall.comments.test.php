<?php

use App\Models\Comment;
use App\Models\Product;

use function Pest\Laravel\get;
use function Tests\helpers\printEndpoint;
use function Tests\helpers\u;

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
$url = '/api/products/{id}/comments';
beforeAll(function () use ($url) {
    printEndpoint('GET', $url);
});

it('gets all comments with their reply count - number of direct children comments', function () use ($url) {
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

    $response = get(u($url, 'id', $product->id));
    $response->assertOk();
    $body = json_decode($response->baseResponse->content());
    expect(count($body))->toEqual(2);
    expect($body[0]->replies_count)->toEqual(5);
    expect($body[1]->replies_count)->toEqual(0);
});
