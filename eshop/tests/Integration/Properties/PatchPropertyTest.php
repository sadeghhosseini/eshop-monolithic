<?php

namespace Tests\Integration\Properties;

use App\Models\Property;
use Tests\MyTestCase;

class PatchPropertyTest extends MyTestCase
{
    public function getUrl()
    {
        return '/api/properties/{id}';
    }

    public function dataset_testUpdatesAProperty() {
        return [
            ['title', 'new-title'],
            ['is_visible', false],
            ['is_visible', true],
        ];
    }
    /**
     * @dataProvider dataset_testUpdatesAProperty
     * @testdox updates a property
     */
    public function testUpdatesAProperty($key, $value)
    {
        $this->actAsUserWithPermission('edit-property-any');
        $property = Property::factory()->create();
        $response = $this->rpatch(['id', $property->id], [
            $key => $value,
        ]);
        $response->assertOk();
        $this->assertEquals(
            $value,
            Property::find($property->id)->$key,
        );
    }

    public function dataset_testChecksValidationRules() {
        return [
            ['is_visible', 3], //is_visible => boolean(true|false|1|0)
            ['title', 1], //title => string
            ['title', 'ai'], //title => min:3
            ['category_id', 123], //category_id => ForeinKeyExists
        ];
    }
    /**
     * @dataProvider dataset_testChecksValidationRules
     * @testdox checks validation rules
     */
    public function testChecksValidationRules($key, $value)
    {
        $this->actAsUserWithPermission('edit-property-any');
        $property = Property::factory()->create();
        $response = $this->rpatch(['id', $property->id], [
            $key => $value,
        ]);
        $response->assertStatus(400);
    }

    /**
     * @testdox returns 401 if user is not authenticated
     */
    public function testReturns401IfUserIsNotAuthenticated()
    {
        $item = Property::factory()->create();
        $response = $this->rpatch(
            ['id', $item->id],
            Property::factory()->make()->toArray()
        );
        $response->assertUnauthorized();
    }

    /**
     * @testdox returns 403 if user is not permitted
     */
    public function testReturns403IfUserIsNotPermitted()
    {
        $this->actAsUser();
        $item = Property::factory()->create();
        $response = $this->rpatch(
            ['id', $item->id],
            Property::factory()->make()->toArray()
        );
        $response->assertForbidden();
    }
}
