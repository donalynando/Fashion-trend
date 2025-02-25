<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::where('is_active', true)
            ->latest()
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => $product->price,
                    'stock' => $product->stock,
                    'image_url' => $product->image_url,
                    'category' => $product->category,
                    'created_at' => $product->created_at
                ];
            });

        return response()->json($products);
    }

    public function show(Product $product)
    {
        if (!$product->is_active) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'price' => $product->price,
            'stock' => $product->stock,
            'image_url' => $product->image_url,
            'category' => $product->category,
            'created_at' => $product->created_at
        ]);
    }
}
