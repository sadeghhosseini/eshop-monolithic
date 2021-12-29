<?php

use App\Models\Category;
use Illuminate\Database\Eloquent\Model;

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


use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;

uses(RefreshDatabase::class);


/* expect($response)->toEqualAll($categories, function ($responseItem, $expectedItem) {
            expect($responseItem->title)->toEqual($expectedItem->title);
            expect($responseItem->description)->toEqual($expectedItem->description);
            expect($responseItem->id)->toEqual($expectedItem->id);
        }); 
*/
expect()->extend('toEqualAll', function ($expectedItems, $expectationClosure) {
    /** @var Model[] $expectedItems */
    /** @var  TestResponse $response */
    $response = $this->value;
    $responseItems = json_decode($response->baseResponse->content());
    foreach ($expectedItems as $expectedItem) {
        $respItem = array_filter($responseItems, function ($value) use ($expectedItem) {
            return $expectedItem->id == $value->id;
        });
        if (empty($respItem)) {
            // expect($expectedItem->id)->toEqual(null);
            throw new Exception(
                "response item with id of {$expectedItem->id} does not exist"
            );
        }
        $respItem = array_pop($respItem);
        if ($expectationClosure) {
            $expectationClosure($respItem, $expectedItem);
        }
    }

    /* foreach($items as $item) {
        echo $item->title;
    } */
});

//Read
group('GET', '/api/categories', function ($verb, $url) {

    mit('returns 200 if no categories exist', function () use ($url) {
        $response = get($url);
        $response->assertStatus(200);
    }, $verb, $url);

    mit('returns empty array, if category table is empty', function ()  use ($url) {
        $response = get($url);
        $response->assertExactJson([]);
    }, $verb, $url);

    mit('returns all the category records in db', function ()  use ($url) {
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
        
    }, $verb, $url);
});

group('GET', '/api/categories/{id}', function ($verb, $url) {
    // $genUrl = fn ($id) => "/api/categories/${id}";
    mit('returns 404 if no category with the id of {id} exists', function () use ($url) {
        $response = get(buildUrl($url, ['id' => 1]));
        expect($response->baseResponse->content())->toBeJson();
        $response->assertStatus(404);
    }, $verb, $url);

    mit('returns the category with id = {id}', function () use ($url) {
        $category = Category::factory()->create();
        $response = get(buildUrl($url, ['id' => $category->id]));
        expect($response->baseResponse->content())->toBeJson();
        $response->assertOk();
        
        $expectedItemsAsArray = $category->toArray();
        expect($response->json())->toEqualCanonicalizing($expectedItemsAsArray);
    }, $verb, $url);
});

//Create
group('POST', '/api/categories', function ($verb, $url) {
    mit('creates a category without parent', function () use ($url) {
        $category = Category::factory()->make();
        $response = post($url, $category->toArray());
        $response->assertOk();
        $expectedItemsAsArray = $category->makeHidden('parent_id')->toArray();
        expect($response->json())
            ->toMatchArray($expectedItemsAsArray);
    }, $verb, $url);
    
    mit('creates a category with parent', function () use ($url) {
        $parentCategory = Category::factory()->create();
        $category = Category::factory(['parent_id' => $parentCategory->id])->make();
        $response = post($url, $category->toArray());
        $response->assertOk();
        $expectedResponseItemAsArray = $response->json();
        $expectedItemsAsArray = $category->toArray();
        expect($expectedResponseItemAsArray)
            ->toMatchArray($expectedItemsAsArray);
    }, $verb, $url);

    mit('returns 400 if input data is not valid', function () use ($url) {
        $category = Category::factory(['title' => ''])->make();
        $response = post($url, $category->toArray());
        $response->assertStatus(400);
    }, $verb, $url);
});

//Update
group('PATCH', '/api/categories/{id}', function ($verb, $url) {
    mit('returns 400 if input is not valid', function () use ($url) {
        $category = Category::factory()->create();

        $response = patch(
            buildUrl($url, ['id' => $category->id]),
            ['title' => '']
        );
        $response->assertStatus(400);
    }, $verb, $url);

    mit('returns 404 if no category with id={id} exists', function () use ($url) {
        $response = patch(buildUrl($url, ['id' => 32]));
        $response->assertStatus(404);
    }, $verb, $url);

    mit('returns updated category record', function () use ($url) {
        $category = Category::factory()->create();

        $newTitle = 'updated-title';
        $response = patch(buildUrl($url, ['id' => $category->id]), ['title' => $newTitle]);
        $category->title = $newTitle;
        $response->assertOk();
        expect($response->json())
            ->toMatchArray($category->toArray());
    }, $verb, $url);
});

//Delete
group('DELETE', '/api/categories/{id}', function ($verb, $url) {
    mit('delete - returns 404 if no category with id={id} exists', function () use ($url) {
        $response = delete(buildUrl($url, ['id' => 300]));
        $response->assertStatus(404);
    }, $verb, $url);

    mit('delete - returns 200 if category is deleted successfully', function () use ($url) {
        $category = Category::factory()->create();
        $response = delete(buildUrl($url, ['id' => $category->id]));
        expect(Category::find($category->id))->toBeNull();
        $response->assertOk();
    }, $verb, $url);
});
