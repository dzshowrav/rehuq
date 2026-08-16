<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:190',
            'comment' => 'nullable|string|max:2000',
        ]);

        $user = $request->user();

        $review = Review::updateOrCreate(
            ['product_id' => $data['product_id'], 'user_id' => $user->id],
            ['rating' => $data['rating'], 'title' => $data['title'] ?? null, 'comment' => $data['comment'] ?? null]
        );

        $product = Product::find($data['product_id']);
        $product?->recomputeRating();

        return response()->json(['review' => $review, 'message' => 'Thank you for your review!'], 201);
    }

    public function my(Request $request)
    {
        return response()->json([
            'reviews' => $request->user()->reviews()->with('product')->latest()->get(),
        ]);
    }
}
