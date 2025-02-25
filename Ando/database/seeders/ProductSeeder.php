<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();
        
        // Categories with their respective products
        $products = [
            // Jackets
            [
                'name' => 'Classic Leather Jacket',
                'description' => 'Premium leather jacket with modern design',
                'price' => 599.00,
                'stock' => 50,
                'image' => '/img/12.png',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Denim Jacket',
                'description' => 'Stylish denim jacket perfect for casual wear',
                'price' => 599.00,
                'stock' => 50,
                'image' => '/img/13.png',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Bomber Jacket',
                'description' => 'Classic bomber jacket with modern touches',
                'price' => 599.00,
                'stock' => 50,
                'image' => '/img/14.png',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Winter Parka',
                'description' => 'Warm and stylish winter parka',
                'price' => 599.00,
                'stock' => 50,
                'image' => '/img/15.png',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Windbreaker Jacket',
                'description' => 'Lightweight windbreaker for active lifestyle',
                'price' => 599.00,
                'stock' => 50,
                'image' => '/img/16.png',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Shirts/Sweatshirts
            [
                'name' => 'Classic White T-Shirt',
                'description' => 'Essential white t-shirt for everyday wear',
                'price' => 49.99,
                'stock' => 100,
                'image' => '/img/17.png',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Graphic Print Sweatshirt',
                'description' => 'Comfortable sweatshirt with modern graphic design',
                'price' => 49.99,
                'stock' => 100,
                'image' => '/img/18.png',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Polo Shirt',
                'description' => 'Classic polo shirt for a smart casual look',
                'price' => 49.99,
                'stock' => 100,
                'image' => '/img/19.png',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Long Sleeve T-Shirt',
                'description' => 'Comfortable long sleeve t-shirt',
                'price' => 49.99,
                'stock' => 100,
                'image' => '/img/20.png',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Hoodie Sweatshirt',
                'description' => 'Cozy hoodie for casual comfort',
                'price' => 49.99,
                'stock' => 100,
                'image' => '/img/21.png',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Sandals/Shoes
            [
                'name' => 'Classic Sneakers',
                'description' => 'Versatile sneakers for everyday wear',
                'price' => 299.00,
                'stock' => 50,
                'image' => '/img/22.png',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Leather Sandals',
                'description' => 'Comfortable leather sandals',
                'price' => 299.00,
                'stock' => 50,
                'image' => '/img/23.png',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Running Shoes',
                'description' => 'High-performance running shoes',
                'price' => 299.00,
                'stock' => 50,
                'image' => '/img/24.png',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Casual Loafers',
                'description' => 'Stylish and comfortable loafers',
                'price' => 299.00,
                'stock' => 50,
                'image' => '/img/25.png',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Sport Sandals',
                'description' => 'Durable sport sandals for active lifestyle',
                'price' => 299.00,
                'stock' => 50,
                'image' => '/img/26.png',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Pants
            [
                'name' => 'Classic Jeans',
                'description' => 'Timeless denim jeans',
                'price' => 499.00,
                'stock' => 75,
                'image' => '/img/27.png',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Chino Pants',
                'description' => 'Versatile chino pants for any occasion',
                'price' => 499.00,
                'stock' => 75,
                'image' => '/img/28.png',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Cargo Pants',
                'description' => 'Functional cargo pants with multiple pockets',
                'price' => 499.00,
                'stock' => 75,
                'image' => '/img/29.png',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Slim Fit Trousers',
                'description' => 'Modern slim fit dress trousers',
                'price' => 499.00,
                'stock' => 75,
                'image' => '/img/30.png',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Track Pants',
                'description' => 'Comfortable track pants for sports and leisure',
                'price' => 499.00,
                'stock' => 75,
                'image' => '/img/31.png',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // Insert all products
        DB::table('products')->insert($products);
    }
}
