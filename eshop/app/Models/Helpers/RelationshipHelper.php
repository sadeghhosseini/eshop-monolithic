<?php

namespace App\Models\Helpers;

use Illuminate\Database\Eloquent\Model;

class RelationshipHelper
{
    public static function oneToOneWithFk(Model $model, $associatedModelClass, string $foreignKey = null, string $primaryKeyOfReferencingTable = null): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $model->belongsTo($associatedModelClass, $foreignKey, $primaryKeyOfReferencingTable);
    }

    public static function oneToOne(Model $model, $associatedModelClass, string $foreignKey = null, string $primaryKeyOfReferencingTable = null): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $model->hasOne($associatedModelClass, $foreignKey, $primaryKeyOfReferencingTable);
    }

    public static function OneToManyWithFk(Model $model, $associatedModelClass, string $foreignKey = null, string $primaryKeyOfReferencingTable = null): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $model->belongsTo($associatedModelClass, $foreignKey, $primaryKeyOfReferencingTable);
    }

    public static function oneToMany(Model $model, $associatedModelClass, string $foreignKey = null, string $primaryKeyOfReferencingTable = null): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $model->hasMany($associatedModelClass, $foreignKey, $primaryKeyOfReferencingTable);
    }

    public static function manyToMany(Model $model, $associatedModelClass, string $intermediateTableName = null, string $foreignKeyOfCurrentClassInIntermediateTable = null, string $foreignKeyOfOtherClassInIntermediateTable = null): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $model->belongsToMany($associatedModelClass, $intermediateTableName, $foreignKeyOfCurrentClassInIntermediateTable, $foreignKeyOfOtherClassInIntermediateTable);
    }
}
