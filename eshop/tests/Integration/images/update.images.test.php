<?php

use function Pest\Laravel\patch;
use function Tests\helpers\actAsUser;
use function Tests\helpers\printEndpoint;
use function Tests\helpers\u;

use App\Models\Image;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
$url = '/api/images/{id}';
beforeAll(function() use ($url) {
    printEndpoint('POST', $url);
});

/* it('returns 401 if user is not authenticated', function () use ($url) {
    $item = Image::factory()->create();
    $response = patch(
        u($url, 'id', $item->id),
        Image::factory()->make()->toArray()
    );
    $response->assertUnauthorized();
});
it('returns 403 if user is not permitted', function () use ($url) {
    actAsUser();
    $item = Image::factory()->create();
    $response = patch(
        u($url, 'id', $item->id),
        Image::factory()->make()->toArray()
    );
    $response->assertForbidden();
}); */