<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Order;
use App\Models\Helpers\RelationshipHelper as Helper;

class OrderAddress extends Model
{
    public function order() {
        // return $this->belongsTo(Order::class, 'address_id');
        return Helper::oneToOneWithFk($this, Order::class, 'order_id');
    }
}
