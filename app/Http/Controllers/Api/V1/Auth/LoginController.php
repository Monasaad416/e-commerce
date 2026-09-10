<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Incorrect email or password'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        // Merge guest cart if client sends X-Cart-Token / cart_token.
        $guestToken = Cart::extractGuestToken($request);
        if ($guestToken) {
            Cart::mergeGuestCartIntoUser($guestToken, $user);
        }

        return response()->json([
            'message' => 'Logged in successfully',
            'token' => $token,
            'user' => $user,
            'name' => $user->name,
            'email' => $user->email,
            'token_type' => 'Bearer',
            'role' => 'user',
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ], 200);
    }
}
