<?php

namespace App\Http\Controllers\Api\V1\Admin\Auth;

use App\Events\NewUserRegistered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
  public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins',
            'password' => 'required|string|min:8|confirmed',
        ]);

        DB::beginTransaction();

        try {
            $admin = \App\Models\Admin::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            // Dispatch event to send notification
            NewUserRegistered::dispatch($admin, 'Admin');

            // Create token without logging in
            $token = $admin->createToken('auth_token')->plainTextToken;

            DB::commit();


         
            return response()->json([
                'message' => 'Admin registered successfully',
                'user' => $admin,
                'access_token' => $token,
                'token_type' => 'Bearer',
                'role' => 'admin'
            ], 201);



        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
