<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Helpers\RelationshipHelper as Helper;
use App\Models\Product;

class Comment extends Model
{
    public function product() {
        // return $this->belongsTo(Product::class, 'productId');
        return Helper::oneToManyWithFk($this, Product::class, 'product_id');
    }

    public function replies() {
        // return $this->hasMany(Comment::class, 'parent_id');
        return Helper::oneToMany($this, Comment::class, 'parent_id');
    }

    public function parentComment() {
        // return $this->belongsTo(Comment::class, 'parent_id');
        return Helper::oneToManyWithFk($this, Comment::class, 'parent_id');
    }
}
