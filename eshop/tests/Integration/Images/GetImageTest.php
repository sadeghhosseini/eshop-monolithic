<?php

namespace Tests\Integration\Images;

use App\Models\Image;
use Tests\MyTestCase;


/**
* @testdox GET /api/images/{id}
*/
class GetImageTest extends MyTestCase
{
    public function getUrl()
    {
        return '/api/images/{id}';
    }

    
    /**
    * @testdox gets image
    */
    
    public function testGetsImage() {
        $image = Image::factory()->create();
        $response = $this->get($this->url('id', $image->id));
        $response->assertOk();
        expect($response->baseResponse->content())->json()
            ->id->toEqual($image->id)
            ->path->toEqual($image->path);
    
    }

    
    /**
    * @testdox returns 404 if image with the id does not exist
    */
    
    public function testReturns404IfImageWithTheIdDoesNotExist() {
        $response = $this->get($this->url('id', 1));
        $response->assertStatus(404);
    
    }
}