<?php

use App\Models\Category;
use function Pest\Laravel\patch;
use function Tests\helpers\buildUrl;
use function Tests\helpers\printEndpoint;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

$url = '/api/categories/{id}';
beforeAll(function() use ($url) {
    printEndpoint('PATCH', $url);
});

beforeEach(function() {
    $this->seed(\Database\Seeders\PermissionSeeder::class);
    $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->registerPermissions();
});

it('returns 400 if input is not valid', function () use ($url) {
    Laravel\Sanctum\Sanctum::actingAs(App\Models\User::factory()->create()->givePermissionTo('edit-category-any'));
    $category = Category::factory()->create();
    $response = patch(
        buildUrl($url, ['id' => $category->id]),
        ['title' => '']
    );
    $response->assertStatus(400);
});

it('returns 404 if no category with id={id} exists', function () use ($url) {
    Laravel\Sanctum\Sanctum::actingAs(App\Models\User::factory()->create()->givePermissionTo('edit-category-any'));
    $response = patch(buildUrl($url, ['id' => 32]));
    $response->assertStatus(404);
});

it('returns updated category record', function () use ($url) {
    Laravel\Sanctum\Sanctum::actingAs(App\Models\User::factory()->create()->givePermissionTo('edit-category-any'));
    $category = Category::factory()->create();
    
    $newTitle = 'updated-title';
    $response = patch(buildUrl($url, ['id' => $category->id]), ['title' => $newTitle]);
    $category->title = $newTitle;
    $response->assertOk();
    expect($response->json())
    ->toMatchArray($category->toArray());
});