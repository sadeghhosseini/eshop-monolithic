<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Category;
use App\Models\Product;
use App\Models\Helpers\RelationshipHelper as Helper;

class Property extends Model
{
    public function category() {
        // return $this->belongsTo(Category::class, 'category_id');
        return Helper::oneToManyWithFk($this, Category::class, 'category_id');
    }

    public function products() {
        // return $this->belongsToMany(Product::class, 'products_properties', 'property_id', 'product_id');
        return Helper::manyToMany($this, Product::class, 'products_properties', 'property_id', 'product_id');
    }
}
