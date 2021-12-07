<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Helpers\RelationshipHelper as Helper;
use App\Models\Category;
use App\Models\Property;
use App\Models\Image;
use App\Models\Cart;

class Product extends Model
{
    use HasFactory;

    public function category() {
        return Helper::oneToManyWithFk($this, Category::class, 'category_id');
    }

    public function properties() {
        return Helper::manyToMany($this, Property::class, 'products_properties', 'product_id', 'property_id');
    }

    public function orders() {
        return Helper::manyToMany($this, Order::class, 'order_items', 'product_id', 'order_id');
    }

    public function images() {
        return Helper::manyToMany($this, Image::class, 'products_images', 'product_id', 'image_id');
    }

    public function carts() {
        return Helper::manyToMany($this, Cart::class, 'cart_items', 'product_id', 'customer_id');
    }

    public function comments() {
        return Helper::oneToMany($this, Comment::class, 'product_id');
    }
}
