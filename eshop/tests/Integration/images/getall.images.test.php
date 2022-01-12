<?php

use function Pest\Laravel\get;
use function Tests\helpers\printEndpoint;

use App\Models\Image;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
$url = '/api/images';
beforeAll(function() use ($url) {
    printEndpoint('POST', $url);
});


it ('returns all the images in db', function () use ($url) {
    $images = Image::factory()->count(50)->create();
    $response = get($url);
    $response->assertOk();
    $response->assertJsonCount(count($images));
    expect($response->json())->toMatchArray($images->toArray());
});
