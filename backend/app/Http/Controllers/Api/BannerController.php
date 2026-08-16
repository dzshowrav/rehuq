<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function index(Request $request)
    {
        $position = $request->input('position');
        $query = Banner::where('is_active', true)->orderBy('sort_order');
        if ($position) {
            $query->where('position', $position);
        }
        return response()->json(['banners' => $query->get()]);
    }
}
