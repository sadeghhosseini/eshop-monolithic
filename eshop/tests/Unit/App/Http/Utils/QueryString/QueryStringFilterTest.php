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

class QueryStringFilterTest extends TestCase
{
    use AssertHelpers;

    public function dataset_testFilter()
    {
        return [
            [
                '{"title": "book"}',
                [['title', '=', 'book']],
            ],
            [
                '{"age": {"$gt": 20}}',
                [['age', '>', '20']],
            ],
            [
                '{"age": {"$gte": 20}}',
                [['age', '>=', '20']],
            ],
            [
                '{"fname": "John", "lname": "Doe"}',
                [['fname', '=', 'John'], ['lname', '=', 'Doe']],
            ],
            [
                '{"lname": "Jack", "age": {"$gte": 20}}',
                [['lname', '=', 'Jack'], ['age', '>=', '20']],
            ],
        ];
    }

    /**
     * @dataProvider dataset_testFilter
     */
    public function testFilter($input, $expected)
    {
        $mockRequest = Mockery::spy(Request::class, function (MockInterface $mockInterface) use ($input) {
            $mockInterface->shouldReceive('has')->with('filter')->andReturn(true);
            $mockInterface->shouldReceive('query')->with('filter')->andReturn($input);
        });
        $this->app->instance('request', $mockRequest); //injecting $mockRequest to laravel's ioc container so that request() helper which is used in the QueryString class returns our mocked version of request object
        $mockBuilder = Mockery::spy(Builder::class);

        QueryString::createFromQueryBuilder($mockBuilder)->filter();
        $mockBuilder->shouldHaveReceived('where')->times(count($expected));
    }

    public function testFilterWithFieldLimitation()
    {
        $input = '{"age": {"$gt": 20}}';
        $mockRequest = Mockery::spy(Request::class, function (MockInterface $mockInterface) use ($input) {
            $mockInterface->shouldReceive('has')->with('filter')->andReturn(true);
            $mockInterface->shouldReceive('query')->with('filter')->andReturn($input);
        });
        $this->app->instance('request', $mockRequest); //injecting $mockRequest to laravel's ioc container so that request() helper which is used in the QueryString class returns our mocked version of request object
        $mockBuilder = Mockery::spy(Builder::class);

        //only filter by "title" is allowed
        QueryString::createFromQueryBuilder($mockBuilder)->filter(['title']);
        //hence where should not get called
        $mockBuilder->shouldNotHaveReceived('where');
    }

    public function testFilterE2E()
    {
        $qs = '{"quantity": {"$gte": 5}}';
        //mocking request and injecting it to the Laravel's IOC container
        $mockRequest = Mockery::spy(Request::class, function (MockInterface $mockInterface) use ($qs) {
            $mockInterface->shouldReceive('has')->with('filter')->andReturn(true);
            $mockInterface->shouldReceive('query')->with('filter')->andReturn($qs);
        });
        $this->app->instance('request', $mockRequest); //injecting $mockRequest to laravel's ioc container so that request() helper which is used in the QueryString class returns our mocked version of request object


        $quantities = [1, 3, 3, 5, 15, 32, 3, 2, 1, 20, 5, 4];
        $products = [];
        foreach ($quantities as $quantity) {
            $products[] = Product::factory(['quantity' => $quantity])->create()->toArray();
        }

        $filteredProducts = QueryString::createFromModelClass(Product::class)
            ->filter()
            ->getCollection()
            ->toArray();
        $expectedProducts = array_filter($products, function ($product) {
            return $product['quantity'] >= 5;
        });

        $this->assertEqualArray(
            $expectedProducts,
            $filteredProducts,
            ['quantity'],
            exactEquality: true
        );
    }
}
