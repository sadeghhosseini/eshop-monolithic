<?php

namespace App\Http\Utils\QueryString;

use App\Helpers;

class FilterParser
{
    public static function deserialize(string $filter)
    {
        $operatorMap = [
            '$gte' => '>=',
            '$lte' => '<=',
            '$gt' => '>',
            '$lt' => '<',
        ];
        $data = json_decode($filter, true);
        
        $result = [];
        foreach($data as $key => $value) {
            if (Helpers::isAssociativeArray($value)) {
                $result[] = [
                    $key,
                    $operatorMap[array_keys($value)[0]],
                    array_values($value)[0],
                ];
            } else {
                $result[] = [
                    $key,
                    '=',
                    $value,
                ];
            }
        }
        return $result;
    }
}
