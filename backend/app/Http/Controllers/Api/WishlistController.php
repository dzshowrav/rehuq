<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\WishlistItem;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $items = WishlistItem::where('user_id', $request->user()->id)
            ->with('product.images')
            ->latest()
            ->get();

        return response()->json([
            'items' => $items->map(fn ($w) => [
                'id' => $w->id,
                'product' => $w->product ? [
                    'id' => $w->product->id,
                    'name' => $w->product->name,
                    'slug' => $w->product->slug,
                    'price' => $w->product->effective_price,
                    'compare_at_price' => $w->product->compare_at_price,
                    'image' => $w->product->cover,
                    'stock' => $w->product->stock,
                    'rating_avg' => round((float) $w->product->rating_avg, 1),
                ] : null,
            ]),
        ]);
    }

    public function toggle(Request $request)
    {
        $data = $request->validate(['product_id' => 'required|integer|exists:products,id']);
        $user = $request->user();
        $exists = WishlistItem::where('user_id', $user->id)->where('product_id', $data['product_id'])->exists();

        if ($exists) {
            WishlistItem::where('user_id', $user->id)->where('product_id', $data['product_id'])->delete();
            return response()->json(['wishlisted' => false, 'message' => 'Removed from wishlist.']);
        }

        WishlistItem::create(['user_id' => $user->id, 'product_id' => $data['product_id']]);
        return response()->json(['wishlisted' => true, 'message' => 'Added to wishlist.']);
    }

    public function status(Request $request, $productId)
    {
        $exists = WishlistItem::where('user_id', $request->user()->id)->where('product_id', $productId)->exists();
        return response()->json(['wishlisted' => $exists]);
    }

    public function destroy(Request $request, $id)
    {
        WishlistItem::where('user_id', $request->user()->id)->findOrFail($id)->delete();
        return response()->json(['message' => 'Removed.']);
    }
}
