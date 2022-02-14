<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Http\Utils\QueryString\QueryString;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Patch;
use Spatie\RouteAttributes\Attributes\Prefix;

#[Prefix('/api')]
#[Middleware('auth:sanctum')]
class UserController extends Controller
{
    #[Get('/users', middleware: ['permission:view-user-own|view-user-any'])]
    public function getAll(Request $request)
    {
        if ($request->user()->hasPermissionTo('view-user-any')) {
            $users = QueryString::createFromModelClass(User::class)
                ->paginate()
                ->filter(['name', 'email'])
                ->getCollection();
            return UserResource::collection($users);
        }

        if ($request->user()->hasPermissionTo('view-user-own')) {
            $currentUser = User::find($request->user()->id);
            return new UserResource($currentUser);
        }
    }

    #[Get('/users/{user}', middleware: ['permission:view-user-own|view-user-any'])]
    public function get(Request $request, User $user)
    {
        $ownerId = $request->user()->id;
        $has_viewUserOwn_permission = $request->user()->hasPermissionTo('view-user-own');
        $has_viewUserAny_permission = $request->user()->hasPermissionTo('view-user-any');
        $isOwner = $ownerId == $user->id;

        #has none of the permissions
        if (!$has_viewUserAny_permission && !$has_viewUserOwn_permission) {
            throw new AuthorizationException();
        }

        #has only view-user-own permission but is not the owner of $user
        if (!$has_viewUserAny_permission && $has_viewUserOwn_permission && !$isOwner) {
            throw new AuthorizationException();
        }


        // return response()->json($user);
        return new UserResource($user);
    }

    #[Patch('/users/{user}', middleware: ['permission:edit-user(name)-own'])]
    public function update(UpdateUserRequest $request, User $user)
    {
        $isOwner = $request->user()->id == $user->id;
        $has_editUser__name__Own_permission = $request->user()->hasPermissionTo('edit-user(name)-own');

        if (!$has_editUser__name__Own_permission || !$isOwner) {
            throw new AuthorizationException();
        }

        $user->name = $request->name ?? $user->name;
        $user->save();
        // return response()->json($user);
        return new UserResource($user);

    }
}
