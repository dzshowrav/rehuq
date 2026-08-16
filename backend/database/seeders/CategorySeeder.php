<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $tree = [
            ['Mobiles & Tablets', 'smartphone', 'smartphones', [
                'Smartphones', 'Feature Phones', 'Tablets', 'Accessories', 'Power Banks', 'Phone Cases & Covers',
            ]],
            ['Electronics', 'monitor', 'electronics', [
                'Televisions', 'Headphones & Audio', 'Speakers', 'Cameras', 'Gaming', 'Wearables', 'Computer Accessories',
            ]],
            ['Fashion', 'shirt', 'fashion', [
                "Men's Fashion", "Women's Fashion", "Kids' Fashion", 'Shoes', 'Bags & Luggage', 'Watches', 'Jewellery & Accessories',
            ]],
            ['Home & Living', 'sofa', 'home-living', [
                'Furniture', 'Kitchen & Dining', 'Bedding & Bath', 'Home Decor', 'Lighting', 'Cleaning Supplies', 'Storage & Organisation',
            ]],
            ['Beauty & Health', 'sparkles', 'beauty-health', [
                'Skincare', 'Makeup', 'Hair Care', 'Fragrance', 'Personal Care', 'Health & Wellness',
            ]],
            ['Sports & Outdoors', 'dumbbell', 'sports-outdoors', [
                'Fitness Equipment', 'Sports Shoes', 'Cycling', 'Camping & Hiking', 'Team Sports', 'Yoga & Pilates',
            ]],
            ['Automotive', 'car', 'automotive', [
                'Car Accessories', 'Car Care', 'Motorcycle Accessories', 'Tyres & Wheels', 'Oils & Fluids', 'Interior Accessories',
            ]],
            ['Grocery & Pets', 'shopping-basket', 'grocery-pets', [
                'Snacks & Beverages', 'Cooking Essentials', 'Pet Food', 'Pet Accessories', 'Breakfast & Cereals', 'Baby Care',
            ]],
            ['Toys & Kids', 'toy-brick', 'toys-kids', [
                'Action Figures', 'Educational Toys', 'Board Games', 'Outdoor Play', 'Dolls & Playsets', 'Building Sets',
            ]],
            ['Books & Stationery', 'book', 'books-stationery', [
                'Fiction', 'Non-Fiction', 'Kids Books', 'School Supplies', 'Office Supplies', 'Art & Craft',
            ]],
        ];

        $order = 0;
        foreach ($tree as [$parentName, $icon, $slug, $children]) {
            $order++;
            $parent = Category::updateOrCreate(['slug' => $slug], [
                'name' => $parentName,
                'icon' => $icon,
                'description' => "Everything in {$parentName}, all in one place.",
                'sort_order' => $order,
                'is_active' => true,
            ]);

            foreach ($children as $i => $childName) {
                Category::updateOrCreate(['slug' => \Illuminate\Support\Str::slug($childName)], [
                    'parent_id' => $parent->id,
                    'name' => $childName,
                    'sort_order' => $i + 1,
                    'is_active' => true,
                ]);
            }
        }
    }
}
