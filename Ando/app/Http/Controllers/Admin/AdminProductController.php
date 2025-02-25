<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AdminProductController extends Controller
{
    public function index()
    {
        try {
            $products = Product::orderBy('created_at', 'desc')->get();
            return response()->json($products);
        } catch (\Exception $e) {
            Log::error('Error fetching products:', [
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'message' => 'Failed to fetch products'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'price' => 'required|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'image' => 'required|string',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $product = Product::create([
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'stock' => $request->stock,
                'image' => $request->image,
                'is_active' => $request->is_active ?? true
            ]);

            return response()->json([
                'message' => 'Product created successfully',
                'product' => $product
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating product:', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);
            return response()->json([
                'message' => 'Failed to create product'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $product = Product::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'price' => 'required|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'image' => 'required|string',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $product->update([
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'stock' => $request->stock,
                'image' => $request->image,
                'is_active' => $request->is_active ?? $product->is_active
            ]);

            return response()->json([
                'message' => 'Product updated successfully',
                'product' => $product
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating product:', [
                'error' => $e->getMessage(),
                'product_id' => $id,
                'data' => $request->all()
            ]);
            return response()->json([
                'message' => 'Failed to update product'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);
            $product->delete();

            return response()->json([
                'message' => 'Product deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting product:', [
                'error' => $e->getMessage(),
                'product_id' => $id
            ]);
            return response()->json([
                'message' => 'Failed to delete product'
            ], 500);
        }
    }
}
