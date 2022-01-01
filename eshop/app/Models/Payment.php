<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Helpers\RelationshipHelper as Helper;
use App\Models\Order;

class Payment extends Model
{
    use HasFactory;

    public function order() {
        return Helper::oneToOneWithFk($this, Order::class, 'order_id');
    }
}
