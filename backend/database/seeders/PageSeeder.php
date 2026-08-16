<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            ['About Us', 'about-us', 'about'],
            ['Terms & Conditions', 'terms', 'terms'],
            ['Privacy Policy', 'privacy', 'privacy'],
            ['Shipping Policy', 'shipping-policy', 'shipping'],
            ['Returns & Refunds', 'returns', 'returns'],
            ['FAQ', 'faq', 'faq'],
        ];

        foreach ($pages as [$title, $slug, $key]) {
            Page::updateOrCreate(['slug' => $slug], [
                'title' => $title,
                'content' => $this->content($key),
                'is_active' => true,
            ]);
        }
    }

    private function content(string $key): string
    {
        $body = match ($key) {
            'about' => 'Rehuq started with a simple idea: one store, everything you need. We are a single-vendor e-commerce brand that curates quality products — from smartphones and electronics to fashion, home essentials and groceries — and delivers them to your door at honest prices.\n\nOur promise is simple:\n\n• Genuine products, always\n• Fair, transparent pricing\n• Fast and reliable delivery\n• Human support, 24/7\n\nThank you for shopping with Rehuq.',
            'terms' => 'These terms govern your use of the Rehuq store. By placing an order you agree to our pricing, delivery and returns policies.\n\n1. Orders are confirmed via email/SMS.\n2. Prices are displayed inclusive of applicable taxes.\n3. We may cancel orders affected by stock or pricing errors.\n4. Promotional codes cannot be combined.\n5. Rehuq reserves the right to update these terms at any time.',
            'privacy' => 'Your privacy matters to us. We collect only the information needed to process orders and improve your experience: name, contact details, delivery address and order history.\n\n• Your data is never sold to third parties.\n• Payments are processed securely.\n• You may request deletion of your account at any time by contacting support.',
            'shipping' => 'We deliver across the country.\n\n• Standard Delivery: 3–5 working days.\n• Express Delivery: 1–2 working days.\n• Store Pickup: free, ready within 24 hours.\n• Orders above the free-shipping threshold ship free with Standard Delivery.\n\nYou will receive a tracking number as soon as your order ships.',
            'returns' => 'Changed your mind? No problem.\n\n• You have 7 days from delivery to request a return.\n• Items must be unused, with tags and original packaging.\n• Refunds are issued to your original payment method within 5–7 working days.\n• For damaged or incorrect items, we cover return shipping.',
            'faq' => 'How do I track my order? — Use the order tracking page with your order number.\n\nHow long does delivery take? — Standard 3–5 days, express 1–2 days.\n\nCan I pay on delivery? — Yes, cash on delivery is available nationwide.\n\nHow do I apply a coupon? — Enter the code at checkout; the discount applies instantly.\n\nHow do I return an item? — Request a return from your account within 7 days of delivery.',
        };

        return str_replace('\\n', "\n", $body);
    }
}
