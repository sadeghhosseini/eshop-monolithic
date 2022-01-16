<?php

use App\Models\Category;
use App\Models\User;

use function Pest\Laravel\get;
use function Tests\helpers\printEndpoint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

$url = '/api/categories';
beforeAll(function() use ($url) {
    printEndpoint('GET', $url);
});

beforeEach(function() {
    $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->registerPermissions();
});

it('returns 200 if no categories exist', function () use ($url) {
    Sanctum::actingAs(User::factory()->create());
    $response = get($url);
    $response->assertStatus(200);
});

it('returns empty array, if category table is empty', function ()  use ($url) {
    Sanctum::actingAs(User::factory()->create());
    $response = get($url);
    $response->assertExactJson([]);
});

it('returns all the category records in db', function ()  use ($url) {
    Sanctum::actingAs(User::factory()->create());
    $count = 1;
    $categories = Category::factory()
    ->count($count)
    ->create();
    
    $response = get($url);
    $response->assertJsonCount($count);
    
    $responseItemsAsArray = $response->json();
    $expectedItemsAsArray = $categories->toArray();
    expect($responseItemsAsArray)
    ->toEqualCanonicalizing($expectedItemsAsArray);
    
});