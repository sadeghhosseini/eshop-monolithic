<?php


namespace Tests\Integration\Categories;

use App\Helpers;
use App\Models\Category;
use App\Models\Product;
use Tests\MyTestCase;

class GetCategoryProductsTest extends MyTestCase
{
    public function getUrl()
    {
        return "/api/categories/{id}/products";
    }


    /**
     * @testdox get products of certain category
     */
    public function testGetProductsOfCertainCategory()
    {
        $category = Category::factory()->create();
        $categoryProducts = Product::factory(['category_id' => $category->id])
            ->count(10)->create();
        $nonCategoryProducts = Product::factory()->count(20)->create();
        $response = $this->rget(['id', $category->id]);
        $response->assertOk();
        $data = $this->getResponseBodyAsArray($response)['data'];
        $responseProducts = $data;
        $responseProductIds = collect($responseProducts)->mapWithKeys(function ($product) {
            return [
                'id' => $product['id'],
            ];
        });
        $categoryProductIds = $categoryProducts->mapWithKeys(function ($product) {
            return [
                'id' => $product->id,
            ];
        });
        $this->assertCount(
            $categoryProducts->count(),
            $data,
        );

        $this->assertEqualArray(
            $categoryProducts->toArray(), 
            $responseProducts,
            ['id', 'title'],
        );
    }

    /**
    * @testdox get products with pagination
    */
    public function testGetProductsWithPagination() {
        $offset = 0;
        $limit = 30;
        $category = Category::factory()->create();
        $products = Product::factory(['category_id' => $category->id])->count(100)->create();
        $response = $this->rget(['id' => $category->id], qs: "?offset=$offset&limit=$limit");
        $response->assertOk();
        $data = $this->getResponseBodyAsArray($response)['data'];
        $this->assertCount($limit, $data);
        $this->assertEqualArray(
            $products->splice($offset, $limit),
            $data,
        );
    }
}
