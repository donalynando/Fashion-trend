<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AdminAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $admin = Admin::where('email', $request->email)->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $admin->createToken('admin-token')->plainTextToken;

        // Format the admin data
        $userData = array_merge($admin->toArray(), [
            'displayName' => $admin->firstname . ' ' . $admin->lastname
        ]);

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => $userData,
            'message' => 'Login successful'
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        
        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    public function verify(Request $request)
    {
        if (!$request->user()) {
            return response()->json([
                'authenticated' => false
            ], 401);
        }

        return response()->json([
            'authenticated' => true,
            'user' => $request->user()
        ]);
    }

    public function notifications()
    {
        if (!auth()->check()) {
            return response()->json([
                'message' => 'Unauthenticated'
            ], 401);
        }

        // For now, return an empty array of notifications
        return response()->json([
            'notifications' => []
        ]);
    }
}
