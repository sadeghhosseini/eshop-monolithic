<?php

use App\Models\Category;
use function Pest\Laravel\post;
use function Tests\helpers\actAsUser;
use function Tests\helpers\actAsUserWithPermission;
use function Tests\helpers\preparePermissions;
use function Tests\helpers\printEndpoint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);
$url = '/api/categories';
beforeAll(function() use ($url) {
    printEndpoint('POST', $url);
});

Tests\helpers\setupAuthorization(fn($closure) => beforeEach($closure));

it('creates a category without parent', function () use ($url) {
    actAsUserWithPermission('add-category');
    $category = Category::factory()->make();
    $response = post($url, $category->toArray());
    $response->assertOk();
    $expectedItemsAsArray = $category->makeHidden('parent_id')->toArray();
    expect($response->json())
    ->toMatchArray($expectedItemsAsArray);
});

it('creates a category with parent', function () use ($url) {
    actAsUserWithPermission('add-category');
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
    actAsUserWithPermission('add-category');
    $category = Category::factory(['title' => ''])->make();
    $response = post($url, $category->toArray());
    $response->assertStatus(400);
});

it('returns 403 if user is not permitted', function() use ($url) {
    $user = actAsUser();
    $result = post($url, Category::factory()->make()->toArray());
    $result->assertForbidden();//status = 403
});

it('returns 401 if user is not authenticated', function() use ($url) {
    $result = post($url, Category::factory()->make()->toArray());
    $result->assertUnauthorized();//status = 401
});