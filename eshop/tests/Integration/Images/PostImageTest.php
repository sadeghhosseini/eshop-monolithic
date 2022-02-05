<?php


namespace Tests\Images;

use App\Models\Image;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\MyTestCase;


/**
* @testdox POST /api/images
*/

class PostImageTest extends MyTestCase
{
    public function getUrl()
    {
        return '/api/images';
    }

    
    /**
    * @testdox uploads images and saves images path in db
    */
    
    public function testUploadsImagesAndSavesImagesPathInDb() {
        $this->actAsUserWithPermission('add-image');
        Storage::fake('local');
    
        $data = [
            [
                'path' => '',
                'file' => UploadedFile::fake()->create('image-1.jpg', 400),
            ],
            [
                'path' => '',
                'file' => UploadedFile::fake()->create('image-2.jpg', 400),
            ],
            [
                'path' => '/fun',
                'file' => UploadedFile::fake()->create('image-3.jpg', 400),
            ],
        ];
    
        $response = $this->post($this->getUrl(), [
            'images' => $data,
        ]);
    
        $response->assertOk();
    
        //check existing of the uploaded files on server
        Storage::disk('local')->assertExists('images/' . $data[0]['file']->hashName());
        Storage::disk('local')->assertExists('images/' . $data[1]['file']->hashName());
        Storage::disk('local')->assertExists('images/fun/' . $data[2]['file']->hashName());
    
        //check file paths on db
        $images = Image::whereIn('path', [
            'images/' . $data[0]['file']->hashName(),
            'images/' . $data[1]['file']->hashName(),
            'images/fun/' . $data[2]['file']->hashName(),
        ])->get()->toArray();
    
        expect($images)->not()->toBeNull();
        expect($images)->toHaveCount(3);
        Storage::fake('local');
    
    }

    
    /**
    * @testdox returns 400 if images size is more than the valid image size
    */
    
    public function testReturns400IfImagesSizeIsMoreThanTheValidImageSize() {
        $this->actAsUserWithPermission('add-image');
        Storage::fake('local');
        $response = $this->post($this->getUrl(), [
            'images' => [
                [
                    'path' => '',
                    'file' => UploadedFile::fake()->create('image.jpg', 600),
                ]
            ],
        ]);
        $response->assertStatus(400);
    
    }

    
    /**
    * @testdox returns 400 if uploaded file is anything other than jpg|png
    */
    
    public function testReturns400IfUploadedFileIsAnythingOtherThanJpgPng() {
        $this->actAsUserWithPermission('add-image');
        Storage::fake('local');
        $response = $this->post($this->getUrl(), [
            'images' => [
                [
                    'path' => '',
                    'file' => UploadedFile::fake()->create('image.gif', 300),
                ]
            ]
        ]);
        $response->assertStatus(400);
    
    }

    
    /**
    * @testdox returns 401 if user is not authenticated
    */
    
    public function testReturns401IfUserIsNotAuthenticated() {
    
        $item = Image::factory()->make();
        $response = $this->post(
            $this->getUrl(),
            $item->toArray()
        );
        $response->assertUnauthorized();
    }

    
    /**
    * @testdox returns 403 if user is not permitted
    */
    
    public function testReturns403IfUserIsNotPermitted() {
        $this->actAsUser();
        $item = Image::factory()->make();
        $response = $this->post(
            $this->getUrl(),
            $item->toArray()
        );
        $response->assertForbidden();
    
    }
}