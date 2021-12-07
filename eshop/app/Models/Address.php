<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Helpers\RelationshipHelper as Helper;
use App\Models\User;

class Address extends Model
{
    use HasFactory;

    public function customer() {
        // return $this->belongsTo(User::class, 'customer_id');
        return Helper::oneToManyWithFk($this, User::class, 'customer_id');
    }
}
