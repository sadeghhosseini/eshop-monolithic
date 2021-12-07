<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Helpers\RelationshipHelper as Helper;
use App\Models\Product;
use App\Models\Property;


class Category extends Model
{
    use HasFactory;

    public function parent() {
        return Helper::oneToMany($this, Category::class, 'parent_id');
    }
    
    public function children() {
        return Helper::oneToManyWithFk($this, Category::class, 'parent_id');
    }

    public function products() {
        return Helper::oneToMany($this, Product::class, 'category_id');
    }

    public function properties() {
        return Helper::oneToMany($this, Property::class, 'category_id');
    }
}
