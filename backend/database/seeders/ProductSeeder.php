<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = [
            'smartphones' => [
                ['Nova X5 Pro 5G — 8GB/256GB', 'NovaTech', 89999, 109999, 34, true, true],
                ['Aura S21 — 6.5" HD+, 64GB', 'Aura', 29999, 34999, 60, true, false],
                ['Pixel Mini 12 — Compact 5G Phone', 'Pixel', 45999, 52999, 25, true, false],
                ['Classic Keypad Phone — Dual SIM', 'SimpleTel', 3499, 4499, 120, false, false],
                ['TabMate 10.1" 4GB/128GB Tablet', 'TabMate', 32999, 39999, 18, true, true],
                ['Nova X2 Ultra — 12GB/512GB', 'NovaTech', 129999, 149999, 12, true, false],
                ['Aura S21 Lite — 4GB/64GB', 'Aura', 22999, 26999, 45, false, false],
                ['PowerCore 20000mAh Fast-Charge Power Bank', 'PowerCore', 2999, 3999, 80, true, true],
                ['ClearGuard Silicone Case for Nova X5 Pro', 'ClearGuard', 699, 999, 200, false, false],
                ['Tempered Glass Screen Protector (3-Pack)', 'GlassPro', 499, 799, 300, false, false],
                ['TurboCharge 65W USB-C Wall Charger', 'TurboCharge', 2499, 3499, 90, true, false],
                ['MagLink Wireless Charging Stand', 'MagLink', 3499, 4599, 40, false, false],
            ],
            'electronics' => [
                ['Vision 55" 4K UHD Smart TV', 'Vision', 119999, 149999, 15, true, true],
                ['Vision 43" FHD Smart TV', 'Vision', 64999, 79999, 20, false, false],
                ['AirSound Pro Wireless Earbuds', 'AirSound', 5499, 7999, 150, true, true],
                ['StudioBeat Over-Ear Headphones — ANC', 'StudioBeat', 12999, 17999, 35, true, false],
                ['BoomBox 360° Bluetooth Speaker', 'BoomBox', 8999, 11999, 28, true, true],
                ['SnapShot 48MP Mirrorless Camera Kit', 'SnapShot', 189999, 219999, 6, false, false],
                ['GamePad X Wireless Controller', 'GamePad', 6499, 8499, 55, false, false],
                ['PulseFit Smartwatch — AMOLED, GPS', 'PulseFit', 9999, 13999, 70, true, true],
                ['MechKey RGB Mechanical Keyboard', 'MechKey', 7999, 9999, 40, false, false],
                ['GlideX Wireless Mouse — Silent Click', 'GlideX', 1999, 2799, 110, false, false],
                ['ViewPro 27" QHD Monitor 144Hz', 'ViewPro', 64999, 74999, 14, false, false],
                ['NestCam 360° Smart Home Camera', 'NestCam', 7499, 9499, 33, true, false],
            ],
            'fashion' => [
                ['Classic Oxford Shirt — Cotton, Slim Fit', 'UrbanWear', 2499, 3499, 90, true, false],
                ['Everyday Denim Jeans — Stretch Fit', 'UrbanWear', 3499, 4999, 75, true, true],
                ['Floral Summer Dress — Midi Length', 'Bloom', 3299, 4599, 60, true, true],
                ['TrailRun Sneakers — Lightweight Mesh', 'Stride', 5999, 7999, 50, true, false],
                ['Formal Leather Oxford Shoes', 'StepUp', 7999, 10999, 30, false, false],
                ['Weekender 45L Travel Backpack', 'CarryOn', 4999, 6999, 65, true, true],
                ['ChronoCraft Analog Watch — Stainless', 'ChronoCraft', 8999, 12999, 25, false, false],
                ['Silk Touch Scarf & Hijab Collection', 'Bloom', 999, 1499, 140, false, false],
                ['Kids Hoodie Set — Fleece Lined', 'TinyToes', 1899, 2599, 85, false, false],
                ['Minimalist Leather Crossbody Bag', 'CarryOn', 3999, 5499, 44, true, false],
                ['SportFlex Joggers — 4-Way Stretch', 'Stride', 2999, 3999, 70, false, false],
                ['Golden Drops Earring Set (Gold-Plated)', 'Lumière', 1499, 2499, 95, false, false],
            ],
            'home-living' => [
                ['CloudNine 5ft Queen Mattress — Memory Foam', 'CloudNine', 45999, 59999, 10, true, true],
                ['Scandinavian 3-Seater Sofa — Fabric', 'NordHome', 89999, 119999, 5, true, false],
                ['ChefMaster 12-Piece Non-Stick Cookware Set', 'ChefMaster', 12999, 17999, 22, true, true],
                ['AirFryer 5.5L — Digital Touch, 8 Presets', 'ChefMaster', 24999, 32999, 30, true, true],
                ['Botanic Planter Set (3-Pack) — Ceramic', 'NordHome', 2499, 3499, 60, false, false],
                ['CloudTouch 200TC Cotton Bedding Set — Queen', 'CloudNine', 6999, 9499, 40, true, false],
                ['LumiGlow Smart LED Bulb — RGB, WiFi', 'LumiGlow', 1499, 2199, 130, false, false],
                ['Aroma 1.8L Electric Kettle — Stainless', 'Aroma', 3999, 5499, 70, false, false],
                ['FoldAway 4-Tier Storage Rack', 'NordHome', 5499, 7499, 35, false, false],
                ['BreezePro 16" Pedestal Fan — Remote', 'BreezePro', 8999, 11999, 25, true, true],
                ['MicroFiber Cleaning Cloth (12-Pack)', 'Sparkle', 699, 999, 250, false, false],
                ['EcoBamboo Cutting Board Set (3-Pack)', 'ChefMaster', 1999, 2799, 80, false, false],
            ],
            'beauty-health' => [
                ['GlowDew Vitamin C Face Serum 30ml', 'GlowDew', 1799, 2499, 120, true, true],
                ['HydraBoost Hyaluronic Moisturizer 50ml', 'GlowDew', 1999, 2799, 100, true, false],
                ['Velvet Matte Lipstick — 24H Wear', 'Velvet', 1299, 1799, 150, false, true],
                ['SilkTouch Argan Hair Oil 100ml', 'SilkTouch', 1499, 2099, 110, false, false],
                ['FreshFace Charcoal Face Wash 150ml', 'FreshFace', 899, 1299, 180, false, false],
                ['Eau de Parfum — Amber Nights 50ml', 'Aurelia', 4999, 6999, 45, true, false],
                ['ProGlow Makeup Brush Set (12-Piece)', 'Velvet', 2499, 3499, 60, false, false],
                ['SoothSkin Aloe Vera Gel 250ml', 'SoothSkin', 799, 1099, 200, false, false],
                ['DailyDefense SPF 50+ Sunscreen 60ml', 'GlowDew', 1599, 2199, 90, true, true],
                ['BreatheEasy Essential Oil Diffuser', 'Aroma', 3499, 4599, 38, false, false],
            ],
            'sports-outdoors' => [
                ['IronCore Adjustable Dumbbell Set 20kg', 'IronCore', 15999, 21999, 12, true, true],
                ['FlexMat 6mm Non-Slip Yoga Mat', 'FlexMat', 2499, 3499, 70, true, false],
                ['TrailBlazer 30L Hiking Backpack — Waterproof', 'TrailBlazer', 6999, 9499, 28, true, true],
                ['SpeedRun Running Shoes — Cushioned', 'Stride', 5499, 7499, 55, false, false],
                ['CyclePro Foldable Commuter Bike 26"', 'CyclePro', 54999, 69999, 8, false, false],
                ['HomeGym Resistance Bands Set (5-Level)', 'IronCore', 1899, 2699, 85, false, false],
                ['CamoPro 4-Person Camping Tent', 'TrailBlazer', 14999, 19999, 15, false, false],
                ['SkyHigh Basketball — Size 7, Indoor/Outdoor', 'SkyHigh', 2499, 3499, 50, false, false],
                ['SwiftScooter Foldable Kids Scooter', 'SwiftScooter', 4999, 6999, 22, true, false],
                ['HydraBottle 1L Insulated Steel Bottle', 'HydraBottle', 1999, 2799, 95, false, false],
            ],
            'automotive' => [
                ['DrivePro 1080P Dash Camera', 'DrivePro', 7999, 10999, 30, true, true],
                ['AllWeather Car Cover — XL', 'AutoShield', 4499, 6499, 25, false, false],
                ['MotoGlove Touchscreen Riding Gloves', 'AutoShield', 2499, 3499, 40, false, false],
                ['ShinePro Ceramic Car Wash Kit', 'ShinePro', 2999, 4199, 55, true, false],
                ['SeatComfort Memory Foam Cushion', 'AutoShield', 1999, 2799, 65, false, false],
                ['AirVent 12V Car Vacuum Cleaner', 'DrivePro', 5499, 7499, 33, false, false],
                ['BikeLock Heavy-Duty Chain Lock', 'AutoShield', 1799, 2499, 45, false, false],
                ['LEDPro 9000LM Headlight Bulbs (Pair)', 'LEDPro', 3499, 4999, 38, true, true],
            ],
            'grocery-pets' => [
                ['GourmetNest Roasted Almonds 500g', 'GourmetNest', 1899, 2499, 140, false, false],
                ['BrewMaster Premium Coffee Beans 1kg', 'BrewMaster', 3999, 5499, 75, true, true],
                ['HappyPaws Adult Dog Food 5kg', 'HappyPaws', 5999, 7999, 50, false, false],
                ['OrganicHive Raw Honey 500g', 'OrganicHive', 2499, 3299, 85, true, false],
                ['SnackBox Assorted Trail Mix 1kg', 'GourmetNest', 2199, 2999, 95, false, false],
                ['Purrfect Cat Litter 10L', 'HappyPaws', 2999, 3999, 60, false, false],
                ['TeaTime Masala Chai 500g', 'TeaTime', 1499, 1999, 130, false, false],
                ['PetCo Interactive Cat Toy Set', 'PetCo', 1299, 1899, 70, false, false],
            ],
            'toys-kids' => [
                ['BuilderBlocks 500-Piece Building Set', 'KidJoy', 3499, 4999, 55, true, true],
                ['RoboKit STEM Educational Robot', 'KidJoy', 5499, 7499, 30, true, false],
                ['CuddleSoft Plush Bear — 50cm', 'CuddleSoft', 1499, 2199, 110, false, false],
                ['RaceTrack 1:32 Remote Control Car', 'SpeedToys', 4299, 5999, 45, false, true],
                ['PuzzleWorld 1000-Piece Panorama Puzzle', 'BrainPlay', 1999, 2799, 65, false, false],
                ['ArtStudio 72-Piece Kids Art Set', 'KidJoy', 2499, 3499, 80, false, false],
                ['MindMatch Board Game — Family Edition', 'BrainPlay', 2999, 3999, 40, true, false],
                ['TinyChef Pretend Play Kitchen Set', 'KidJoy', 6499, 8999, 20, false, false],
            ],
            'books-stationery' => [
                ['The Midnight Library — Hardcover', 'PageTurner', 2499, 3299, 60, true, false],
                ['Atomic Habits — Practical Guide Edition', 'PageTurner', 2199, 2899, 75, true, true],
                ['Little Explorer Illustrated Encyclopedia', 'KidReads', 2999, 3999, 40, false, false],
                ['Sakura Gel Pen Set (10-Pack) — 0.5mm', 'WriteWell', 799, 1199, 190, false, false],
                ['NotaBene A5 Dotted Journal — 240 Pages', 'WriteWell', 1499, 2099, 100, true, true],
                ['WaterColor Master 24-Pan Paint Set', 'ArtStudio', 1799, 2499, 55, false, false],
                ['SmartStudy 40-Sheet Exam Pad (10-Pack)', 'WriteWell', 1299, 1799, 150, false, false],
                ['The Design of Everyday Things — Paperback', 'PageTurner', 1999, 2699, 48, false, false],
            ],
        ];

        $flashSaleEndsAt = now()->addDays(2);

        foreach ($catalog as $categorySlug => $products) {
            $category = Category::where('slug', $categorySlug)->first();
            if (! $category) {
                continue;
            }

            foreach ($products as $i => [$name, $brand, $price, $compareAt, $stock, $featured, $flash]) {
                $slug = Str::slug($name);
                if (Product::withTrashed()->where('slug', $slug)->exists()) {
                    continue;
                }

                $flashPrice = $flash ? round($price * 0.7, -2) : null;

                $product = Product::create([
                    'category_id' => $category->id,
                    'name' => $name,
                    'slug' => $slug,
                    'sku' => strtoupper(Str::substr(Str::slug($brand), 0, 4)) . '-' . strtoupper(Str::substr(md5($slug), 0, 6)),
                    'brand' => $brand,
                    'short_description' => "{$name} — quality you can trust, at a price you'll love. Ships fast across the country.",
                    'description' => "## {$name}\n\n**{$brand}** brings you the {$name} with the quality and reliability you expect.\n\n### Key features\n- Premium build quality\n- Backed by a full warranty\n- 7-day easy returns\n- Fast nationwide delivery\n\n### What's in the box\n- 1 × {$name}\n- User manual\n- Warranty card\n\nOrder today and get it delivered to your doorstep in 2–5 working days.",
                    'price' => $price,
                    'compare_at_price' => $compareAt,
                    'cost' => round($price * 0.62),
                    'stock' => $stock,
                    'low_stock_threshold' => 8,
                    'sold_count' => rand(20, 900),
                    'rating_avg' => round(3.6 + (rand(0, 14) / 10), 1),
                    'rating_count' => rand(5, 400),
                    'flash_sale_price' => $flashPrice,
                    'flash_sale_ends_at' => $flash ? $flashSaleEndsAt : null,
                    'is_featured' => $featured,
                    'is_active' => true,
                    'meta_title' => "{$name} | Rehuq",
                    'meta_description' => "Shop {$name} at Rehuq. Best price, fast delivery, easy returns.",
                ]);

                $seeds = [Str::slug($name), Str::slug($name) . '-alt', Str::slug($name) . '-detail'];
                foreach ($seeds as $pos => $seed) {
                    if ($pos === 2 && rand(0, 1) === 0) {
                        continue;
                    }
                    ProductImage::create([
                        'product_id' => $product->id,
                        'url' => "https://picsum.photos/seed/{$seed}/600/600",
                        'position' => $pos,
                    ]);
                }
            }
        }
    }
}
