<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Product;
use App\Models\Property;
use App\Models\Helpers\RelationshipHelper as Helper;

class Category extends Model
{

    public function parent() {
        // return $this->belongsTo(Category::class, 'parent_id');
        return Helper::oneToMany($this, Category::class, 'parent_id');
    }
    
    public function children() {
        // return $this->hasMany(Category::class, 'parent_id');
        return Helper::oneToManyWithFk($this, Category::class, 'parent_id');
    }

    public function products() {
        // return $this->hasMany(Product::class, 'category_id');
        return Helper::oneToMany($this, Product::class, 'category_id');
    }

    public function properties() {
        // return $this->hasMany(Property::class, 'category_id');
        return Helper::oneToMany($this, Property::class, 'category_id');
    }
}
