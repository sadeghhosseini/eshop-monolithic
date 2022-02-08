<?php


namespace Tests\Integration\Properties;

use App\Helpers;
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
        $data = $this->getResponseBodyAsArray($response)['data'];
        // expect($data)->toMatchArray($properties->toArray());
        $this->assertMatchSubsetOfArray($data, $properties->toArray());
    }
}
