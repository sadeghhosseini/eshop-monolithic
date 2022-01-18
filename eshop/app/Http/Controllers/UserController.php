<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRequest;
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
    #[Get('/users', middleware: ['auth:sanctum', 'permission:view-user-own|view-any-user'])]
    public function getAll(Request $request)
    {
        if ($request->user()->hasPermissionTo('view-any-user')) {
            return response()->json(User::all());
        }

        if ($request->user()->hasPermissionTo('view-user-own')) {
            $currentUser = User::find($request->user()->id);
            return response()->json($currentUser);
        }
    }

    #[Get('/users/{user}', middleware: ['permission:view-user-own|view-any-user'])]
    public function get(Request $request, User $user)
    {
        if (!$request->user()->hasAnyPermission('view-any-user', 'view-user-own')) {
            throw new AuthorizationException();
        }

        if ($request->user()->hasPermissionTo('view-any-user')) {
            return response()->json($user);
        }

        if ($request->user()->hasPermissionTo('view-user-own')) {
            if ($user->id !== $request->user()->id) {//$user is not the current user
                throw new AuthorizationException();
            }
            return response()->json($user);
        }
    }

    #[Patch('/users/{user}')]
    public function update(UpdateUserRequest $request, User $user)
    {
    }
}
