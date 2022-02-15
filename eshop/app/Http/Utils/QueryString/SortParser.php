<?php

namespace App\Http\Utils\QueryString;

use App\Helpers;

class SortParser
{

    /**
     * regex: (?>\("([^\(\),]*)",\s*"([^\(\),]*)"\))
     */
    public static function parse($sortString)
    {
        $sortItems = null;
        preg_match_all('/(?>\("([^\(\),]*)",\s*"([^\(\),]*)"\))/', $sortString, $sortItems, PREG_SET_ORDER);

        $result = [];
        if ($sortItems && is_array($sortItems)) {
            foreach ($sortItems as $item) {
                $result[] = array_splice($item, 1, 2);
            }
        }

        return $result;
    }
}
