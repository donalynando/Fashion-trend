<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function index()
    {
        try {
            // Get statistics for current month
            $currentMonth = Carbon::now()->startOfMonth();
            $lastMonth = Carbon::now()->subMonth()->startOfMonth();

            // Current month statistics
            $currentStats = $this->getMonthlyStats($currentMonth);
            
            // Last month statistics for comparison
            $lastStats = $this->getMonthlyStats($lastMonth);

            // Calculate percentage changes
            $changes = [
                'orders' => $this->calculatePercentageChange($lastStats['orders'], $currentStats['orders']),
                'customers' => $this->calculatePercentageChange($lastStats['customers'], $currentStats['customers']),
                'revenue' => $this->calculatePercentageChange($lastStats['revenue'], $currentStats['revenue']),
                'products' => $this->calculatePercentageChange($lastStats['products'], $currentStats['products'])
            ];

            // Get recent orders
            $recentOrders = Order::with(['items.product'])
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get()
                ->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'orderNumber' => $order->order_number,
                        'customerName' => $order->shipping_address['name'] ?? 'N/A',
                        'total' => number_format($order->total, 2),
                        'status' => $order->status,
                        'items' => $order->items->map(function ($item) {
                            return [
                                'productName' => $item->product->name,
                                'quantity' => $item->quantity,
                                'price' => number_format($item->price, 2)
                            ];
                        }),
                        'date' => $order->created_at->format('Y-m-d H:i:s')
                    ];
                });

            return response()->json([
                'success' => true,
                'statistics' => [
                    'totalOrders' => $currentStats['orders'],
                    'totalCustomers' => $currentStats['customers'],
                    'totalRevenue' => number_format($currentStats['revenue'], 2),
                    'totalProducts' => $currentStats['products'],
                    'changes' => $changes
                ],
                'recentOrders' => $recentOrders
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard data: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getMonthlyStats($date)
    {
        return [
            'orders' => Order::whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->count(),
            'customers' => User::whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->count(),
            'revenue' => Order::whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->sum('total'),
            'products' => Product::whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->count()
        ];
    }

    private function calculatePercentageChange($oldValue, $newValue)
    {
        if ($oldValue == 0) {
            return $newValue > 0 ? 100 : 0;
        }
        
        return round((($newValue - $oldValue) / $oldValue) * 100, 1);
    }
}
