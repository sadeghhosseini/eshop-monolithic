<?php
namespace App\Utils\RestResponse\MetaData;


class MetaDataPagination
{
    public function __construct(
        private int $page = 0,
        private int $totalPages = 0,
        private int $size = 0,
        private int $offset = 0,
    ) {
    }

    public static function create(
        int $page = 0,
        int $totalPages = 0,
        int $size = 0,
        int $offset = 0,
    ) {
        return new Self($page, $totalPages, $size, $offset);
    }

    public function get(): string
    {
        return json_encode([
            'page' => $this->page,
            'total_pages' => $this->totalPages,
            'size' => $this->size,
            'offset' => $this->offset,
        ]);
    }
}