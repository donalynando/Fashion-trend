<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserCartController extends Controller
{
    public function index()
    {
        try {
            $cartItems = Cart::where('user_id', Auth::id())
                ->with(['product' => function($query) {
                    $query->select('id', 'name', 'price', 'stock', 'image')
                        ->where('is_active', true);
                }])
                ->get()
                ->filter(function($item) {
                    return $item->product && $item->product->is_active;
                })
                ->map(function($item) {
                    return [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                        'price' => $item->product->price,
                        'quantity' => min($item->quantity, $item->product->stock), // Ensure quantity doesn't exceed stock
                        'image' => $item->product->image,
                        'stock' => $item->product->stock
                    ];
                });

            return response()->json([
                'success' => true,
                'items' => $cartItems
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching cart items:', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch cart items: ' . $e->getMessage()
            ], 500);
        }
    }

    public function clear()
    {
        try {
            Log::info('Clearing cart for user:', ['user_id' => Auth::id()]);
            
            Cart::where('user_id', Auth::id())->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Cart cleared successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error clearing cart:', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cart: ' . $e->getMessage()
            ], 500);
        }
    }
}
