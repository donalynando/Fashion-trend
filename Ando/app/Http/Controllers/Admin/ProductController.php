<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::latest()
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => $product->price,
                    'stock' => $product->stock,
                    'image' => $product->image ? asset('storage/products/' . $product->image) : null,
                    'is_active' => $product->is_active,
                    'status' => $this->getStockStatus($product->stock)
                ];
            });

        return response()->json($products);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'required|in:0,1'
        ]);

        // Handle image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $imagePath = $file->storeAs('', $filename, 'product_images');
        }

        $product = Product::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'image' => $imagePath,
            'is_active' => (bool)$validated['is_active']
        ]);

        return response()->json([
            'message' => 'Product created successfully',
            'product' => array_merge($product->toArray(), [
                'image' => $imagePath ? asset('storage/products/' . $imagePath) : null
            ])
        ], 201);
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'required|in:0,1',
            'remove_image' => 'boolean'
        ]);

        // Handle image upload or removal
        $imagePath = $product->image;
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($product->image && Storage::disk('product_images')->exists($product->image)) {
                Storage::disk('product_images')->delete($product->image);
            }
            
            // Store new image
            $file = $request->file('image');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $imagePath = $file->storeAs('', $filename, 'product_images');
        } elseif ($request->boolean('remove_image')) {
            // Remove existing image if requested
            if ($product->image && Storage::disk('product_images')->exists($product->image)) {
                Storage::disk('product_images')->delete($product->image);
            }
            $imagePath = null;
        }

        $product->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'image' => $imagePath,
            'is_active' => (bool)$validated['is_active']
        ]);

        return response()->json([
            'message' => 'Product updated successfully',
            'product' => array_merge($product->toArray(), [
                'image' => $imagePath ? asset('storage/products/' . $imagePath) : null
            ])
        ]);
    }

    public function destroy(Product $product)
    {
        // Delete the product image if it exists
        if ($product->image && Storage::disk('product_images')->exists($product->image)) {
            Storage::disk('product_images')->delete($product->image);
        }

        $product->delete();
        return response()->json(['message' => 'Product deleted successfully'], 204);
    }

    private function getStockStatus($stock)
    {
        if ($stock <= 0) {
            return 'Out of Stock';
        } elseif ($stock <= 10) {
            return 'Low Stock';
        } else {
            return 'In Stock';
        }
    }
}
