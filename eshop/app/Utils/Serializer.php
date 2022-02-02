<?php
namespace App\Utils;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Serializer
{
    private Model $model;
    private Collection $items;
    private string $relationName;
    private Strategy $strategy;
    
    private function __construct()
    {
    }

    public static function create(?Model $model = null, ?Collection $relation = null, ?string $relationName = 'items')
    {
        $instance = new Self();
        $instance->setModel($model);
        $instance->setRelation($relation, $relationName);
        return $instance;
    }

    public function setModel(Model $model)
    {
        $this->model = $model;
        return $this;
    }

    public function setRelation(Collection $relation, ?string $relationName = 'items')
    {
        $this->items = $relation;
        $this->relationName = $relationName;
        return $this;
    }

    public function selectOnlyPivotStrategy()
    {
        $this->strategy = new OnlyPivotStrategy();
        return $this;
    }
    
    public function selectMergePivotStrategy()
    {
        $this->strategy = new MergePivotStrategy();
        return $this;
    }

    public function serialize(): array
    {
        if (!$this->model) {
            return null;
        }

        if (!$this->items) {
            return $this->model->toArray();
        }

        if(!$this->strategy) {
            return $this->model->toArray();
        }
        $items = $this->strategy->execute($this->items);
        return [
            ...$this->model->withoutRelations()->toArray(),
            $this->relationName => $items,
        ];
    }
}

interface Strategy
{
    function execute(Collection $items);
}

class OnlyPivotStrategy implements Strategy
{
    public function execute(Collection $items)
    {
        return $items->map(function ($item) {
            return $item->pivot->toArray();
        })->toArray();
    }
}

class MergePivotStrategy implements Strategy
{
    public function execute(Collection $items)
    {
        return $items->map(function ($item) {
            return [
                ...$item->toArray(),
                ...$item->pivot->toArray(),
            ];
        })->toArray();
    }
}
