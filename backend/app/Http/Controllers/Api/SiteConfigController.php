<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\Setting;
use App\Models\ShippingMethod;

class SiteConfigController extends Controller
{
    public function index()
    {
        $settings = Setting::allCached();
        $keys = ['site_name', 'site_tagline', 'logo', 'announcement', 'announcement_enabled',
            'currency', 'currency_symbol', 'tax_rate', 'free_shipping_threshold',
            'primary_color', 'accent_color', 'footer_about', 'footer_contact_email',
            'footer_contact_phone', 'footer_address', 'facebook_url', 'instagram_url',
            'twitter_url', 'youtube_url', 'whatsapp_number', 'copyright_text',
            'contact_email', 'contact_phone', 'support_hours',
            'hero_title', 'hero_subtitle', 'hero_badge', 'hero_image',
            'feature_1_icon', 'feature_1_title', 'feature_1_text',
            'feature_2_icon', 'feature_2_title', 'feature_2_text',
            'feature_3_icon', 'feature_3_title', 'feature_3_text',
            'feature_4_icon', 'feature_4_title', 'feature_4_text',
            'meta_title', 'meta_description', 'meta_keywords',
        ];

        $out = [];
        foreach ($keys as $key) {
            $out[$key] = $settings[$key] ?? null;
        }
        $out['shipping_methods'] = ShippingMethod::where('is_active', true)->orderBy('sort_order')->get();
        $out['payment_methods'] = PaymentMethod::where('is_active', true)->orderBy('sort_order')->get();
        $out['nav_categories'] = null;

        return response()->json(['settings' => $out]);
    }
}
