<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserAddressController extends Controller
{
    public function store(Request $request)
    {
        try {
            Log::info('Address data received:', $request->all());
            
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
                'street' => 'required|string|max:255',
                'barangay' => 'required|string|max:100',
                'city' => 'required|string|max:100',
                'province' => 'required|string|max:100',
                'postal_code' => 'required|string|max:10'
            ]);

            $address = UserAddress::updateOrCreate(
                ['user_id' => Auth::id()],
                [
                    'name' => $validated['name'],
                    'phone' => $validated['phone'],
                    'street' => $validated['street'],
                    'barangay' => $validated['barangay'],
                    'city' => $validated['city'],
                    'province' => $validated['province'],
                    'postal_code' => $validated['postal_code'],
                    'is_default' => true
                ]
            );

            return response()->json([
                'success' => true,
                'address' => [
                    'name' => $address->name,
                    'phone' => $address->phone,
                    'street' => $address->street,
                    'barangay' => $address->barangay,
                    'city' => $address->city,
                    'province' => $address->province,
                    'postalCode' => $address->postal_code
                ],
                'message' => 'Address saved successfully'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error:', [
                'errors' => $e->errors(),
                'data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error saving address:', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to save address: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show()
    {
        try {
            $address = UserAddress::where('user_id', Auth::id())
                ->where('is_default', true)
                ->first();

            if (!$address) {
                return response()->json([
                    'success' => true,
                    'address' => null
                ]);
            }

            return response()->json([
                'success' => true,
                'address' => [
                    'name' => $address->name,
                    'phone' => $address->phone,
                    'street' => $address->street,
                    'barangay' => $address->barangay,
                    'city' => $address->city,
                    'province' => $address->province,
                    'postalCode' => $address->postal_code
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching address:', [
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch address: ' . $e->getMessage()
            ], 500);
        }
    }
}
