<?php


namespace Tests\Integration\Properties;

use App\Models\Product;
use App\Models\Property;
use Tests\MyTestCase;

class DeletePropertyTest extends MyTestCase
{
    public function getUrl()
    {
        return '/api/properties/{id}';
    }


    /**
     * @testdox deletes a property
     */
    public function testDeletesAProperty()
    {
        $this->actAsUserWithPermission('delete-property-any');
        $property = Property::factory()->create();
        $response = $this->rdelete(['id', $property->id]);
        $response->assertOk();
        expect(Property::where('id', $property->id)->exists())->toBeFalse();
    }

    /**
     * @testdox deletes related product_properties records
     */
    public function testDeletesRelatedProductPropertiesRecords()
    {
        $this->actAsUserWithPermission('delete-property-any');
        $property = Property::factory()->create();
        $products = Product::factory()->count(3)
            ->has(Property::factory()->count(3))
            ->create();
        $products->each(function (Product $product) use ($property) {
            $product->properties()->attach($property->id);
        });

        $response = $this->rdelete(['id', $property->id]);
        $response->assertOk();

        $products->each(function (Product $product) use ($property) {
            expect(
                $product->properties()
                    ->wherePivot('property_id', $property->id)
                    ->exists()
            )->toBeFalse();
        });
    }

    /**
     * @testdox returns 401 if user is not authenticated
     */
    public function testReturns401IfUserIsNotAuthenticated()
    {
        $item = Property::factory()->create();
        $response = $this->rdelete(
            ['id', $item->id],
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
        $response = $this->rdelete(
            ['id', $item->id],
        );
        $response->assertForbidden();
    }
}
