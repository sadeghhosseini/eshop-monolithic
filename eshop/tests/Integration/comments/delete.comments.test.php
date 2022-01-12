<?php

use App\Models\Comment;
use App\Models\Product;

use function Pest\Laravel\delete;
use function Tests\helpers\printEndpoint;
use function Tests\helpers\u;

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
$url = '/api/comments/{id}';
beforeAll(function () use ($url) {
    printEndpoint('DELETE', $url);
});

it("deletes a comment and all it's children and descendants", function () use ($url) {
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

    $response = delete(u($url, 'id', $grnadParentComment->id));
    $response->assertOk();
    print_r(Comment::all()->toArray());
    expect(Comment::where('id', $grnadParentComment->id)->exists())->toBeFalse();
    expect(Comment::where('id', $parentComment->id)->exists())->toBeFalse();
    expect(Comment::where('id', $uncleComment->id)->exists())->toBeFalse();
    expect(Comment::where('id', $childComment->id)->exists())->toBeFalse();
});
it("deletes only a comment and all it's children and descendants", function () use ($url) {
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

    $response = delete(u($url, 'id', $parentComment->id));
    $response->assertOk();
    expect(Comment::where('id', $parentComment->id)->exists())->toBeFalse();
    expect(Comment::where('id', $uncleComment->id)->exists())->toBeTrue();
    expect(Comment::where('id', $grnadParentComment->id)->exists())->toBeTrue();
    expect(Comment::where('id', $childComment->id)->exists())->toBeFalse();
});
