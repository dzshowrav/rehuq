<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    private function resolveCart(Request $request): Cart
    {
        if ($request->user()) {
            $cart = Cart::firstOrCreate(['user_id' => $request->user()->id]);
            $token = $request->header('X-Session-Token');
            if ($token) {
                $guest = Cart::where('session_token', $token)->whereNull('user_id')->first();
                if ($guest && $guest->id !== $cart->id) {
                    foreach ($guest->items as $item) {
                        $existing = $cart->items()->where('product_id', $item->product_id)->first();
                        $existing ? $existing->update(['qty' => min(99, $existing->qty + $item->qty)]) : $cart->items()->create(['product_id' => $item->product_id, 'qty' => $item->qty]);
                    }
                    $guest->delete();
                }
            }
            return $cart;
        }

        $token = $request->header('X-Session-Token') ?? 'guest-' . md5($request->ip() . $request->userAgent());
        return Cart::firstOrCreate(['session_token' => $token]);
    }

    public function show(Request $request)
    {
        $cart = $this->resolveCart($request)->load('items.product.images', 'coupon');
        return response()->json(['cart' => $this->cartPayload($cart)]);
    }

    public function add(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'qty' => 'required|integer|min:1|max:99',
        ]);

        $product = Product::active()->findOrFail($data['product_id']);
        $cart = $this->resolveCart($request);

        $item = $cart->items()->where('product_id', $product->id)->first();
        if ($item) {
            $item->update(['qty' => min(99, $item->qty + $data['qty'])]);
        } else {
            $cart->items()->create(['product_id' => $product->id, 'qty' => $data['qty']]);
        }

        $cart->load('items.product.images', 'coupon');
        return response()->json(['cart' => $this->cartPayload($cart), 'message' => 'Added to cart.']);
    }

    public function update(Request $request, $itemId)
    {
        $data = $request->validate(['qty' => 'required|integer|min:1|max:99']);
        $cart = $this->resolveCart($request);
        $item = $cart->items()->findOrFail($itemId);
        $item->update(['qty' => $data['qty']]);
        $cart->load('items.product.images', 'coupon');
        return response()->json(['cart' => $this->cartPayload($cart)]);
    }

    public function remove(Request $request, $itemId)
    {
        $cart = $this->resolveCart($request);
        $cart->items()->findOrFail($itemId)->delete();
        $cart->load('items.product.images', 'coupon');
        return response()->json(['cart' => $this->cartPayload($cart)]);
    }

    public function clear(Request $request)
    {
        $cart = $this->resolveCart($request);
        $cart->items()->delete();
        $cart->update(['coupon_id' => null]);
        return response()->json(['cart' => $this->cartPayload($cart)]);
    }

    public function applyCoupon(Request $request)
    {
        $data = $request->validate(['code' => 'required|string|max:40']);
        $cart = $this->resolveCart($request);
        $cart->load('items.product');

        $subtotal = $cart->items->sum(fn ($i) => $i->product?->effective_price * $i->qty ?? 0);

        $coupon = Coupon::whereRaw('UPPER(code) = ?', [strtoupper($data['code'])])->valid($subtotal)->first();

        if (! $coupon) {
            return response()->json(['message' => 'This coupon code is invalid or expired.'], 422);
        }

        $cart->update(['coupon_id' => $coupon->id]);
        $cart->load('items.product.images', 'coupon');
        return response()->json(['cart' => $this->cartPayload($cart), 'message' => 'Coupon applied.']);
    }

    public function removeCoupon(Request $request)
    {
        $cart = $this->resolveCart($request);
        $cart->update(['coupon_id' => null]);
        $cart->load('items.product.images', 'coupon');
        return response()->json(['cart' => $this->cartPayload($cart)]);
    }

    private function cartPayload(Cart $cart): array
    {
        $items = $cart->items->map(fn ($i) => [
            'id' => $i->id,
            'product_id' => $i->product_id,
            'name' => $i->product?->name,
            'slug' => $i->product?->slug,
            'image' => $i->product ? $i->product->cover : null,
            'price' => $i->product?->effective_price ?? 0,
            'compare_at_price' => $i->product?->compare_at_price,
            'stock' => $i->product?->stock ?? 0,
            'qty' => $i->qty,
            'line_total' => round(($i->product?->effective_price ?? 0) * $i->qty, 2),
        ]);

        $subtotal = round($items->sum('line_total'), 2);
        $discount = 0.0;
        if ($cart->coupon) {
            $discount = $cart->coupon->discountFor($subtotal);
        }

        return [
            'id' => $cart->id,
            'items' => $items,
            'count' => $items->sum('qty'),
            'subtotal' => $subtotal,
            'discount' => round($discount, 2),
            'total' => round($subtotal - $discount, 2),
            'coupon' => $cart->coupon ? [
                'code' => $cart->coupon->code,
                'type' => $cart->coupon->type,
                'value' => $cart->coupon->value,
            ] : null,
        ];
    }
}
