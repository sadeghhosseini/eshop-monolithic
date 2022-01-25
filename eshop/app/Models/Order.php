<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Helpers\RelationshipHelper as Helper;
use App\Models\User;
use App\Models\Payment;
use App\Models\OrderAddress;

class Order extends Model
{
    use HasFactory;

    public const PROCESSING_STATUS = 'processing';
    public const PROCESSED_STATUS = 'not-processed';
    public const SENT_STATUS = 'sent';

    public function address() {
        return Helper::oneToOne($this, OrderAddress::class, 'order_id');
    }

    public function customer() {
        return Helper::oneToManyWithFk($this, User::class, 'customer_id');
    }

    public function items() {
        return Helper::manyToMany($this, Product::class, 'order_items', 'order_id', 'product_id');
    }

    public function payment() {
        return Helper::oneToOne($this, Payment::class, 'order_id');
    }
}

