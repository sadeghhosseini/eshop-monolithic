<?php

namespace Tests\Integration\Categories;

use App\Helpers;
use App\Models\Category;
use App\Models\Product;
use App\Models\Property;
use Tests\MyTestCase;

class GetCategoryPropertiesTest extends MyTestCase
{

    public function getUrl()
    {
        return "/api/categories/{id}/properties";
    }


    public function testGetPropertiesOfCertainCategory()
    {
        $category = Category::factory()->create();
        $categoryProperties = Property::factory(['category_id' => $category->id])->count(10)->create();
        $nonCategoryProperties = Property::factory()->count(20)->create();

        $response = $this->rget(['id', $category->id]);
        $response->assertOk();
        $responseProperties = collect($this->getResponseBodyAsArray($response)['data']);

        $categoryProperties->each(function ($property) use ($responseProperties) {
            $needle = $responseProperties->filter(fn ($rProperty) => $property['id'] == $rProperty['id']);
            $this->assertNotNull($needle, sprintf("property with id of %s is missing from response", $property['id']));
        });
    }



    /**
     * @testdox get properties with pagination
     */
    public function testGetPropertiesWithPagination()
    {
        $offset = 1;
        $limit = 25;
        $category = Category::factory()->create();
        $categoryProperties = Property::factory(['category_id' => $category->id])
            ->count(75)
            ->create();
        $response = $this->rget(['id', $category->id], qs: "?offset=$offset&limit=$limit");
        $response->assertOk();
        $data = $this->getResponseBodyAsArray($response)['data'];
        $this->assertCount($limit - $offset + 1, $data);
        $this->assertEqualArray(
            $categoryProperties->splice($offset, $limit),
            $data,
            exactEquality: true,
        );
    }
}
