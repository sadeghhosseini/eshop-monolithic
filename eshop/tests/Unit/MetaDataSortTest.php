<?php

namespace Tests\Unit;

use App\Utils\RestResponse\MetaData\MetaDataSort;
use Tests\TestCase;

class MetaDataSortTest extends TestCase
{

    public function dataset_testTestMetadatasort()
    {
        return [
            [
                'input' => [
                    ['asc', 'field1'],
                    ['asc', 'field2'],
                    ['desc', 'field3'],
                    ['asc', 'field4'],
                ],
                'output' => '[("asc", "field1"),("asc", "field2"),("desc", "field3"),("asc", "field4")]'
            ],
            [
                'input' => [
                    ['asc', 'title'],
                    ['desc', 'quantity'],
                ],
                'output' => '[("asc", "title"),("desc", "quantity")]'

            ]
        ];
    }
    /**
     * @dataProvider dataset_testTestMetadatasort
     * @testdox test MetaDataSort
     */
    public function testTestMetadatasort($input, $output)
    {
        $sort = MetaDataSort::create();
        foreach ($input as $item) {
            if ($item[0] == 'asc') {
                $sort = $sort->addAsc($item[1]);
            } else {
                $sort = $sort->addDesc($item[1]);
            }
        }
        // expect($sort->get())->toContain($output);
        $this->assertStringContainsString(
            $output,
            $sort->get(),
        );
    }
}
