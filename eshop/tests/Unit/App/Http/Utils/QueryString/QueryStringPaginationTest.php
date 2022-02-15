<?php

namespace Tests\Unit\App\Http\Utils\QueryString;

use App\Helpers;
use App\Http\Utils\QueryString\QueryString;
use App\Models\Category;
use App\Models\Product;
use Mockery;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Mockery\MockInterface;
use Tests\AssertHelpers;

class QueryStringPaginationTest extends TestCase
{
    use AssertHelpers;

    private function mockRequest($offset, $limit) {
        //mocking request and injecting it to the Laravel's IOC container
        $mockRequest = Mockery::spy(Request::class, function (MockInterface $mockInterface) use ($offset, $limit) {
            $mockInterface->shouldReceive('has')->with('offset', 'limit')->andReturn(true);
            $mockInterface->shouldReceive('query')->with('offset')->andReturn($offset);
            $mockInterface->shouldReceive('query')->with('limit')->andReturn($limit);
        });
        $this->app->instance('request', $mockRequest); //injecting $mockRequest to laravel's ioc container so that request() helper which is used in the QueryString class returns our mocked version of request object

    }
    public function testPaginationE2E() {
        $offset = 3; 
        $limit = 20;
        $this->mockRequest($offset, $limit);

        $categories = Category::factory()->count(300)->create();
        $paginatedCategories = QueryString::createFromModelClass(Category::class)
            ->paginate()
            ->getCollection();

        $this->assertCount($limit, $paginatedCategories->toArray());
        
        $this->assertEqualArray(
            $categories->skip($offset)->take($limit),
            $paginatedCategories,
            exactEquality: true,
        );
    }

}