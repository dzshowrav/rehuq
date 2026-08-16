<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::where('is_admin', false)->pluck('id')->all();
        $titles = ['Excellent!', 'Great value', 'Highly recommend', 'Good quality', 'Exactly as described', 'Love it!', 'Decent for the price', 'Fast delivery, great product'];
        $comments = [
            'Really impressed with the quality. Delivery was fast and packaging was solid.',
            'Better than I expected at this price point. Would buy again.',
            'Works perfectly. The customer support team was also very helpful.',
            'Good product overall. Took a couple of days longer than expected but worth it.',
            'Exactly what I wanted. Fits/works as described in the listing.',
            'Quality is great and the price was fair. Five stars from me.',
            'Solid purchase. Minor quibbles but nothing that matters.',
            'My whole family loves it. Ordering another one soon!',
        ];

        Product::all()->each(function (Product $product) use ($users, $titles, $comments) {
            $count = rand(0, 5);
            if ($count === 0) {
                return;
            }
            $chosen = collect($users)->shuffle()->take($count);
            foreach ($chosen as $userId) {
                Review::firstOrCreate(
                    ['product_id' => $product->id, 'user_id' => $userId],
                    [
                        'rating' => rand(3, 5),
                        'title' => $titles[array_rand($titles)],
                        'comment' => $comments[array_rand($comments)],
                        'is_approved' => true,
                        'created_at' => now()->subDays(rand(1, 60)),
                    ]
                );
            }
            $product->recomputeRating();
        });
    }
}
