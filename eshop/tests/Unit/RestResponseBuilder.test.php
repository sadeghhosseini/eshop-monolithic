<?php

use App\Models\Product;
use App\Utils\RestResponse\MetaData\MetaDataFields;
use App\Utils\RestResponse\MetaData\MetaDataPagination;
use App\Utils\RestResponse\MetaData\MetaDataSort;
use App\Utils\RestResponse\MetaData\MetaDataWhere;
use App\Utils\RestResponse\RestResponseBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;

uses(RefreshDatabase::class);

it('tests setData with one model instance  as input', function () {
    $product = Product::factory()->make();
    $response = RestResponseBuilder::create()->setData($product)->get();
    expect(array_key_exists('data', $response))->toBeTrue();
    expect($response['data'])->toMatchArray($product->toArray());
});

it('tests setData with collection of instances as input', function () {
    $product = Product::factory()->count(3)->make();
    $response = RestResponseBuilder::create()->setData($product)->get();
    expect(array_key_exists('data', $response))->toBeTrue();
    expect($response['data'])->toMatchArray($product->toArray());
});

it('tests setMetaData', function () {
    $paginationResult = '{"page":3,"total_pages":20,"size":50,"offset":20}';
    $pagination = Mockery::mock(MetaDataPagination::class, function (MockInterface $mock) use ($paginationResult) {
        $mock->shouldReceive('get')
            ->once()
            ->andReturn($paginationResult);
    });

    $sortResult = '[("asc", "title"),("desc", "quantity")]';
    $sort = Mockery::mock(MetaDataSort::class, function (MockInterface $mock) use ($sortResult) {
        $mock->shouldReceive('get')
            ->once()
            ->andReturn($sortResult);
    });

    $whereResult = '{"age":{"$gt":20},"lastName":"hosseini"}';
    $where = Mockery::mock(MetaDataWhere::class, function (MockInterface $mock) use ($whereResult) {
        $mock->shouldReceive('get')
            ->once()
            ->andReturn($whereResult);
    });

    $fieldsResult = '{"includes":["age","lastName","firstName"],"excludes":["ssn"]}';
    $fields = Mockery::mock(MetaDataFields::class, function (MockInterface $mock) use ($fieldsResult) {
        $mock->shouldReceive('get')
            ->once()
            ->andReturn($fieldsResult);
    });

    $response = RestResponseBuilder::create()
        ->setMetaData(
            pagination: $pagination,
            sort: $sort,
            where: $where,
            fields: $fields,
        )
        ->get();
    expect($response['_metadata']['pagination'])
        ->toContain($paginationResult);
    expect($response['_metadata']['sort'])
        ->toContain($sortResult);
    expect($response['_metadata']['where'])
        ->toContain($whereResult);
    expect($response['_metadata']['fields'])
        ->toContain($fieldsResult);
});
