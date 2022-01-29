<?php
namespace App\Utils\RestResponse\MetaData;

class MetaDataFields
{
    private function __construct(
        private ?array $includes = [],
        private ?array $excludes = [],
    ) {
    }

    public static function create(?array $includes = [], ?array $exclude = [])
    {
        return new MetaDataFields($includes, $exclude);
    }

    public function get(): string
    {
        return json_encode([
            'includes' => $this->includes,
            'excludes' => $this->excludes,
        ]);
    }
}

