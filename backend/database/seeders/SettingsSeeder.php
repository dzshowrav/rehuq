<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use App\Models\Setting;
use App\Models\ShippingMethod;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $general = [
            'site_name' => 'Rehuq',
            'site_tagline' => 'One store. Everything you need.',
            'logo' => '',
            'currency' => 'PKR',
            'currency_symbol' => 'Rs',
            'tax_rate' => '0',
            'free_shipping_threshold' => '5000',
            'meta_title' => 'Rehuq — Shop Everything Online',
            'meta_description' => 'Rehuq is your one-stop online store for mobiles, electronics, fashion, home & living, beauty and more. Best prices, fast delivery.',
            'meta_keywords' => 'online shopping, ecommerce, electronics, fashion, rehuq',
        ];

        $appearance = [
            'primary_color' => '#f97316',
            'accent_color' => '#7c3aed',
            'hero_title' => 'Mega Sale Days Are Here',
            'hero_subtitle' => 'Up to 70% off on top brands. Free delivery on orders over Rs 5,000.',
            'hero_badge' => 'Limited time offer',
            'hero_image' => 'https://picsum.photos/seed/rehuqhero/1200/500',
        ];

        $announcement = [
            'announcement_enabled' => '1',
            'announcement' => 'Free shipping on orders above Rs 5,000 — plus use code WELCOME10 for 10% off your first order!',
        ];

        $features = [
            'feature_1_icon' => 'truck',
            'feature_1_title' => 'Fast Delivery',
            'feature_1_text' => 'Delivered to your doorstep in 2–5 days across the country.',
            'feature_2_icon' => 'shield',
            'feature_2_title' => 'Secure Payments',
            'feature_2_text' => 'COD, cards and digital wallets — your money is always safe.',
            'feature_3_icon' => 'refresh',
            'feature_3_title' => 'Easy Returns',
            'feature_3_text' => '7-day hassle-free returns with a full refund guarantee.',
            'feature_4_icon' => 'headset',
            'feature_4_title' => '24/7 Support',
            'feature_4_text' => 'Our support heroes are here around the clock, every day.',
        ];

        $contact = [
            'contact_email' => 'support@rehuq.test',
            'contact_phone' => '+92 300 1234567',
            'support_hours' => 'Mon–Sat, 9:00 AM – 9:00 PM',
            'footer_about' => 'Rehuq is a single-vendor e-commerce store delivering quality products at honest prices. From electronics to everyday essentials — everything you love, in one place.',
            'footer_contact_email' => 'hello@rehuq.test',
            'footer_contact_phone' => '+92 300 1234567',
            'footer_address' => 'Suite 12, Innovation Tower, Gulberg III, Lahore, Pakistan',
            'whatsapp_number' => '+92 300 1234567',
            'copyright_text' => '© ' . date('Y') . ' Rehuq. All rights reserved.',
            'facebook_url' => 'https://facebook.com/rehuq',
            'instagram_url' => 'https://instagram.com/rehuq',
            'twitter_url' => 'https://twitter.com/rehuq',
            'youtube_url' => 'https://youtube.com/@rehuq',
        ];

        foreach (['general' => $general, 'appearance' => $appearance, 'announcement' => $announcement, 'features' => $features, 'contact' => $contact] as $group => $items) {
            foreach ($items as $key => $value) {
                Setting::set($key, $value, $group);
            }
        }

        ShippingMethod::updateOrCreate(['name' => 'Standard Delivery'], ['description' => 'Delivered in 3–5 working days to your doorstep.', 'price' => 199, 'estimated_days' => 5, 'sort_order' => 1]);
        ShippingMethod::updateOrCreate(['name' => 'Express Delivery'], ['description' => 'Priority handling, delivered in 1–2 working days.', 'price' => 499, 'estimated_days' => 2, 'sort_order' => 2]);
        ShippingMethod::updateOrCreate(['name' => 'Store Pickup'], ['description' => 'Free pickup from our Gulberg III store — ready in 24 hours.', 'price' => 0, 'estimated_days' => 1, 'sort_order' => 3]);

        PaymentMethod::updateOrCreate(['code' => 'cod'], ['name' => 'Cash on Delivery', 'description' => 'Pay in cash when your order arrives.', 'sort_order' => 1]);
        PaymentMethod::updateOrCreate(['code' => 'card'], ['name' => 'Debit / Credit Card', 'description' => 'Visa, Mastercard and UnionPay accepted. (Demo — no real charge)', 'sort_order' => 2]);
        PaymentMethod::updateOrCreate(['code' => 'wallet'], ['name' => 'Digital Wallet', 'description' => 'Pay with JazzCash, Easypaisa or Google Pay. (Demo)', 'sort_order' => 3]);
        PaymentMethod::updateOrCreate(['code' => 'bank'], ['name' => 'Bank Transfer', 'description' => 'Transfer to our account and upload the receipt. (Demo)', 'sort_order' => 4]);
    }
}
