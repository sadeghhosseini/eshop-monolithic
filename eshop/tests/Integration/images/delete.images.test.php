<?php

use function Pest\Laravel\delete;
use function Tests\helpers\actAsUser;
use function Tests\helpers\actAsUserWithPermission;
use function Tests\helpers\getUrl;
use function Tests\helpers\printEndpoint;
use function Tests\helpers\setupAuthorization;
use function Tests\helpers\u;

use App\Models\Image;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);
$url = '/api/images/{id}';
beforeAll(function () use ($url) {
    printEndpoint('POST', $url);
});

setupAuthorization(fn($closure) => beforeEach($closure));

/**
 * check if image is deleted both from filesystem and db
 */
it('deletes image from db and filesystem', function () use ($url) {
    actAsUserWithPermission('delete-image-any');
    Storage::fake('local');
    $imageFile = UploadedFile::fake('local')->create('img.png');
    $uploadedImagePath = Storage::putFile('images/', $imageFile);
    $image = Image::factory(['path' => $uploadedImagePath])->create();
    $response = delete(u($url, 'id', $image->id));
    $response->assertOk();
    //deleted from filesystem
    expect(Storage::disk('local')->exists($uploadedImagePath))->toBeFalse();
    //deleted from db
    expect(Image::where('id', $image->id)->exists())->toBeFalse();
});

/**
 * check if image is deleted from products_images record
 */
it('image deletion cascades to products_images records', function () use ($url) {
    actAsUserWithPermission('delete-image-any');
    $product = Product::factory()
        ->has(Image::factory())
        ->create();
    $imageId = $product->images[0]?->id;
    $response = delete(u($url, 'id', $imageId));
    $response->assertOk();
    expect(Image::where('id', $imageId)->exists())->toBeFalse();
});

it('returns 401 if user is not authenticated', function () use ($url) {
    $item = Image::factory()->create();
    $response = delete(
        u($url, 'id', $item->id),
    );
    $response->assertUnauthorized();
});
it('returns 403 if user is not permitted', function () use ($url) {
    actAsUser();
    $item = Image::factory()->create();
    $response = delete(
        u($url, 'id', $item->id),
    );
    $response->assertForbidden();
});