<?php

use App\Models\Category;
use function Pest\Laravel\post;
use function Tests\helpers\preparePermissions;
use function Tests\helpers\printEndpoint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);
$url = '/api/categories';
beforeAll(function() use ($url) {
    printEndpoint('POST', $url);
});

beforeEach(function() {
    $this->seed(\Database\Seeders\PermissionSeeder::class);
    $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->registerPermissions();
});

it('creates a category without parent', function () use ($url) {
    // Laravel\Sanctum\Sanctum::actingAs(App\Models\User::factory()->create()->assignRole('admin'));
    Laravel\Sanctum\Sanctum::actingAs(App\Models\User::factory()->create()->givePermissionTo('add-category'));
    $category = Category::factory()->make();
    $response = post($url, $category->toArray());
    $response->assertOk();
    $expectedItemsAsArray = $category->makeHidden('parent_id')->toArray();
    expect($response->json())
    ->toMatchArray($expectedItemsAsArray);
});

it('creates a category with parent', function () use ($url) {
    Laravel\Sanctum\Sanctum::actingAs(App\Models\User::factory()->create()->givePermissionTo('add-category'));
    $parentCategory = Category::factory()->create();
    $category = Category::factory(['parent_id' => $parentCategory->id])->make();
    $response = post($url, $category->toArray());
    $response->assertOk();
    $expectedResponseItemAsArray = $response->json();
    $expectedItemsAsArray = $category->toArray();
    expect($expectedResponseItemAsArray)
    ->toMatchArray($expectedItemsAsArray);
});

it('returns 400 if input data is not valid', function () use ($url) {
    Laravel\Sanctum\Sanctum::actingAs(App\Models\User::factory()->create()->givePermissionTo('add-category'));
    $category = Category::factory(['title' => ''])->make();
    $response = post($url, $category->toArray());
    $response->assertStatus(400);
});