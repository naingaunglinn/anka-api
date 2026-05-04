<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Http\Resources\AuthUserResource;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::with('tenant')->where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        return response()->json([
            'user'  => (new AuthUserResource($user))->resolve($request),
            'token' => $user->createToken('auth_token')->plainTextToken,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    }

    public function me(Request $request)
    {
        $user = $request->user()->load('tenant');
        return new AuthUserResource($user);
    }

    // Revoke current token and issue a new one — used by the axios refresh interceptor.
    public function refresh(Request $request)
    {
        $user = $request->user();
        $user->currentAccessToken()->delete();
        return response()->json([
            'token' => $user->createToken('auth_token')->plainTextToken,
        ]);
    }
}
