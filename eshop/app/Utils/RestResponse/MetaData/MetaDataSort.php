<?php
namespace App\Utils\RestResponse\MetaData;


class MetaDataSort
{
    private $criteria = [];
    private function __construct()
    {
    }

    public static function create()
    {
        return new Self();
    }
    public function get(): string
    {
        $result = [];
        foreach ($this->criteria as $criterion) {
            $result[] = sprintf('("%s", "%s")', array_keys($criterion)[0], array_values($criterion)[0]);
        }
        $result = "[" . implode(",", $result) . "]";
        return $result;
    }

    public function addAsc(string $field)
    {
        $this->criteria[] = [
            'asc' => $field,
        ];
        return $this;
    }

    public function addDesc(string $field)
    {
        $this->criteria[] = [
            'desc' => $field
        ];
        return $this;
    }
}


