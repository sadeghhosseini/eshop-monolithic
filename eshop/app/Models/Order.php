<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Helpers\RelationshipHelper as Helper;
use App\User;
use App\Models\Payment;
use App\Models\OrderAddress;

class Order extends Model
{
    public function address() {
        // return $this->hasOne(Address::class, 'address_id');
        return Helper::oneToOne($this, OrderAddress::class, 'order_id');
    }

    public function customer() {
        // return $this->belongsTo(User::class, 'customer_id');
        return Helper::oneToManyWithFk($this, User::class, 'customer_id');
    }

    public function items() {
        // return $this->belongsToMany(Product::class, 'order_items', 'order_id', 'product_id');
        return Helper::manyToMany($this, Product::class, 'order_items', 'order_id', 'product_id');
    }

    public function payment() {
        return Helper::oneToMany($this, Payment::class, 'order_id');
    }
}
