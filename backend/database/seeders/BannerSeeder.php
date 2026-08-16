<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        $banners = [
            ['Mega Tech Week', 'Up to 60% off smartphones, audio & wearables', 'https://picsum.photos/seed/techweek/1200/450', '/category/smartphones', 'home_hero', 1],
            ['Fashion Fest', 'Fresh styles from Rs 499', 'https://picsum.photos/seed/fashionfest/1200/450', '/category/fashion', 'home_hero', 2],
            ['Home Makeover Sale', 'Transform your space — up to 50% off', 'https://picsum.photos/seed/homesale/1200/450', '/category/home-living', 'home_hero', 3],
            ['Flash Sale: Electronics', '48 hours only. Prices drop every hour.', 'https://picsum.photos/seed/flashelec/800/300', '/products?flash_sale=1', 'home_mid', 1],
            ['Beauty Essentials', 'Glow up with up to 40% off', 'https://picsum.photos/seed/beautysale/800/300', '/category/beauty-health', 'home_mid', 2],
            ['Sports Season', 'Gear up — fitness equipment at record lows', 'https://picsum.photos/seed/sportsseason/1200/300', '/category/sports-outdoors', 'home_bottom', 1],
        ];

        foreach ($banners as [$title, $subtitle, $image, $link, $position, $sort]) {
            Banner::updateOrCreate(['title' => $title, 'position' => $position], [
                'subtitle' => $subtitle,
                'image' => $image,
                'link' => $link,
                'sort_order' => $sort,
                'is_active' => true,
            ]);
        }
    }
}
