<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Refund;
use Illuminate\Http\Request;

class RefundController extends Controller
{
    public function index()
    {
        $refunds = Refund::with(['order', 'user'])
            ->latest()
            ->get()
            ->map(function ($refund) {
                return [
                    'id' => $refund->id,
                    'order_id' => $refund->order_id,
                    'order_number' => $refund->order->order_number,
                    'customer_name' => $refund->user->name,
                    'amount' => $refund->amount,
                    'status' => $refund->status,
                    'reason' => $refund->reason,
                    'admin_notes' => $refund->admin_notes,
                    'created_at' => $refund->created_at->format('Y-m-d H:i:s')
                ];
            });

        return response()->json($refunds);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0',
            'reason' => 'required|string',
            'status' => 'required|in:Pending,Approved,Rejected',
            'admin_notes' => 'nullable|string'
        ]);

        $refund = Refund::create($validated);
        $refund->load(['order', 'user']);

        return response()->json([
            'id' => $refund->id,
            'order_id' => $refund->order_id,
            'order_number' => $refund->order->order_number,
            'customer_name' => $refund->user->name,
            'amount' => $refund->amount,
            'status' => $refund->status,
            'reason' => $refund->reason,
            'admin_notes' => $refund->admin_notes,
            'created_at' => $refund->created_at->format('Y-m-d H:i:s')
        ], 201);
    }

    public function update(Request $request, Refund $refund)
    {
        $validated = $request->validate([
            'status' => 'required|in:Pending,Approved,Rejected',
            'admin_notes' => 'nullable|string'
        ]);

        $refund->update($validated);
        $refund->load(['order', 'user']);

        return response()->json([
            'id' => $refund->id,
            'order_id' => $refund->order_id,
            'order_number' => $refund->order->order_number,
            'customer_name' => $refund->user->name,
            'amount' => $refund->amount,
            'status' => $refund->status,
            'reason' => $refund->reason,
            'admin_notes' => $refund->admin_notes,
            'created_at' => $refund->created_at->format('Y-m-d H:i:s')
        ]);
    }

    public function destroy(Refund $refund)
    {
        $refund->delete();
        return response()->json(null, 204);
    }
}
