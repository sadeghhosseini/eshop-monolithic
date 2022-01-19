<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Helpers\RelationshipHelper as Helper;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * @property User commenter
 * @property Product product
 * @property Comment parentComment
 * @property Collection<Comment> replies
 */
class Comment extends Model
{
    use HasFactory;

    public function product() {
        return Helper::oneToManyWithFk($this, Product::class, 'product_id');
    }

    public function replies() {
        return Helper::oneToMany($this, Comment::class, 'parent_id');
    }

    public function parentComment() {
        return Helper::oneToManyWithFk($this, Comment::class, 'parent_id');
    }

    public function commenter() {
        return Helper::OneToManyWithFk($this, User::class, 'commenter_id', 'id');
    }
}
