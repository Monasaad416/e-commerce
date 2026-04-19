<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Events\NewUserRegistered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class RegisterController extends Controller
{
  public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'customer'
        ]);

        DB::beginTransaction();

        try {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'customer'
            ]);

            // Dispatch event to send notification
            NewUserRegistered::dispatch($user, 'cusstomer');

            // Create token without logging in
            $token = $user->createToken('auth_token')->plainTextToken;

            DB::commit();

            return response()->json([
                'message' => __('Registered successfully'),
                'user' => $user,
                'auth_token' => $token,
                'token_type' => 'Bearer',
                'role' => 'customer'
            ], 201);



        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Registration failed', [
                'email' => $request->email,
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => __('Registration failed'),
            ], 500);
        }
    }
}
