<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Helpers\RelationshipHelper as Helper;
use App\Models\Product;
use App\Models\User;

class Cart extends Model
{
    public function items() {
        return Helper::manyToMany($this, Product::class, 'cart_items', 'customer_id', 'product_id');
    }

    public function customer() {
        return Helper::oneToOneWithFk($this, User::class, 'customer_id');
    }
}
