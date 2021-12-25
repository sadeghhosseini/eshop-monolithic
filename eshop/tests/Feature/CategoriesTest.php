<?php

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\patch;
use function Pest\Laravel\post;
use function Tests\helpers\buildUrl;
use function Tests\helpers\colorize;
use function Tests\helpers\createRecords;
use function Tests\helpers\endpoint;
use function Tests\helpers\group;
use function Tests\helpers\mit;

uses(RefreshDatabase::class);

//Read
group('GET', '/api/categories', function($verb, $url) {
    
    mit('returns 200 if no categories exist', function () use ($url) {
        $response = get($url);
        $response->assertStatus(200);
    }, $verb, $url);
    
    mit('returns empty array, if category table is empty', function ()  use ($url) {
        $response = get($url);
        $response->assertExactJson([]);
    }, $verb, $url);
    
    mit('returns all the category records in db', function ()  use ($url) {
        $result = createRecords(Category::class, [
            [
                'title' => 'Laptop',
                'description' => 'Best laptops ever',
            ],
            [
                'title' => 'PC',
                'description' => 'Best pcs ever',
            ],
            [
                'title' => 'Mobile Phones',
                'description' => 'Best phones in the market',
            ],
        ]);
        $response = get($url);
        
        foreach ($result->getItems() as $category) {
            $response->assertJsonFragment($category);
        }
        $response->assertJsonCount(count($result->getItems()));
    }, $verb, $url);
    
});

group('GET', '/api/categories/{id}', function($verb, $url) {
    // $genUrl = fn ($id) => "/api/categories/${id}";
    mit('returns 404 if no category with the id of {id} exists', function () use ($url){
        $response = get(buildUrl($url, ['id' => 1]));
        expect($response->baseResponse->content())->toBeJson();
        $response->assertStatus(404);
    }, $verb, $url);


    mit('returns the category with id = {id}', function () use ($url) {
        $result = createRecords(Category::class, [
            [
                'title' => 'title-1',
                'description' => 'description-1',
            ]
        ]);

        $createdCategoryModel = $result->lastModel;
        $response = get(buildUrl($url, ['id' => $createdCategoryModel->id]));
        expect($response->baseResponse->content())->toBeJson();
        $response->assertOk();
        $response->assertJsonFragment($result->lastItem);
    }, $verb, $url);
});

//Create
group('POST', '/api/categories', function($verb, $url) {
    mit('creates a user', function () use ($url) {
        $response = post($url, [
            'title' => 'title-1',
            'description' => 'description-1',
        ]);
        $newCategory = json_decode($response->baseResponse->content());
        $category = Category::find($newCategory->id);
        expect($category->id)->toEqual($newCategory->id);
        expect($category->title)->toEqual($newCategory->title);
        expect($category->description)->toEqual($newCategory->description);
    }, $verb, $url);

    mit('returns 400 if input data is not valid', function () use ($url) {
        $response = post($url, [
            'title' => '',
            'description' => '',
        ]);

        $response->assertStatus(400);
    }, $verb, $url);
});

//Update
group('PATCH', '/api/categories/{id}', function($verb, $url) {
    mit('returns 400 if input is not valid', function () use ($url) {
        $result = createRecords(Category::class, [
            [
                'title' => 'title-1',
                'description' => 'description-1',
            ]
        ]);

        $response = patch(
            buildUrl($url, ['id' => $result->lastModel->id]),
            ['title' => '']
        );
        $response->assertStatus(400);
    }, $verb, $url);

    mit('returns 404 if no category with id={id} exists', function () use ($url) {
        $response = patch(buildUrl($url, ['id' => 32]));
        $response->assertStatus(404);
    }, $verb, $url);

    mit('returns updated category record', function () use ($url) {
        $result = createRecords(Category::class, [
            [
                'title' => 'title-1',
                'description' => 'description-1',
            ]
        ]);

        $response = patch(buildUrl($url, ['id' => $result->lastModel->id]), ['title' => 'updated-title-1']);
        $response->assertOk();
        $response->assertJsonFragment(
            array_merge(
                $result->lastModel->attributesToArray(),
                ['title' => 'updated-title-1']
            )
        );
    }, $verb, $url);
});

//Delete
group('DELETE', '/api/categories/{id}', function($verb, $url) {
    mit('delete - returns 404 if no category with id={id} exists', function () use ($url) {
        $response = delete(buildUrl($url, ['id' => 300]));
        $response->assertStatus(404);
    }, $verb, $url);

    mit('delete - returns 200 if category is deleted successfully', function () use ($url) {
        $result = createRecords(Category::class, [
            [
                'title' => 'title-1',
                'description' => 'description-1',
            ]
        ]);
        $savedCategory = $result->lastModel;
        $response = delete(buildUrl($url, ['id' => $savedCategory->id]));
        $response->assertOk();
    }, $verb, $url);
});

