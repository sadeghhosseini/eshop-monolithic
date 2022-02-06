<?php

namespace Tests\Integration\Images;

use App\Models\Image;
use Tests\MyTestCase;


/**
 * @testdox GET /api/images
 */

class GetImagesTest extends MyTestCase
{
    public function getUrl()
    {
        return '/api/images';
    }


    /**
     * @testdox returns all the images in db
     */

    public function testReturnsAllTheImagesInDb()
    {
        $images = Image::factory()->count(50)->create();
        $response = $this->get($this->getUrl());
        $response->assertOk();
        $response->assertJsonCount(count($images), 'data');
        $body = $this->getResponseBodyAsArray($response);
        expect($body['data'])->toMatchArray($images->toArray());
        $this->assertArrayHasKey('data', $body);
        $this->assertMatchArray(
            $images->toArray(),
            $body['data'],
        );
    }
}
