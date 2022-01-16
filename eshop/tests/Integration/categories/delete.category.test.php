<?php

use App\Models\Category;
use function Pest\Laravel\delete;
use function Tests\helpers\buildUrl;
use function Tests\helpers\printEndpoint;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

$url = '/api/categories/{id}';
beforeAll(function () use ($url) {
    printEndpoint('PATCH', $url);
});
beforeEach(function () {
    $this->seed(\Database\Seeders\PermissionSeeder::class);
    $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->registerPermissions();
});
it('delete - returns 404 if no category with id={id} exists', function () use ($url) {
    Laravel\Sanctum\Sanctum::actingAs(
        App\Models\User::factory()
            ->create()
            ->givePermissionTo('delete-any-categories')
    );
    $response = delete(buildUrl($url, ['id' => 300]));
    $response->assertStatus(404);
});

it('delete - returns 200 if category is deleted successfully', function () use ($url) {
    Laravel\Sanctum\Sanctum::actingAs(
        App\Models\User::factory()
            ->create()
            ->givePermissionTo('delete-any-categories')
    );
    $category = Category::factory()->create();
    $response = delete(buildUrl($url, ['id' => $category->id]));
    expect(Category::find($category->id))->toBeNull();
    $response->assertOk();
});
