<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // For now, return dummy data
        return response()->json([
            'wishlistCount' => 0,
            'cartCount' => 0,
            'recentOrders' => [],
            'recommendedProducts' => [
                [
                    'id' => 1,
                    'name' => 'Sample Product 1',
                    'price' => '999.99',
                    'image' => 'https://via.placeholder.com/200'
                ],
                [
                    'id' => 2,
                    'name' => 'Sample Product 2',
                    'price' => '1299.99',
                    'image' => 'https://via.placeholder.com/200'
                ]
            ]
        ]);
    }
}
