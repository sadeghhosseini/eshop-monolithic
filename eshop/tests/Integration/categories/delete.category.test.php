<?php

use App\Models\Category;
use function Pest\Laravel\delete;
use function Tests\helpers\actAsUser;
use function Tests\helpers\buildUrl;
use function Tests\helpers\printEndpoint;
use function Tests\helpers\u;

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

$url = '/api/categories/{id}';
beforeAll(function () use ($url) {
    printEndpoint('PATCH', $url);
});
Tests\helpers\setupAuthorization(fn($closure) => beforeEach($closure));
it('delete - returns 404 if no category with id={id} exists', function () use ($url) {
    Laravel\Sanctum\Sanctum::actingAs(
        App\Models\User::factory()
            ->create()
            ->givePermissionTo('delete-category-any')
    );
    $response = delete(buildUrl($url, ['id' => 300]));
    $response->assertStatus(404);
});

it('delete - returns 200 if category is deleted successfully', function () use ($url) {
    Laravel\Sanctum\Sanctum::actingAs(
        App\Models\User::factory()
            ->create()
            ->givePermissionTo('delete-category-any')
    );
    $category = Category::factory()->create();
    $response = delete(buildUrl($url, ['id' => $category->id]));
    expect(Category::find($category->id))->toBeNull();
    $response->assertOk();
});

it('it returns 403 if user not permitted', function() use ($url) {
    actAsUser();
    $category = Category::factory()->create();
    $response = delete(u($url, 'id', $category->id));
    $response->assertForbidden();    
});

it('it returns 401 if user not authenticated', function() use ($url) {
    $category = Category::factory()->create();
    $response = delete(u($url, 'id', $category->id));
    $response->assertUnauthorized();    
});