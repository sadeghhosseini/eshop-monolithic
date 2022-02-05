<?php


namespace Tests\Integration\Properties;

use App\Models\Property;
use Tests\MyTestCase;

class GetPropertiesTest extends MyTestCase
{
    public function getUrl()
    {
        return '/api/properties';
    }


    /**
     * @testdox gets all properties
     */
    public function testGetsAllProperties()
    {
        $properties = Property::factory()->count(20)->create();
        $response = $this->rget();
        $response->assertOk();
        expect($response->json())->toMatchArray($properties->toArray());
    }
}
