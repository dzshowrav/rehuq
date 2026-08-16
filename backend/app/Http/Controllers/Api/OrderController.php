<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->with('items')
            ->orderByDesc('id')
            ->paginate(10);

        return response()->json([
            'orders' => $orders->map(fn ($o) => $this->payload($o)),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    public function show(Request $request, $id)
    {
        $order = Order::where('user_id', $request->user()->id)->with('items')->findOrFail($id);
        return response()->json(['order' => $this->payload($order)]);
    }

    public function track(Request $request, $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->with('items')->firstOrFail();
        return response()->json(['order' => $this->payload($order)]);
    }

    public function cancel(Request $request, $id)
    {
        $order = Order::where('user_id', $request->user()->id)->findOrFail($id);

        if (! in_array($order->status, ['pending', 'confirmed'])) {
            return response()->json(['message' => 'This order can no longer be cancelled.'], 422);
        }

        $order->update(['status' => 'cancelled']);

        foreach ($order->items as $item) {
            if ($item->product_id) {
                $item->product?->increment('stock', $item->qty);
                $item->product?->decrement('sold_count', $item->qty);
            }
        }

        return response()->json(['message' => 'Order cancelled.', 'order' => $this->payload($order->load('items'))]);
    }

    private function payload(Order $o): array
    {
        return [
            'id' => $o->id,
            'order_number' => $o->order_number,
            'status' => $o->status,
            'payment_status' => $o->payment_status,
            'payment_method' => $o->payment_method,
            'shipping_method' => $o->shipping_method,
            'subtotal' => $o->subtotal,
            'discount' => $o->discount,
            'shipping_fee' => $o->shipping_fee,
            'tax' => $o->tax,
            'total' => $o->total,
            'tracking_number' => $o->tracking_number,
            'items_count' => $o->items->sum('qty'),
            'items' => $o->items->map(fn ($i) => [
                'id' => $i->id, 'name' => $i->name, 'image' => $i->image,
                'price' => $i->price, 'qty' => $i->qty, 'total' => $i->total,
            ]),
            'placed_at' => $o->placed_at?->toIso8601String(),
        ];
    }
}
