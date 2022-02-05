<?php

namespace Tests\Unit;

use App\Utils\RestResponse\MetaData\MetaDataFields;
use Tests\TestCase;

class MetaDataFieldsTest extends TestCase
{
    public function dataset_testTestsMetadatafields() {
        return [
            [
                ['age', 'lastName', 'firstName'],
                ['ssn'],
                '{"includes":["age","lastName","firstName"],"excludes":["ssn"]}'
            ],
            [
                ['field1', 'field2', 'field3', 'field4'],
                ['field5', 'field6', 'field7'],
                '{"includes":["field1","field2","field3","field4"],"excludes":["field5","field6","field7"]}'
            ],
            [
                [],
                ['field1'],
                '{"includes":[],"excludes":["field1"]}'
            ],
            [
                ['field1'],
                [],
                '{"includes":["field1"],"excludes":[]}'
            ],
            [
                [],
                [],
                '{"includes":[],"excludes":[]}'
            ],
        ];
    }
    
    /**
    * @dataProvider dataset_testTestsMetadatafields
    * @testdox tests MetaDataFields
    */
    public function testTestsMetadatafields($includes, $excludes, $output) {
        $fields = MetaDataFields::create($includes, $excludes, $output);
        // expect($fields->get())->toContain($output);
        $this->assertStringContainsString(
            $output,
            $fields->get(),
        );
    }
}
