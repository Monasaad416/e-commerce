<?php

namespace App\Http\Controllers\Api\V1\Admin\Auth;

use Hash;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Support\Facades\Cache;
use App\Notifications\PasswordResetNotification;

class PasswordController extends Controller
{

   public function sendResetRequest(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:garages,email']);

        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = now()->addMinutes(15);

        // Store in cache
        Cache::put('pw_reset_' . $request->email, [
            'code' => $code,
            'expires_at' => $expiresAt
        ], $expiresAt); // Use the same expiration for cache

        // Send email with code
        $admin = Admin::where('email', $request->email)->first();
        $admin->notify(new PasswordResetNotification($code));

        return response()->json([
            'message' => 'Verification code sent to your email',
            'email' => $request->email,
            'expires_in' => $expiresAt->diffForHumans()
        ]);
    }
    public function verifyResetCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|digits:6'
        ]);

        // Get the cached reset data
        $cachedReset = Cache::get('pw_reset_' . $request->email);

        // Verify the code
        if (
            !$cachedReset ||
            $cachedReset['code'] != $request->code ||
            now()->gt($cachedReset['expires_at'])
        ) {
            return response()->json(['message' => 'Invalid or expired code'], 400);
        }

        // Generate a short-lived token for the frontend
        $frontendToken = Str::random(32);

        // Store this token temporarily (expires in 5 minutes)
        Cache::put('pwd_reset_token:' . $frontendToken, [
            'email' => $request->email,
            'verified_at' => now()
        ], now()->addMinutes(5));

        //clear the verification code from cache
        Cache::forget('pw_reset_' . $request->email);

        return response()->json([
            'message' => 'Code verified',
            'reset_token' => $frontendToken
        ]);
    }




    public function completeReset(Request $request)
    {
        $request->validate([
            'reset_token' => 'required',
            'password' => 'required|confirmed|min:8'
        ]);

        // Verify the frontend token
        $email = Cache::get('pwd_reset_token:' . $request->reset_token);

        if (!$email) {
            return response()->json(['message' => 'Invalid or expired reset session'], 400);
        }

        // Update password
        $garage = Garage::where('email', $email)->first();
        $garage->password = Hash::make($request->password);
        $garage->save();


        DB::table('password_resets')->where('email', $email)->delete();
        Cache::forget('pwd_reset_token:' . $request->reset_token);

        return response()->json(['message' => 'Password reset successfully']);
    }
    
}
