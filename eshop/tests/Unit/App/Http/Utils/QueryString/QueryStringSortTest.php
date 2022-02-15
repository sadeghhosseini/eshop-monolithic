<?php

namespace Tests\Unit\App\Http\Utils\QueryString;

use App\Helpers;
use App\Http\Utils\QueryString\QueryString;
use App\Models\Category;
use Mockery;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Mockery\MockInterface;

class QueryStringSortTest extends TestCase
{

    public function dataset_testSort()
    {
        return [
            [
                '[("title-1","asc"), ("title-2", "desc")]',
                [['title-1', 'asc'], ['title-2', 'desc']],
            ],
            [
                '[("title-1",    "asc")]',
                [['title-1', 'asc']],
            ],
            [
                '[("title-1","asc")]',
                [['title-1', 'asc']],
            ],
            [
                '[("title-1","asc"), ("title-2", "desc"),         ("title-3",    "asc")]',
                [['title-1', 'asc'], ['title-2', 'desc'], ['title-3', 'asc']],
            ],
        ];
    }

    /**
     * @dataProvider dataset_testSort
     * @testdox test sort
     */
    public function testSort($input, $expected)
    {
        $mockRequest = Mockery::spy(Request::class, function (MockInterface $mockInterface) use ($input) {
            $mockInterface->shouldReceive('has')->with('sort')->andReturn(true);
            $mockInterface->shouldReceive('query')->with('sort')->andReturn($input);
        });
        $this->app->instance('request', $mockRequest); //injecting $mockRequest to laravel's ioc container so that request() helper which is used in the QueryString class returns our mocked version of request object
        $mockBuilder = Mockery::spy(Builder::class);

        QueryString::createFromQueryBuilder($mockBuilder)->sort();
        $mockBuilder->shouldHaveReceived('orderBy')->times(count($expected));
    }

    public function testSortE2E()
    {
        $qs = '[("title","asc")]';
        $mockRequest = Mockery::spy(Request::class, function (MockInterface $mockInterface) use ($qs) {
            $mockInterface->shouldReceive('has')->with('sort')->andReturn(true);
            $mockInterface->shouldReceive('query')->with('sort')->andReturn($qs);
        });
        $this->app->instance('request', $mockRequest); //injecting $mockRequest to laravel's ioc container so that request() helper which is used in the QueryString class returns our mocked version of request object


        //populating in-memory db
        $titles = [
            'c-title',
            'a-title',
            'f-title',
            'e-title',
            'd-title',
            'g-title',
            'b-title',
        ];
        $categories = [];
        foreach ($titles as $title) {
            $categories[] = Category::factory(['title' => $title])->create()->toArray();
        }
        $sortedCategories = QueryString::createFromModelClass(Category::class)
            ->sort()
            ->getCollection();
        
        usort($categories, function ($categorya, $categoryb) {
            return $categorya['title'] > $categoryb['title'];
        });
        
        for($i = 0; $i < count($sortedCategories); ++$i) {
            $this->assertEquals(
                $sortedCategories[$i]['title'],
                $categories[$i]['title'],
            );
        }
    }
}
