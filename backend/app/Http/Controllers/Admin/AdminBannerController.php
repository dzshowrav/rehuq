<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;

class AdminBannerController extends Controller
{
    public function index()
    {
        return response()->json([
            'banners' => Banner::orderBy('position')->orderBy('sort_order')->get(),
            'positions' => ['home_hero', 'home_mid', 'home_bottom', 'category_top'],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'nullable|string|max:190',
            'subtitle' => 'nullable|string|max:190',
            'image' => 'nullable|string|max:500',
            'link' => 'nullable|string|max:500',
            'position' => 'required|in:home_hero,home_mid,home_bottom,category_top',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);
        $banner = Banner::create($data);
        return response()->json(['message' => 'Banner created.', 'banner' => $banner], 201);
    }

    public function update(Request $request, $id)
    {
        $banner = Banner::findOrFail($id);
        $data = $request->validate([
            'title' => 'nullable|string|max:190',
            'subtitle' => 'nullable|string|max:190',
            'image' => 'nullable|string|max:500',
            'link' => 'nullable|string|max:500',
            'position' => 'sometimes|in:home_hero,home_mid,home_bottom,category_top',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);
        $banner->update($data);
        return response()->json(['message' => 'Banner updated.', 'banner' => $banner]);
    }

    public function destroy($id)
    {
        Banner::findOrFail($id)->delete();
        return response()->json(['message' => 'Banner deleted.']);
    }
}
