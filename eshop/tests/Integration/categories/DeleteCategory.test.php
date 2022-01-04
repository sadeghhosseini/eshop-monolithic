<?php

use App\Models\Category;
use function Pest\Laravel\delete;
use function Tests\helpers\buildUrl;
use function Tests\helpers\printEndpoint;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

$url = '/api/categories/{id}';
beforeAll(function() use ($url) {
    printEndpoint('PATCH', $url);
});

it('delete - returns 404 if no category with id={id} exists', function () use ($url) {
    $response = delete(buildUrl($url, ['id' => 300]));
    $response->assertStatus(404);
});

it('delete - returns 200 if category is deleted successfully', function () use ($url) {
    $category = Category::factory()->create();
    $response = delete(buildUrl($url, ['id' => $category->id]));
    expect(Category::find($category->id))->toBeNull();
    $response->assertOk();
});



