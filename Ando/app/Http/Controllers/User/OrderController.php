<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class UserOrderController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'shipping_address' => 'required|array',
            'shipping_option' => 'required|string',
            'payment_method' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'voucher_code' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            // Calculate totals
            $subtotal = collect($request->items)->sum(function ($item) {
                return $item['price'] * $item['quantity'];
            });

            // Get shipping cost
            $shippingCost = $this->getShippingCost($request->shipping_option);
            
            // Get payment fee
            $paymentFee = $this->getPaymentFee($request->payment_method);

            // Calculate voucher discount
            $voucherDiscount = 0;
            if ($request->voucher_code) {
                // TODO: Implement voucher validation and discount calculation
            }

            // Create order
            $order = new Order();
            $order->user_id = Auth::id();
            $order->order_number = $this->generateOrderNumber();
            $order->status = 'pending';
            $order->shipping_address = json_encode($request->shipping_address);
            $order->shipping_option = $request->shipping_option;
            $order->shipping_fee = $shippingCost;
            $order->payment_method = $request->payment_method;
            $order->payment_fee = $paymentFee;
            $order->subtotal = $subtotal;
            $order->voucher_code = $request->voucher_code;
            $order->voucher_discount = $voucherDiscount;
            $order->total = $subtotal + $shippingCost + $paymentFee - $voucherDiscount;
            $order->save();

            // Create order items
            foreach ($request->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['price'] * $item['quantity']
                ]);
            }

            // Clear cart
            $this->clearCart();

            DB::commit();

            return response()->json([
                'success' => true,
                'order_id' => $order->id,
                'message' => 'Order placed successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to place order: ' . $e->getMessage()
            ], 500);
        }
    }

    private function generateOrderNumber()
    {
        $prefix = 'ORD';
        $timestamp = now()->format('YmdHis');
        $random = str_pad(random_int(0, 999), 3, '0', STR_PAD_LEFT);
        return $prefix . $timestamp . $random;
    }

    private function getShippingCost($option)
    {
        $costs = [
            'Standard Local' => 125,
            'Express Delivery' => 200
        ];

        return $costs[$option] ?? 125; // Default to standard shipping if option not found
    }

    private function getPaymentFee($method)
    {
        $fees = [
            'cash-on-delivery' => 0,
            'credit-card' => 50,
            'paypal' => 50,
            'gcash' => 20
        ];

        return $fees[$method] ?? 0; // Default to no fee if method not found
    }

    public function clearCart()
    {
        try {
            Cart::where('user_id', Auth::id())->delete();
            return response()->json(['success' => true, 'message' => 'Cart cleared successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to clear cart'], 500);
        }
    }

    public function getRecentOrders()
    {
        try {
            $orders = Order::where('user_id', Auth::id())
                ->with(['items'])
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get()
                ->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'status' => $order->status,
                        'total' => $order->total,
                        'items_count' => $order->items->count(),
                        'created_at' => $order->created_at
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $orders
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch recent orders',
                'data' => []
            ], 500);
        }
    }
}
