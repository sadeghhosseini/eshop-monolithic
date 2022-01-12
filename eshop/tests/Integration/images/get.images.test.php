<?php

use function Pest\Laravel\get;
use function Tests\helpers\printEndpoint;
use function Tests\helpers\u;

use App\Models\Image;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
$url = '/api/images/{id}';
beforeAll(function() use ($url) {
    printEndpoint('POST', $url);
});


//gets image by id
it ('gets image', function () use ($url) {
    $image = Image::factory()->create();
    $response = get(u($url, 'id', $image->id));
    $response->assertOk();
    expect($response->baseResponse->content())->json()
        ->id->toEqual($image->id)
        ->path->toEqual($image->path);
});

it('returns 404 if image with the id does not exist', function() use ($url) {
    $response = get(u($url, 'id', 1));
    $response->assertStatus(404);
});