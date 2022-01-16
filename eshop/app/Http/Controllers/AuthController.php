<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterationRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterationRequest $request)
    {
        $user = new User();
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->assignRole('customer');
        $user->save();
    }

    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'msg' => 'The provided credentials are incorrect.',
            ]);
        }

        return response()->json([
            'pat' => $user->createToken('login')->plainTextToken, //PAT => PersonalAccessToken
        ]);
    }
}
