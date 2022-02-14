<?php

namespace Tests\Unit\App\Http\Utils\QueryString;

use App\Helpers;
use App\Http\Utils\QueryString\Filter;
use App\Http\Utils\QueryString\FilterParser;
use Tests\AssertHelpers;
use Tests\MyTestCase;
use Tests\TestCase;

class FilterParserTest extends TestCase
{

    use AssertHelpers;

    public function dataset_deserializationTest()
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
     * @dataProvider dataset_deserializationTest
     * @testdox deserializes mondo-db-like filter query-string from url to  
     */
    public function testDeserialization($input, $expected)
    {
        $deserializer = new FilterParser();
        $result = $deserializer->deserialize($input);
        
        for ($i = 0; $i < count($expected); ++$i) {
            $this->assertCount(3, $result[$i]);
            $this->assertEquals($expected[$i][0], $result[$i][0]);        
            $this->assertEquals($expected[$i][1], $result[$i][1]);        
            $this->assertEquals($expected[$i][2], $result[$i][2]);
        }
    }
}
