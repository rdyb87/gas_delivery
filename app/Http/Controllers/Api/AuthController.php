<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages(['message' => 'Invalid credentials']);
        }

        /** @var User $user */
        $user = Auth::user();

        if (! $user->is_active) {
            return response()->json(['success' => false, 'message' => 'Account disabled'], 403);
        }

        $token = $user->createToken('driver-mobile', ['driver'])->plainTextToken;

        return response()->json([
            'success'      => true,
            'access_token' => $token,
            'user'         => [
                'id'        => $user->id,
                'username'  => $user->username,
                'role'      => $user->role,
                'full_name' => $user->full_name,
                'email'     => $user->email,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['success' => true, 'message' => 'Logged out']);
    }
}