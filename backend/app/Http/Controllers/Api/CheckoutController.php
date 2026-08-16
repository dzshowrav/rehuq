<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Setting;
use App\Models\ShippingMethod;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    private function resolveCart(Request $request): Cart
    {
        if ($request->user()) {
            return Cart::firstOrCreate(['user_id' => $request->user()->id])->load('items.product', 'coupon');
        }
        $token = $request->header('X-Session-Token') ?? 'guest-' . md5($request->ip() . $request->userAgent());
        return Cart::firstOrCreate(['session_token' => $token])->load('items.product', 'coupon');
    }

    public function summary(Request $request)
    {
        $cart = $this->resolveCart($request);
        $settings = Setting::allCached();
        $taxRate = (float) ($settings['tax_rate'] ?? 0);

        $subtotal = $cart->items->sum(fn ($i) => $i->product?->effective_price * $i->qty ?? 0);
        $discount = $cart->coupon ? $cart->coupon->discountFor($subtotal) : 0;
        $tax = round(($subtotal - $discount) * $taxRate / 100, 2);

        $shippingMethods = ShippingMethod::where('is_active', true)->orderBy('sort_order')->get()->map(fn ($m) => [
            'id' => $m->id, 'name' => $m->name, 'description' => $m->description,
            'price' => $m->price, 'estimated_days' => $m->estimated_days,
            'free' => (float) ($settings['free_shipping_threshold'] ?? 0) > 0 && $subtotal >= (float) ($settings['free_shipping_threshold'] ?? 0),
        ]);

        return response()->json([
            'summary' => [
                'subtotal' => round($subtotal, 2),
                'discount' => round($discount, 2),
                'tax_rate' => $taxRate,
                'tax' => $tax,
                'shipping_methods' => $shippingMethods,
                'payment_methods' => PaymentMethod::where('is_active', true)->orderBy('sort_order')->get(['code', 'name', 'description']),
                'free_shipping_threshold' => (float) ($settings['free_shipping_threshold'] ?? 0),
            ],
        ]);
    }

    public function placeOrder(Request $request)
    {
        $data = $request->validate([
            'shipping_method_id' => 'required|integer|exists:shipping_methods,id',
            'payment_method' => 'required|string|exists:payment_methods,code',
            'address_id' => 'required_without:guest|integer|exists:addresses,id',
            'recipient_name' => 'required_without:address_id|string|max:120',
            'recipient_phone' => 'required_without:address_id|string|max:40',
            'shipping_line1' => 'required_without:address_id|string|max:190',
            'shipping_line2' => 'nullable|string|max:190',
            'shipping_city' => 'required_without:address_id|string|max:90',
            'shipping_state' => 'nullable|string|max:90',
            'shipping_postal_code' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:1000',
        ]);

        $cart = $this->resolveCart($request);
        if ($cart->items->isEmpty()) {
            return response()->json(['message' => 'Your cart is empty.'], 422);
        }

        // verify stock
        foreach ($cart->items as $item) {
            if (! $item->product || ! $item->product->is_active) {
                return response()->json(['message' => "{$item->product?->name} is no longer available."], 422);
            }
            if ($item->qty > $item->product->stock) {
                return response()->json(['message' => "Only {$item->product->stock} left in stock for {$item->product->name}."], 422);
            }
        }

        $settings = Setting::allCached();
        $taxRate = (float) ($settings['tax_rate'] ?? 0);
        $subtotal = $cart->items->sum(fn ($i) => $i->product->effective_price * $i->qty);
        $discount = $cart->coupon ? $cart->coupon->discountFor($subtotal) : 0;
        $shippingMethod = ShippingMethod::findOrFail($data['shipping_method_id']);
        $freeThreshold = (float) ($settings['free_shipping_threshold'] ?? 0);
        $shippingFee = ($freeThreshold > 0 && $subtotal >= $freeThreshold) ? 0 : (float) $shippingMethod->price;
        $tax = round(($subtotal - $discount) * $taxRate / 100, 2);
        $total = round($subtotal - $discount + $shippingFee + $tax, 2);

        $address = null;
        if (! empty($data['address_id']) && $request->user()) {
            $address = Address::where('user_id', $request->user()->id)->findOrFail($data['address_id']);
        }

        $order = Order::create([
            'order_number' => Order::generateNumber(),
            'user_id' => $request->user()?->id,
            'guest_email' => $request->user()?->email ?? $request->input('recipient_email'),
            'status' => 'pending',
            'payment_status' => $data['payment_method'] === 'cod' ? 'unpaid' : 'paid',
            'payment_method' => $data['payment_method'],
            'shipping_method' => $shippingMethod->name,
            'subtotal' => round($subtotal, 2),
            'discount' => round($discount, 2),
            'shipping_fee' => $shippingFee,
            'tax' => $tax,
            'total' => $total,
            'coupon_id' => $cart->coupon_id,
            'coupon_code' => $cart->coupon?->code,
            'shipping_address_id' => $address?->id,
            'recipient_name' => $address?->recipient_name ?? $data['recipient_name'],
            'recipient_phone' => $address?->phone ?? $data['recipient_phone'],
            'shipping_line1' => $address?->line1 ?? $data['shipping_line1'],
            'shipping_line2' => $address?->line2 ?? ($data['shipping_line2'] ?? null),
            'shipping_city' => $address?->city ?? $data['shipping_city'],
            'shipping_state' => $address?->state ?? ($data['shipping_state'] ?? null),
            'shipping_postal_code' => $address?->postal_code ?? ($data['shipping_postal_code'] ?? null),
            'shipping_country' => $address?->country ?? 'Pakistan',
            'notes' => $data['notes'] ?? null,
            'placed_at' => now(),
        ]);

        foreach ($cart->items as $item) {
            $product = $item->product;
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'image' => $product->cover,
                'price' => $product->effective_price,
                'qty' => $item->qty,
                'total' => round($product->effective_price * $item->qty, 2),
            ]);
            $product->decrement('stock', $item->qty);
            $product->increment('sold_count', $item->qty);
        }

        if ($cart->coupon) {
            $cart->coupon->increment('used_count');
        }

        $cart->items()->delete();
        $cart->update(['coupon_id' => null]);

        return response()->json([
            'message' => 'Order placed successfully!',
            'order' => $this->orderPayload($order->load('items')),
        ], 201);
    }

    public function orderPayload(Order $order): array
    {
        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'payment_status' => $order->payment_status,
            'payment_method' => $order->payment_method,
            'shipping_method' => $order->shipping_method,
            'subtotal' => $order->subtotal,
            'discount' => $order->discount,
            'shipping_fee' => $order->shipping_fee,
            'tax' => $order->tax,
            'total' => $order->total,
            'coupon_code' => $order->coupon_code,
            'tracking_number' => $order->tracking_number,
            'recipient' => [
                'name' => $order->recipient_name,
                'phone' => $order->recipient_phone,
                'line1' => $order->shipping_line1,
                'line2' => $order->shipping_line2,
                'city' => $order->shipping_city,
                'state' => $order->shipping_state,
                'postal_code' => $order->shipping_postal_code,
                'country' => $order->shipping_country,
            ],
            'items' => $order->items->map(fn ($i) => [
                'id' => $i->id, 'name' => $i->name, 'image' => $i->image,
                'price' => $i->price, 'qty' => $i->qty, 'total' => $i->total,
            ]),
            'placed_at' => $order->placed_at?->toIso8601String(),
            'created_at' => $order->created_at?->toIso8601String(),
        ];
    }
}
