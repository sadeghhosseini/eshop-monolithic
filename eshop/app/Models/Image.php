<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Helpers\RelationshipHelper as Helper;
use App\Models\Product;

class Image extends Model
{
    use HasFactory;

    public function products() {
        return Helper::manyToMany($this, Product::class, 'products_images', 'image_id', 'product_id');
    }
}
