<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Helpers\RelationshipHelper as Helper;
use App\Models\Category;
use App\Models\Product;

class Property extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'category_id',
    ];
    public function category() {
        return Helper::oneToManyWithFk($this, Category::class, 'category_id');
    }

    public function products() {
        return Helper::manyToMany($this, Product::class, 'products_properties', 'property_id', 'product_id');
    }
}
