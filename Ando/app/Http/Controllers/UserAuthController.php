<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Password;

class UserAuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'lastname' => 'required|string|max:255',
                'firstname' => 'required|string|max:255',
                'middlename' => 'nullable|string|max:255',
                'gender' => 'required|string|in:male,female,other',
                'age' => 'required|integer|min:1|max:150',
                'email' => 'required|string|email|max:255|unique:users',
                'phone' => 'required|string|max:20',
                'password' => ['required', 'confirmed', Password::defaults()],
            ]);

            $user = User::create([
                'lastname' => $request->lastname,
                'firstname' => $request->firstname,
                'middlename' => $request->middlename,
                'gender' => $request->gender,
                'age' => $request->age,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'role' => 'customer',
                'status' => 'Active'
            ]);

            return response()->json([
                'message' => 'Registration successful',
                'token' => $user->createToken('user-token')->plainTextToken,
                'user' => $user
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred during registration.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $user = User::where('email', $request->email)
                        ->where('role', 'customer')
                        ->first();

            if (!$user) {
                throw ValidationException::withMessages([
                    'email' => ['User not found.'],
                ]);
            }

            if (!Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'password' => ['Invalid password.'],
                ]);
            }

            if ($user->status !== 'Active') {
                throw ValidationException::withMessages([
                    'email' => ['Your account is not active. Please contact support.'],
                ]);
            }

            return response()->json([
                'token' => $user->createToken('user-token')->plainTextToken,
                'user' => $user
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred during login.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return response()->json(['message' => 'Logged out successfully']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred during logout.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function verify(Request $request)
    {
        try {
            return response()->json([
                'user' => $request->user(),
                'verified' => true
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred during verification.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function user(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'message' => 'Unauthenticated'
                ], 401);
            }

            return response()->json([
                'user' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving user details',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
