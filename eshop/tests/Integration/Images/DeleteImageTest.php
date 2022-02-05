<?php

namespace Tests\Integration\Images;

use App\Models\Image;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\MyTestCase;

class DeleteImageTest extends MyTestCase
{
    public function getUrl()
    {
        return '/api/images/{id}';
    }


    /**
     * @testdox deletes image from db and filesystem
     */
    public function testDeletesImageFromDbAndFilesystem()
    {
        $this->actAsUserWithPermission('delete-image-any');
        Storage::fake('local');
        $imageFile = UploadedFile::fake('local')->create('img.png');
        $uploadedImagePath = Storage::putFile('images/', $imageFile);
        $image = Image::factory(['path' => $uploadedImagePath])->create();
        $response = $this->delete($this->url('id', $image->id));
        $response->assertOk();
        //deleted from filesystem
        expect(Storage::disk('local')->exists($uploadedImagePath))->toBeFalse();
        //deleted from db
        expect(Image::where('id', $image->id)->exists())->toBeFalse();
    }


    /**
     * @testdox image deletion cascades to products_images records
     */
    public function testImageDeletionCascadesToProductsImagesRecords()
    {
        $this->actAsUserWithPermission('delete-image-any');
        $product = Product::factory()
            ->has(Image::factory())
            ->create();
        $imageId = $product->images[0]?->id;
        $response = $this->delete($this->url('id', $imageId));
        $response->assertOk();
        expect(Image::where('id', $imageId)->exists())->toBeFalse();
    }
    /**
     * @testdox returns 401 if user is not authenticated
     */
    public function testReturns401IfUserIsNotAuthenticated()
    {
        $item = Image::factory()->create();
    $response = $this->delete(
        $this->url('id', $item->id),
    );
    $response->assertUnauthorized();
    }

    
    /**
    * @testdox returns 403 if user is not permitted
    */
    
    public function testReturns403IfUserIsNotPermitted() {
    $this->actAsUser();
    $item = Image::factory()->create();
    $response = $this->delete(
        $this->url('id', $item->id),
    );
    $response->assertForbidden();
    
    }
}
