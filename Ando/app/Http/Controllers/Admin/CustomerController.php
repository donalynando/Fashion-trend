<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = User::where('role', 'customer')
            ->latest()
            ->get()
            ->map(function ($customer) {
                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                    'status' => $customer->status ?? 'Active',
                    'created_at' => $customer->created_at->format('Y-m-d')
                ];
            });

        return response()->json($customers);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'lastname' => 'required|string',
            'firstname' => 'required|string',
            'middlename' => 'nullable|string',
            'gender' => 'required|string|in:male,female,other',
            'age' => 'required|integer|min:0',
            'email' => 'required|string|email|unique:users',
            'phone' => 'required|string',
            'password' => 'required|string|min:8'
        ]);

        $customer = User::create([
            'lastname' => $validated['lastname'],
            'firstname' => $validated['firstname'],
            'middlename' => $validated['middlename'],
            'gender' => $validated['gender'],
            'age' => $validated['age'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'role' => 'customer',
            'status' => 'Active'
        ]);

        return response()->json($customer, 201);
    }

    public function update(Request $request, User $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($customer->id)],
            'phone' => 'required|string|max:20',
            'status' => 'required|string|in:Active,Inactive',
            'password' => 'nullable|string|min:8'
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'status' => $validated['status']
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $customer->update($updateData);

        return response()->json($customer);
    }

    public function destroy(User $customer)
    {
        if ($customer->role !== 'customer') {
            return response()->json(['message' => 'Cannot delete non-customer users'], 403);
        }
        
        $customer->delete();
        return response()->json(null, 204);
    }
}
