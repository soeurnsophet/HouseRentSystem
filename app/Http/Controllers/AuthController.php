<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Normalize phone number by removing non-digit characters
    function normalizePhone($phone)
    {
        return preg_replace('/\D/', '', $phone);
    }
    // Login process
    public function login(Request $request)
    {
        $credentials =  $request->validate([
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials['phone'] = $this->normalizePhone($credentials['phone']);

        $user = User::where('phone', $credentials['phone'])
            ->where('disabled', false)
            ->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'message' => 'Login successful',
            'access_token' => $token
        ]);
    }

    // Logout process
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout successful']);
    }

    // register process

}
