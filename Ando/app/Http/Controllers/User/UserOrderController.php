<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserOrderController extends Controller
{
    private const SHIPPING_OPTIONS = [
        'standard' => [
            'cost' => 125,
            'description' => 'Delivery within 3-5 business days'
        ],
        'express' => [
            'cost' => 200,
            'description' => 'Next day delivery'
        ],
        'same_day' => [
            'cost' => 350,
            'description' => 'Delivery within 24 hours'
        ]
    ];

    private const PAYMENT_METHODS = [
        'cash-on-delivery' => ['fee' => 0],
        'credit-card' => ['fee' => 50],
        'paypal' => ['fee' => 50],
        'gcash' => ['fee' => 20]
    ];

    public function store(Request $request)
    {
        try {
            Log::info('Order request received:', $request->all());

            $validated = $request->validate([
                'shipping_address' => 'required|array',
                'shipping_address.name' => 'required|string',
                'shipping_address.phone' => 'required|string',
                'shipping_address.street' => 'required|string',
                'shipping_address.barangay' => 'required|string',
                'shipping_address.city' => 'required|string',
                'shipping_address.province' => 'required|string',
                'shipping_address.postal_code' => 'required|string',
                'shipping_option' => 'required|string|in:' . implode(',', array_keys(self::SHIPPING_OPTIONS)),
                'payment_method' => 'required|string|in:' . implode(',', array_keys(self::PAYMENT_METHODS)),
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.price' => 'required|numeric|min:0',
                'voucher_code' => 'nullable|string'
            ]);

            DB::beginTransaction();

            // Calculate totals
            $subtotal = collect($validated['items'])->sum(function ($item) {
                return $item['price'] * $item['quantity'];
            });

            // Get shipping cost
            $shippingCost = $this->getShippingCost($validated['shipping_option']);
            
            // Get payment fee
            $paymentFee = $this->getPaymentFee($validated['payment_method']);

            // Calculate voucher discount
            $voucherDiscount = 0;
            if ($request->voucher_code) {
                // TODO: Implement voucher validation and discount calculation
            }

            // Create order
            $order = Order::create([
                'user_id' => Auth::id(),
                'order_number' => $this->generateOrderNumber(),
                'status' => 'pending',
                'shipping_address' => $validated['shipping_address'],
                'shipping_option' => $validated['shipping_option'],
                'shipping_fee' => $shippingCost,
                'payment_method' => $validated['payment_method'],
                'payment_fee' => $paymentFee,
                'subtotal' => $subtotal,
                'voucher_code' => $validated['voucher_code'],
                'voucher_discount' => $voucherDiscount,
                'total' => $subtotal + $shippingCost + $paymentFee - $voucherDiscount
            ]);

            // Create order items
            foreach ($validated['items'] as $item) {
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['price'] * $item['quantity']
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'order_id' => $order->order_number,
                'message' => 'Order placed successfully'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            Log::error('Order validation error:', [
                'errors' => $e->errors(),
                'data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating order:', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getRecentOrders(Request $request)
    {
        try {
            $query = Order::where('user_id', Auth::id());

            // Apply status filter
            if ($request->status) {
                $query->where('status', $request->status);
            }

            // Apply date filter
            if ($request->date_filter) {
                switch ($request->date_filter) {
                    case 'last_week':
                        $query->where('created_at', '>=', now()->subWeek());
                        break;
                    case 'last_month':
                        $query->where('created_at', '>=', now()->subMonth());
                        break;
                    case 'last_3_months':
                        $query->where('created_at', '>=', now()->subMonths(3));
                        break;
                    case 'last_6_months':
                        $query->where('created_at', '>=', now()->subMonths(6));
                        break;
                    case 'last_year':
                        $query->where('created_at', '>=', now()->subYear());
                        break;
                }
            }

            // Apply search filter
            if ($request->search) {
                $query->where(function ($q) use ($request) {
                    $q->where('order_number', 'like', '%' . $request->search . '%')
                      ->orWhereHas('items.product', function ($q) use ($request) {
                          $q->where('name', 'like', '%' . $request->search . '%');
                      });
                });
            }

            $orders = $query->with(['items.product'])
                          ->orderBy('created_at', 'desc')
                          ->paginate($request->per_page ?? 10);

            $formattedOrders = $orders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'subtotal' => $order->subtotal,
                    'shipping_fee' => $order->shipping_fee,
                    'total' => $order->total,
                    'created_at' => $order->created_at,
                    'items' => $order->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'name' => $item->product->name,
                            'image_url' => $item->product->image_url,
                            'price' => $item->price,
                            'quantity' => $item->quantity
                        ];
                    })
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedOrders,
                'total' => $orders->total(),
                'per_page' => $orders->perPage(),
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage()
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching orders:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch orders: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $order = Order::where('user_id', Auth::id())
                ->with(['items.product'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'shipping_address' => $order->shipping_address,
                    'shipping_option' => $order->shipping_option,
                    'shipping_fee' => $order->shipping_fee,
                    'payment_method' => $order->payment_method,
                    'payment_fee' => $order->payment_fee,
                    'subtotal' => $order->subtotal,
                    'total' => $order->total,
                    'created_at' => $order->created_at,
                    'items' => $order->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'name' => $item->product->name,
                            'image_url' => $item->product->image_url,
                            'price' => $item->price,
                            'quantity' => $item->quantity
                        ];
                    })
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error fetching order:', [
                'error' => $e->getMessage(),
                'order_id' => $id
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch order details'
            ], 500);
        }
    }

    public function cancel($id)
    {
        try {
            $order = Order::where('user_id', Auth::id())
                ->where(function ($query) {
                    $query->where('status', 'pending')
                          ->orWhere('status', 'processing');
                })
                ->findOrFail($id);

            $order->status = 'cancelled';
            $order->save();

            return response()->json([
                'success' => true,
                'message' => 'Order cancelled successfully',
                'data' => [
                    'id' => $order->id,
                    'status' => $order->status
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found or cannot be cancelled'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error cancelling order:', [
                'error' => $e->getMessage(),
                'order_id' => $id
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel order'
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
        return self::SHIPPING_OPTIONS[$option]['cost'] ?? 125;
    }

    private function getPaymentFee($method)
    {
        return self::PAYMENT_METHODS[$method]['fee'] ?? 0;
    }
}
