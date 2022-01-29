<?php
namespace App\Utils\RestResponse;

use App\Utils\RestResponse\MetaData\MetaDataFields;
use App\Utils\RestResponse\MetaData\MetaDataPagination;
use App\Utils\RestResponse\MetaData\MetaDataSort;
use App\Utils\RestResponse\MetaData\MetaDataWhere;

class RestResponseBuilder
{
    private $body;
    private function __construct(
        private ?array $data = [],
        private ?array $metaData = [],
        private ?array $embedded = [],
        private ?array $links = [],
    ) {
    }

    public static function create(
        ?array $data = [],
        ?array $metaData = [],
        ?array $embedded = [],
        ?array $links = []
    ) {
        return new self(
            $data,
            $metaData,
            $embedded,
            $links
        );
    }

    public function setData($data)
    {

        if ($data instanceof \Illuminate\Database\Eloquent\Collection) {
            $this->body['data'] = $data->toArray();
        } else if ($data instanceof \Illuminate\Database\Eloquent\Model) {
            $this->body['data'] = $data->toArray();
        } else {
            $this->body['data'] = $data;
        }

        return $this;
    }

    public function setMetaData(
        MetaDataPagination $pagination = null,
        MetaDataSort $sort = null,
        MetaDataWhere $where = null,
        MetaDataFields $fields = null
    ) {
        $this->body['_metadata'] = [
            ...(!$pagination ? [] : ['pagination' => $pagination->get()]),
            ...(!$sort ? [] : ['sort' => $sort->get()]),
            ...(!$where ? [] : ['where' => $where->get()]),
            ...(!$fields ? [] : ['fields' => $fields->get()]),
        ];

        return $this;
    }

    public function setEmbedded($embedded = null)
    {
        if ($embedded) {
            $this->body['_embedded'] = $embedded;
        }
        return $this;
    }

    public function setLinks($links)
    {
        if ($links) {
            $this->body['_links'] = $links;
        }
        return $this;
    }

    public function get()
    {
        return $this->body;
    }

    public function respond()
    {
        return response()->json($this->get());
    }
}


