<?php

namespace Tests\Unit;

use App\Utils\RestResponse\MetaData\MetaDataPagination;
use Tests\TestCase;

class MetaDataPaginationTest extends TestCase
{
    
    /**
    * @testdox tests MetaDataPagination-get method
    */
    public function testTestsMetadatapaginationGetMethod() {
        $pagination = MetaDataPagination::create(3, 20, 50, 20);
/*         expect($pagination->get())
            ->toContain('{"page":3,"total_pages":20,"size":50,"offset":20}'); */
        $this->assertStringContainsString(
            '{"page":3,"total_pages":20,"size":50,"offset":20}',
            $pagination->get(),
        );
    }
}
