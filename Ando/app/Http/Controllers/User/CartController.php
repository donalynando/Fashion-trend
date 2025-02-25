<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    public function index()
    {
        try {
            $cart = Cart::where('user_id', Auth::id())
                       ->with(['product' => function($query) {
                           $query->select('id', 'name', 'description', 'price', 'stock', 'image', 'is_active');
                       }])
                       ->get();
            
            $items = $cart->map(function ($item) {
                // Ensure product exists
                if (!$item->product) {
                    return null;
                }

                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'name' => $item->product->name,
                    'description' => $item->product->description,
                    'price' => (float)$item->product->price,
                    'quantity' => (int)$item->quantity,
                    'stock' => (int)$item->product->stock,
                    'image_url' => $item->product->image_url,
                    'available' => $item->product->is_active && $item->product->stock > 0
                ];
            })->filter(); // Remove null values

            return response()->json([
                'success' => true,
                'items' => $items
            ]);
        } catch (\Exception $e) {
            Log::error('Error in cart index:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch cart items',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1'
            ]);

            $product = Product::findOrFail($request->product_id);
            
            if ($product->stock < $request->quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not enough stock available'
                ], 400);
            }

            $existingItem = Cart::where('user_id', Auth::id())
                ->where('product_id', $request->product_id)
                ->first();

            if ($existingItem) {
                $existingItem->quantity += $request->quantity;
                $existingItem->save();
            } else {
                Cart::create([
                    'user_id' => Auth::id(),
                    'product_id' => $request->product_id,
                    'quantity' => $request->quantity
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Product added to cart successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in cart store:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to add product to cart',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'quantity' => 'required|integer|min:1'
            ]);

            $cartItem = Cart::where('user_id', Auth::id())
                ->where('id', $id)
                ->firstOrFail();

            $product = Product::findOrFail($cartItem->product_id);
            
            if ($product->stock < $request->quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not enough stock available'
                ], 400);
            }

            $cartItem->quantity = $request->quantity;
            $cartItem->save();

            return response()->json([
                'success' => true,
                'message' => 'Cart item updated successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating cart item:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update cart item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $cartItem = Cart::where('user_id', Auth::id())
                ->where('id', $id)
                ->firstOrFail();

            $cartItem->delete();

            return response()->json([
                'success' => true,
                'message' => 'Item removed from cart successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in cart destroy:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to remove item from cart',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function count()
    {
        try {
            $count = Cart::where('user_id', Auth::id())->sum('quantity');

            return response()->json([
                'success' => true,
                'count' => $count
            ]);
        } catch (\Exception $e) {
            Log::error('Error in cart count:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch cart count',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function clear()
    {
        try {
            Cart::where('user_id', Auth::id())->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Cart cleared successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error clearing cart:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cart',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
