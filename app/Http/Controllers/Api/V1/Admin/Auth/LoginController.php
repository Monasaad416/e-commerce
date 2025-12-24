<?php

namespace App\Http\Controllers\Api\V1\Admin\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\GarageBranch;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
   public function store(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required'
    ]);

    $admin = \App\Models\Admin::where('email', $request->email)->first();

    if (!$admin || !\Illuminate\Support\Facades\Hash::check($request->password, $admin->password)) {
        return response()->json(['message' => 'Incorrect email or password'], 401);
    }

    $token = $admin->createToken('auth_token')->plainTextToken;

    return response()->json([
        'message' => 'Login successful',
        'token' => $token,
        'user' => $admin,
        'role' => 'admin'
    ], 200);
}
}
