<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('user:id,name,email')->withCount('items');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->input('payment_status'));
        }
        if ($request->filled('q')) {
            $q = trim($request->input('q'));
            $query->where(function ($q2) use ($q) {
                $q2->where('order_number', 'like', "%{$q}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%"))
                    ->orWhere('guest_email', 'like', "%{$q}%")
                    ->orWhere('recipient_phone', 'like', "%{$q}%");
            });
        }

        $orders = $query->orderByDesc('id')->paginate(15);

        return response()->json([
            'orders' => $orders->map(fn ($o) => [
                'id' => $o->id,
                'order_number' => $o->order_number,
                'customer' => $o->user?->name ?? 'Guest',
                'email' => $o->user?->email ?? $o->guest_email,
                'status' => $o->status,
                'payment_status' => $o->payment_status,
                'payment_method' => $o->payment_method,
                'total' => $o->total,
                'items_count' => $o->items_count,
                'placed_at' => $o->placed_at?->toIso8601String(),
            ]),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'total' => $orders->total(),
            ],
            'statuses' => Order::STATUSES,
            'payment_statuses' => Order::PAYMENT_STATUSES,
        ]);
    }

    public function show($id)
    {
        $order = Order::with(['user:id,name,email,phone', 'items'])->findOrFail($id);
        return response()->json(['order' => $order]);
    }

    public function updateStatus(Request $request, $id)
    {
        $data = $request->validate([
            'status' => 'required|string|in:' . implode(',', Order::STATUSES),
            'tracking_number' => 'nullable|string|max:120',
        ]);

        $order = Order::findOrFail($id);
        $oldStatus = $order->status;
        $order->update($data);

        // restore stock when cancelled/refunded
        if (in_array($data['status'], ['cancelled', 'refunded']) && ! in_array($oldStatus, ['cancelled', 'refunded'])) {
            foreach ($order->items as $item) {
                if ($item->product_id) {
                    $item->product?->increment('stock', $item->qty);
                    $item->product?->decrement('sold_count', $item->qty);
                }
            }
        }

        return response()->json(['message' => 'Order updated.', 'order' => $order->fresh('items')]);
    }

    public function updatePayment(Request $request, $id)
    {
        $data = $request->validate(['payment_status' => 'required|string|in:' . implode(',', Order::PAYMENT_STATUSES)]);
        $order = Order::findOrFail($id);
        $order->update($data);
        return response()->json(['message' => 'Payment status updated.', 'order' => $order->fresh()]);
    }
}
