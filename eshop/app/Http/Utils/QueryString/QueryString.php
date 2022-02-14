<?php

namespace App\Http\Utils\QueryString;

use App\Http\Utils\QueryString\FilterParser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Mix;
use Illuminate\Http\Request;

class QueryString
{
    private function __construct(private Builder $queryBuilder)
    {
    }

    public static function create(Relation|Builder|Collection $input)
    {
        if ($input instanceof Relation) {
            return new Self($input->getQuery());
        }

        if ($input instanceof Builder) {
            return new Self($input);
        }

        if ($input instanceof Collection) { //relatively lower performance because the collection is the result of an executed query and we will be running our own query based on the executed one
            return new Self($input->toQuery());
        }
    }

    public static function createFromRelation(Relation $relation) {
        return new Self($relation->getQuery());
    }

    public static function createFromQueryBuilder(Builder $queryBuilder)
    {
        return new Self($queryBuilder);
    }

    public static function createFromModelClass($ModelClass)
    {
        return new Self($ModelClass::query());
    }

    public function filter(array $onlyFilterBasedOn = []): QueryString
    {
        if (!request()->has('filter')) {
            return $this;
        }


        $filters = FilterParser::deserialize(request()->query('filter'));
        foreach ($filters as $dfilter) {
            if (empty($onlyFilterBasedOn) || in_array($dfilter[0], $onlyFilterBasedOn)) {
                $this->queryBuilder->where(...$dfilter);
            }
        }
        return $this;
    }

    public function paginate()
    {
        if (!request()->has('offset', 'limit')) {
            return $this;
        }
        $this->queryBuilder->offset(request()->query('offset'))
            ->limit(request()->query('limit'));
        return $this;
    }

    public function getQueryBuilder()
    {
        return $this->queryBuilder;
    }

    public function getCollection(): Collection
    {
        return $this->queryBuilder->get();
    }
}
