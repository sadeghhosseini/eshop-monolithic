<?php

namespace App\Http\Controllers;

use App\Helpers;
use App\Http\Requests\CreateCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Product;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Spatie\RouteAttributes\Attributes\Delete;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Patch;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;


#[Prefix('/api')]
#[Middleware('auth:sanctum')]
class CommentController extends Controller
{
    #[Post('/products/{product}/comments', middleware: 'permission:add-comment')]
    public function create(CreateCommentRequest $request, Product $product)
    {
        $comment = new Comment();
        $comment->content = $request->content;
        $comment->parent_id ??= $request->parent_id;
        $comment->commenter_id = $request->user()->id;
        $product->comments()->save($comment);
        // return response()->json($comment->with('commenter')->get());
        return new CommentResource($comment->with('commenter')->first());
    }

    #[Get('/products/{product}/comments')]
    public function getAll($productId)
    {
        $comments = Comment::where('product_id', $productId)->where('parent_id', null)
            ->withCount('replies')
            ->get();

        // return response()->json($comments);
        return CommentResource::collection($comments);
    }

    #[Get('/comments/{comment}')]
    public function get($id)
    {
        $comment = Comment::where('id', $id)
            ->with('replies', function (HasMany $query) {
                $query->withCount('replies');
            })->with('commenter')->first();
        // return response()->json($comment);
        return new CommentResource($comment);
    }

    #[Delete('/comments/{comment}', middleware: ['permission:delete-comment-own|delete-comment-any'])]
    public function delete(Request $request, Comment $comment)
    {
        $ownerId = $request->user()->id;
        $has_deleteCommentAny_permission = $request->user()->hasPermissionTo('delete-comment-any');
        $has_deleteCommentOwn_permission = $request->user()->hasPermissionTo('delete-comment-own');
        $isOwner = $ownerId == $comment->commenter->id;

        #has none of the permissions
        if (!$has_deleteCommentAny_permission && !$has_deleteCommentOwn_permission) {
            throw new AuthorizationException();
        }

        #has delete-comment-own permission but is not the owner of $comment
        if (!$has_deleteCommentAny_permission && $has_deleteCommentOwn_permission && !$isOwner) {
            throw new AuthorizationException();
        }

        $comment->delete();
        // return response()->json($comment);
        return new CommentResource($comment);
    }

    #[Patch('/comments/{comment}', middleware: ['permission:edit-comment-own'])]
    public function update(UpdateCommentRequest $request, Comment $comment)
    {
        $ownerId = $request->user()->id;
        $isOwner = $ownerId == $comment->commenter->id;
        $has_editCommentOwn_permission = $request->user()->hasPermissionTo('edit-comment-own');

        if (!($has_editCommentOwn_permission & $isOwner)) {
            throw new AuthorizationException();
        }
        $comment->content ??= $request->content;
        $comment->save();
        // return response()->json($comment);
        return new CommentResource($comment);
    }
}
