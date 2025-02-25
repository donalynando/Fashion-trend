<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;

class AdminController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // Find admin by email
        $admin = Admin::where('email', $request->email)->first();

        // Check if admin exists and password matches
        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        // Create token
        $token = $admin->createToken('admin-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $admin,
            'message' => 'Login successful'
        ]);
    }

    public function verify(Request $request)
    {
        // If we get here, the token is already verified by the auth middleware
        return response()->json([
            'verified' => true,
            'admin' => $request->user()
        ]);
    }

    public function logout(Request $request)
    {
        // Revoke the token that was used to authenticate the current request
        $request->user()->currentAccessToken()->delete();
        
        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    public function getOrders()
    {
        // TODO: Implement orders logic
        return response()->json(['orders' => []]);
    }

    public function getProducts()
    {
        // TODO: Implement products logic
        return response()->json(['products' => []]);
    }

    public function getCustomers()
    {
        // TODO: Implement customers logic
        return response()->json(['customers' => []]);
    }

    public function getRefunds()
    {
        // TODO: Implement refunds logic
        return response()->json(['refunds' => []]);
    }
}