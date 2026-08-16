<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class AdminSettingController extends Controller
{
    public function index()
    {
        $settings = Setting::allCached();

        $groups = [
            'general' => ['site_name', 'site_tagline', 'logo', 'currency', 'currency_symbol', 'tax_rate', 'free_shipping_threshold', 'meta_title', 'meta_description', 'meta_keywords'],
            'appearance' => ['primary_color', 'accent_color', 'hero_title', 'hero_subtitle', 'hero_badge', 'hero_image'],
            'announcement' => ['announcement_enabled', 'announcement'],
            'features' => ['feature_1_icon', 'feature_1_title', 'feature_1_text', 'feature_2_icon', 'feature_2_title', 'feature_2_text', 'feature_3_icon', 'feature_3_title', 'feature_3_text', 'feature_4_icon', 'feature_4_title', 'feature_4_text'],
            'contact' => ['contact_email', 'contact_phone', 'support_hours', 'footer_about', 'footer_contact_email', 'footer_contact_phone', 'footer_address', 'whatsapp_number', 'copyright_text', 'facebook_url', 'instagram_url', 'twitter_url', 'youtube_url'],
        ];

        $out = [];
        foreach ($groups as $group => $keys) {
            foreach ($keys as $key) {
                $out[$group][$key] = $settings[$key] ?? null;
            }
        }

        return response()->json(['settings' => $out]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'settings' => 'required|array',
            'group' => 'sometimes|string',
        ]);

        $group = $request->input('group', 'general');
        $count = 0;
        foreach ($request->input('settings') as $key => $value) {
            Setting::set($key, $value, $group);
            $count++;
        }

        return response()->json(['message' => "{$count} setting(s) saved."]);
    }
}
