<?php

use function Pest\Laravel\post;
use function Tests\helpers\printEndpoint;

use App\Models\Image;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);
$url = '/api/images';
beforeAll(function () use ($url) {
    printEndpoint('POST', $url);
});


it('uploads images and saves images path in db', function () use ($url) {
    Storage::fake('local');

    $data = [
        [
            'path' => '',
            'file' => UploadedFile::fake()->create('image-1.jpg', 400),
        ],
        [
            'path' => '',
            'file' => UploadedFile::fake()->create('image-2.jpg', 400),
        ],
        [
            'path' => '/fun',
            'file' => UploadedFile::fake()->create('image-3.jpg', 400),
        ],
    ];

    $response = post($url, [
        'images' => $data,
    ]);

    $response->assertOk();

    //check existing of the uploaded files on server
    Storage::disk('local')->assertExists('images/' . $data[0]['file']->hashName());
    Storage::disk('local')->assertExists('images/' . $data[1]['file']->hashName());
    Storage::disk('local')->assertExists('images/fun/' . $data[2]['file']->hashName());

    //check file paths on db
    $images = Image::whereIn('path', [
        'images/' . $data[0]['file']->hashName(),
        'images/' . $data[1]['file']->hashName(),
        'images/fun/' . $data[2]['file']->hashName(),
    ])->get()->toArray();

    expect($images)->not()->toBeNull();
    expect($images)->toHaveCount(3);
    Storage::fake('local');
});

it('returns 400 if images size is more than the valid image size', function () use ($url) {
    Storage::fake('local');
    $response = post($url, [
        'images' => [
            [
                'path' => '',
                'file' => UploadedFile::fake()->create('image.jpg', 600),
            ]
        ],
    ]);
    $response->assertStatus(400);
});

it('returns 400 if uploaded file is anything other than jpg|png', function () use ($url) {
    Storage::fake('local');
    $response = post($url, [
        'images' => [
            [
                'path' => '',
                'file' => UploadedFile::fake()->create('image.gif', 300),
            ]
        ]
    ]);
    $response->assertStatus(400);
});
