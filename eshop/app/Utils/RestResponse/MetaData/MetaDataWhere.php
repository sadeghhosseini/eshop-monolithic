<?php
namespace App\Utils\RestResponse\MetaData;

class MetaDataWhere
{
    private function __construct(private array $criteria)
    {
    }

    public static function create(array $criteria)
    {
        return new Self($criteria);
    }

    public function get(): string
    {
        return json_encode($this->criteria);
    }
}

