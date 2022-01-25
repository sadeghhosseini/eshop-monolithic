<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Helpers\RelationshipHelper as Helper;
use App\Models\Product;
use App\Models\User;

class Cart extends Model
{
    use HasFactory;
    protected $primaryKey = 'customer_id';

    protected $fillable = ['customer_id'];
    
    public function items() {
        return Helper::manyToMany($this, Product::class, 'cart_items', 'cart_id', 'product_id')
            ->withPivot('quantity');
    }

    public function customer() {
        return Helper::oneToOneWithFk($this, User::class, 'customer_id');
    }
}
