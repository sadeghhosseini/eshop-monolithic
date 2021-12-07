<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Helpers\RelationshipHelper as Helper;
use App\Models\Order;

class Payment extends Model
{
    public function order() {
        return Helper::oneToManyWithFk($this, Order::class, 'order_id');
    }
}
