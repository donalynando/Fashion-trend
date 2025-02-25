<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Address;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    public function index()
    {
        $addresses = Address::where('user_id', Auth::id())->get();
        return response()->json([
            'success' => true,
            'addresses' => $addresses
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'street' => 'required|string|max:255',
            'barangay' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'province' => 'required|string|max:255',
            'postal_code' => 'required|string|max:20',
            'is_default' => 'boolean'
        ]);

        // If this is the first address or is_default is true, unset other default addresses
        if ($request->is_default || !Address::where('user_id', Auth::id())->exists()) {
            Address::where('user_id', Auth::id())->update(['is_default' => false]);
        }

        $address = new Address($request->all());
        $address->user_id = Auth::id();
        $address->save();

        return response()->json([
            'success' => true,
            'address' => $address
        ]);
    }

    public function show($id)
    {
        $address = Address::where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'address' => $address
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'street' => 'required|string|max:255',
            'barangay' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'province' => 'required|string|max:255',
            'postal_code' => 'required|string|max:20',
            'is_default' => 'boolean'
        ]);

        $address = Address::where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        // If setting as default, unset other default addresses
        if ($request->is_default) {
            Address::where('user_id', Auth::id())
                ->where('id', '!=', $id)
                ->update(['is_default' => false]);
        }

        $address->update($request->all());

        return response()->json([
            'success' => true,
            'address' => $address
        ]);
    }

    public function destroy($id)
    {
        $address = Address::where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        $address->delete();

        return response()->json([
            'success' => true
        ]);
    }

    public function getDefault()
    {
        $address = Address::where('user_id', Auth::id())
            ->where('is_default', true)
            ->first();

        if (!$address) {
            $address = Address::where('user_id', Auth::id())->first();
        }

        return response()->json([
            'success' => true,
            'address' => $address
        ]);
    }
}
