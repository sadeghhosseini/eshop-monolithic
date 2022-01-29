
<?php

use App\Models\Product;
use App\Utils\RestResponse\MetaData\MetaDataFields;
use App\Utils\RestResponse\MetaData\MetaDataPagination;
use App\Utils\RestResponse\MetaData\MetaDataSort;
use App\Utils\RestResponse\MetaData\MetaDataWhere;
use App\Utils\RestResponseBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/* it('tests setMetaData', function () {
    $pagination = MetaDataPagination::create(1, 20, 50, 20);
    $sort = MetaDataSort::create()->addAsc('title')->addDesc('quantity');
    $where = MetaDataWhere::create([
        "age" => ['$gt' => 20], //mongodb-style query
        "lastName" => 'hosseini',
    ]);
    $fields = MetaDataFields::create(
        includes: ['age', 'lastName', 'firstName'],
        exclude: ['ssn'],
    );
    $response = RestResponseBuilder::create()
        ->setMetaData(
            pagination: $pagination,
            sort: $sort,
            where: $where,
            fields: $fields,
        )
        ->get();

    
}); */

it('tests MetaDataPagination-get method', function () {
    $pagination = MetaDataPagination::create(3, 20, 50, 20);
    expect($pagination->get())
        ->toContain('{"page":3,"total_pages":20,"size":50,"offset":20}');
});


it('test MetaDataSort', function ($input, $output) {
    $sort = MetaDataSort::create();
    foreach ($input as $item) {
        if ($item[0] == 'asc') {
            $sort = $sort->addAsc($item[1]);
        } else {
            $sort = $sort->addDesc($item[1]);
        }
    }
    expect($sort->get())->toContain($output);
})->with([
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
]);

it('tests MetaDataWhere', function () {
    $where = MetaDataWhere::create([
        "age" => ['$gt' => 20], //mongodb-style query
        "lastName" => 'hosseini',
    ]);
    expect($where->get())->toContain('{"age":{"$gt":20},"lastName":"hosseini"}');
});

it('tests MetaDataFields', function($includes, $excludes, $output) {
    $fields = MetaDataFields::create($includes, $excludes, $output);
    expect($fields->get())->toContain($output);
})->with([
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
]);