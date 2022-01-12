<?php

use function Pest\Laravel\patch;
use function Tests\helpers\printEndpoint;
use function Tests\helpers\u;

use App\Models\Image;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
$url = '/api/images/{id}';
beforeAll(function() use ($url) {
    printEndpoint('POST', $url);
});


