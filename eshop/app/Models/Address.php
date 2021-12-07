<?php

namespace App\Models;
use App\User;
use Illuminate\Database\Eloquent\Model;
use App\Models\Helpers\RelationshipHelper as Helper;
class Address extends Model
{
    public function customer() {
        // return $this->belongsTo(User::class, 'customer_id');
        return Helper::oneToManyWithFk($this, User::class, 'customer_id');
    }

}
