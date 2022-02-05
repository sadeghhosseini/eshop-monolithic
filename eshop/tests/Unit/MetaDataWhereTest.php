<?php

namespace Tests\Unit;

use App\Utils\RestResponse\MetaData\MetaDataWhere;
use Tests\TestCase;

class MetaDataWhereTest extends TestCase
{

    /**
     * @testdox tests MetaDataWhere
     */
    public function testTestsMetadatawhere()
    {
        $where = MetaDataWhere::create([
            "age" => ['$gt' => 20], //mongodb-style query
            "lastName" => 'hosseini',
        ]);
        // expect($where->get())->toContain('{"age":{"$gt":20},"lastName":"hosseini"}');
        $this->assertStringContainsString(
            '{"age":{"$gt":20},"lastName":"hosseini"}',
            $where->get(),
        );
    }
}
