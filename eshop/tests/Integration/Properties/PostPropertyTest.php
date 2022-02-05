<?php


namespace Tests\Integration\Properties;

use App\Models\Property;
use Tests\MyTestCase;

class PostPropertyTest extends MyTestCase
{
    public function getUrl()
    {
        return '/api/properties';
    }

    /**
     * @testdox creates a property
     */
    public function testCreatesAProperty()
    {
        $this->actAsUserWithPermission('add-property');
        $property = Property::factory(['is_visible' => true])->make();
        $response = $this->rpost($property->toArray());
        $response->assertOk();
        expect(
            collect($response->json())->except(
                'id',
                'created_at',
                'updated_at'
            )->toArray()
        )->toMatchArray($property->toArray());
    }

    public function dataset_testChecksValidationRules()
    {
        return [
            ['is_visible', 3], //is_visible => boolean(true|false|1|0)
            ['title', 1], //title => string
            ['title', ''], //title => required
            ['title', 'ai'], //title => min:3
            ['category_id', 123], //category_id => ForeinKeyExists
            ['category_id', ''], //category_id => required
        ];
    }

    /**
     * @dataProvider dataset_testChecksValidationRules
     * @testdox checks validation rules
     */
    public function testChecksValidationRules($key, $value)
    {
        $this->actAsUserWithPermission('add-property');
        $property = Property::factory([
            $key => $value,
        ])->make();
        $response = $this->rpost($property->toArray());
        $response->assertStatus(400);
    }


    /**
     * @testdox returns 401 if user is not authenticated
     */
    public function testReturns401IfUserIsNotAuthenticated()
    {
        $property = Property::factory()->make();
        $response = $this->rpost($property->toArray());
        $response->assertUnauthorized();
    }


    /**
     * @testdox returns 403 if user is not permitted
     */
    public function testReturns403IfUserIsNotPermitted()
    {
        $this->actAsUser();
        $property = Property::factory()->make();
        $response = $this->rpost($property->toArray());
        $response->assertForbidden();
    }
}
