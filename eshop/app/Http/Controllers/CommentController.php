<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Models\Comment;
use App\Models\Product;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Spatie\RouteAttributes\Attributes\Delete;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Patch;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;


#[Prefix('/api')]
class CommentController extends Controller
{
    #[Post('/products/{product}/comments')]
    public function create(CreateCommentRequest $request, Product $product)
    {
        $comment = new Comment();
        $comment->content = $request->content;
        $comment->parent_id ??= $request->parent_id;
        $product->comments()->save($comment);
        return response()->json($product);
    }

    #[Get('/products/{product}/comments')]
    public function getAll($productId)
    {
        $comments = Comment::where('product_id', $productId)->where('parent_id', null)
            ->withCount('replies')
            ->get();
        
        return response()->json($comments);
    }

    #[Get('/comments/{comment}')]
    public function get($id)
    {
        $comment = Comment::where('id', $id)
            ->with('replies', function(HasMany $query) {
                $query->withCount('replies');
            })->get();
        return response()->json($comment);
    }

    #[Delete('/comments/{comment}')]
    public function delete(Comment $comment)
    {
        $comment->delete();
        return response()->json($comment);
    }

    #[Patch('/comments/{comment}')]
    public function update(UpdateCommentRequest $request, Comment $comment)
    {
        $comment->content ??= $request->content;
        $comment->save();
        return response()->json($comment);
    }
}
