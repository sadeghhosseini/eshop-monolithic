<?php


namespace Tests\Images;

use Tests\MyTestCase;


/**
* @testdox PATCH /api/images/{id}
*/
class PatchImagesTest extends MyTestCase
{
    public function getUrl()
    {
        return '/api/images/{id}';
    }

    

}