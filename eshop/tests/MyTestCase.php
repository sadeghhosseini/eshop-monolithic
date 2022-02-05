<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class MyTestCase extends BaseTestCase
{
    use CreatesApplication, RefreshDatabase, AddsHelpers, AssertHelpers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupAuthorization();
    }
}
