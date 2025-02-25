<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        try {
            $orders = Order::with(['user', 'orderItems.product'])
                ->latest()
                ->get()
                ->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'customerName' => $order->user->name,
                        'date' => $order->created_at->format('Y-m-d H:i:s'),
                        'items' => $order->orderItems->map(function ($item) {
                            return [
                                'id' => $item->id,
                                'product_name' => $item->product->name,
                                'quantity' => $item->quantity,
                                'price' => number_format($item->price, 2),
                                'subtotal' => number_format($item->subtotal, 2)
                            ];
                        }),
                        'shipping_address' => $order->shipping_address,
                        'shipping_option' => $order->shipping_option,
                        'shipping_fee' => number_format($order->shipping_fee, 2),
                        'payment_method' => $order->payment_method,
                        'payment_fee' => number_format($order->payment_fee, 2),
                        'subtotal' => number_format($order->subtotal, 2),
                        'voucher_code' => $order->voucher_code,
                        'voucher_discount' => number_format($order->voucher_discount, 2),
                        'total' => number_format($order->total, 2),
                        'status' => $order->status,
                        'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                        'updated_at' => $order->updated_at->format('Y-m-d H:i:s')
                    ];
                });

            return response()->json($orders);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to load orders: ' . $e->getMessage()], 500);
        }
    }

    public function show(Order $order)
    {
        try {
            $order->load(['user', 'orderItems.product']);
            
            return response()->json([
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customerName' => $order->user->name,
                'date' => $order->created_at->format('Y-m-d H:i:s'),
                'items' => $order->orderItems->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_name' => $item->product->name,
                        'quantity' => $item->quantity,
                        'price' => number_format($item->price, 2),
                        'subtotal' => number_format($item->subtotal, 2)
                    ];
                }),
                'shipping_address' => $order->shipping_address,
                'shipping_option' => $order->shipping_option,
                'shipping_fee' => number_format($order->shipping_fee, 2),
                'payment_method' => $order->payment_method,
                'payment_fee' => number_format($order->payment_fee, 2),
                'subtotal' => number_format($order->subtotal, 2),
                'voucher_code' => $order->voucher_code,
                'voucher_discount' => number_format($order->voucher_discount, 2),
                'total' => number_format($order->total, 2),
                'status' => $order->status,
                'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $order->updated_at->format('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to load order: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, Order $order)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|string|in:pending,processing,completed,cancelled'
            ]);

            $order->update($validated);
            
            return response()->json([
                'message' => 'Order status updated successfully',
                'order' => $order
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update order: ' . $e->getMessage()], 500);
        }
    }
}
