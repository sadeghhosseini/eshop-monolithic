<?php


namespace Tests\Integration\Properties;

use App\Models\Property;
use Tests\MyTestCase;

class GetPropertyTest extends MyTestCase
{
    public function getUrl()
    {
        return '/api/properties/{id}';
    }


    /**
     * @testdox gets property by id
     */
    public function testGetsPropertyById()
    {
        $property = Property::factory()->create();
        $response = $this->rget(['id', $property->id]);
        $response->assertOk();
        expect($response->json())->title->toEqual($property->title);
    }

    /**
     * @testdox returns 404 if property does not exist
     */
    public function testReturns404IfPropertyDoesNotExist()
    {
        $response = $this->rget(['id', 1]);
        $response->assertStatus(404);
    }
}
