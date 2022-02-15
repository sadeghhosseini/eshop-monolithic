<?php

namespace Tests\Unit\App\Http\Utils\QueryString;

use App\Helpers;
use App\Http\Utils\QueryString\Filter;
use App\Http\Utils\QueryString\FilterParser;
use App\Http\Utils\QueryString\SortParser;
use Tests\AssertHelpers;
use Tests\MyTestCase;
use Tests\TestCase;

class SortParserTest extends TestCase
{

    use AssertHelpers;

    public function dataset_testParseWithCorrectSortString()
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
     * @dataProvider dataset_testParseWithCorrectSortString
     * @testdox deserializes mondo-db-like filter query-string from url to  
     */
    public function testParseWithCorrectSortString($sortString, $expected)
    {
        $parser = new SortParser();
        $result = $parser->parse($sortString);
        for ($i = 0; $i < count($expected); ++$i) {
            $this->assertCount(2, $result[$i]);
            $this->assertEquals($expected[$i][0], $result[$i][0]);
            $this->assertEquals($expected[$i][1], $result[$i][1]);
        }
    }


    public function dataset_testParseWithWrongFormatSortString()
    {
        return [
            [
                '',
                [],
            ],
            [
                '[("title-1","asc)]', //missing double quotation after second value(asc)
                [],
            ],
            [
                '[("title-1","asc"), ("title-2", )]',
                [['title-1', 'asc']],
            ],

        ];
    }
    /**
     * @dataProvider dataset_testParseWithWrongFormatSortString
     */
    public function testParseWithWrongFormatSortString($sortString, $expected)
    {
        $parser = new SortParser();
        $result = $parser->parse($sortString);
        
        if(empty($expected)) {
            $this->assertCount(0, $result);
            return;
        }

        for ($i = 0; $i < count($expected); ++$i) {
            $this->assertCount(2, $result[$i]);
            $this->assertEquals($expected[$i][0], $result[$i][0]);
            $this->assertEquals($expected[$i][1], $result[$i][1]);
        }
    }
}
