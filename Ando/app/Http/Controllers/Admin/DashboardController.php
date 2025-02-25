<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            // Get total orders and revenue
            $orderStats = Order::select(
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(total) as total_revenue')
            )->first();

            // Get total customers
            $totalCustomers = User::count();

            // Get total products
            $totalProducts = Product::count();

            // Get recent orders
            $recentOrders = Order::with(['user', 'orderItems.product'])
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($order) {
                    return [
                        'id' => $order->order_number,
                        'customer' => $order->user->name,
                        'product' => $order->orderItems->first()->product->name,
                        'amount' => number_format($order->total, 2),
                        'status' => $order->status,
                        'date' => $order->created_at->format('Y-m-d H:i:s')
                    ];
                });

            // Get month-over-month changes
            $lastMonth = now()->subMonth();
            $lastMonthStats = Order::select(
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(total) as total_revenue')
            )
            ->whereMonth('created_at', $lastMonth->month)
            ->whereYear('created_at', $lastMonth->year)
            ->first();

            $lastMonthCustomers = User::whereMonth('created_at', $lastMonth->month)
                ->whereYear('created_at', $lastMonth->year)
                ->count();

            $lastMonthProducts = Product::whereMonth('created_at', $lastMonth->month)
                ->whereYear('created_at', $lastMonth->year)
                ->count();

            // Calculate percentage changes
            $orderChange = $lastMonthStats->total_orders > 0 
                ? (($orderStats->total_orders - $lastMonthStats->total_orders) / $lastMonthStats->total_orders) * 100 
                : 0;

            $revenueChange = $lastMonthStats->total_revenue > 0 
                ? (($orderStats->total_revenue - $lastMonthStats->total_revenue) / $lastMonthStats->total_revenue) * 100 
                : 0;

            $customerChange = $lastMonthCustomers > 0 
                ? (($totalCustomers - $lastMonthCustomers) / $lastMonthCustomers) * 100 
                : 0;

            $productChange = $lastMonthProducts > 0 
                ? (($totalProducts - $lastMonthProducts) / $lastMonthProducts) * 100 
                : 0;

            return response()->json([
                'statistics' => [
                    'totalOrders' => $orderStats->total_orders,
                    'totalCustomers' => $totalCustomers,
                    'totalRevenue' => number_format($orderStats->total_revenue, 2),
                    'totalProducts' => $totalProducts,
                    'changes' => [
                        'orders' => round($orderChange, 1),
                        'customers' => round($customerChange, 1),
                        'revenue' => round($revenueChange, 1),
                        'products' => round($productChange, 1)
                    ]
                ],
                'recentOrders' => $recentOrders
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch dashboard data: ' . $e->getMessage()
            ], 500);
        }
    }
}
