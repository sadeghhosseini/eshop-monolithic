<?php

namespace Tests\Integration\Images;

use App\Helpers;
use App\Models\Image;
use App\Models\Product;
use Tests\MyTestCase;

class GetImageProductsTest extends MyTestCase
{
    public function getUrl()
    {
        return "/api/images/{id}/products";
    }

    public function testGetetProductsThatHaveTheSameImageAsOneOfTheirImages()
    {
        $products = Product::factory()->count(3)->create();
        $image = Image::factory()->create();
        $products[0]->images()->attach($image->id);
        $products[1]->images()->attach($image->id);
        $response = $this->rget(['id', $image->id]);

        $data = $this->getResponseBodyAsArray($response)['data'];
        
        $this->assertEqualArray(
            [
                $products->toArray()[0],
                $products->toArray()[1],
            ],
            $data,
            [
                'id',
                'category_id',
                'title',
                'quantity',
                'price',
            ],
            exactEquality: true,
        );
    }
}
