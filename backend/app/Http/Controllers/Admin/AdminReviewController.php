<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class AdminReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = Review::with('user:id,name', 'product:id,name');

        if ($request->filled('status')) {
            $query->where('is_approved', $request->input('status') === 'approved');
        }

        $reviews = $query->orderByDesc('id')->paginate(15);

        return response()->json([
            'reviews' => $reviews->map(fn ($r) => [
                'id' => $r->id,
                'user' => $r->user?->name,
                'product' => $r->product?->name,
                'product_id' => $r->product_id,
                'rating' => $r->rating,
                'title' => $r->title,
                'comment' => $r->comment,
                'is_approved' => $r->is_approved,
                'created_at' => $r->created_at?->toDateTimeString(),
            ]),
            'pagination' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'total' => $reviews->total(),
            ],
        ]);
    }

    public function toggle($id)
    {
        $review = Review::findOrFail($id);
        $review->update(['is_approved' => ! $review->is_approved]);
        $review->product?->recomputeRating();
        return response()->json(['message' => 'Updated.', 'is_approved' => $review->is_approved]);
    }

    public function destroy($id)
    {
        $review = Review::findOrFail($id);
        $productId = $review->product_id;
        $review->delete();
        \App\Models\Product::find($productId)?->recomputeRating();
        return response()->json(['message' => 'Review deleted.']);
    }
}
