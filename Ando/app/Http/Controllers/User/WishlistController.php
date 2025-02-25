<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function index()
    {
        try {
            $wishlistItems = Wishlist::where('user_id', Auth::id())
                ->with(['product' => function($query) {
                    $query->select('id', 'name', 'price', 'image', 'stock');
                }])
                ->get()
                ->map(function ($item) {
                    if (!$item->product) {
                        return null;
                    }
                    return [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                        'price' => $item->product->price,
                        'image_url' => $item->product->image_url,
                        'stock' => $item->product->stock,
                        'added_at' => $item->created_at->format('Y-m-d H:i:s')
                    ];
                })
                ->filter()
                ->values();

            return response()->json([
                'success' => true,
                'items' => $wishlistItems
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in wishlist index: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load wishlist items'
            ], 500);
        }
    }

    public function add(Request $request)
    {
        try {
            $request->validate([
                'product_id' => 'required|exists:products,id'
            ]);

            $exists = Wishlist::where('user_id', Auth::id())
                ->where('product_id', $request->product_id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => true,
                    'message' => 'Product already in wishlist'
                ]);
            }

            $wishlist = Wishlist::create([
                'user_id' => Auth::id(),
                'product_id' => $request->product_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Product added to wishlist'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error adding to wishlist: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to add product to wishlist'
            ], 500);
        }
    }

    public function remove($productId)
    {
        try {
            Wishlist::where('user_id', Auth::id())
                ->where('product_id', $productId)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Product removed from wishlist'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error removing from wishlist: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove product from wishlist'
            ], 500);
        }
    }

    public function count()
    {
        try {
            $count = Wishlist::where('user_id', Auth::id())->count();

            return response()->json([
                'success' => true,
                'count' => $count
            ]);
        } catch (\Exception $e) {
            \Log::error('Error getting wishlist count: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get wishlist count'
            ], 500);
        }
    }
}
