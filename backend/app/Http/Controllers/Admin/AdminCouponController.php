<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminCouponController extends Controller
{
    public function index()
    {
        return response()->json(['coupons' => Coupon::orderByDesc('id')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:40|unique:coupons,code',
            'type' => 'required|in:percent,fixed',
            'value' => 'required|numeric|min:0',
            'min_order' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'usage_limit' => 'nullable|integer|min:1',
            'is_active' => 'nullable|boolean',
        ]);
        $data['code'] = Str::upper($data['code']);
        $coupon = Coupon::create($data);
        return response()->json(['message' => 'Coupon created.', 'coupon' => $coupon], 201);
    }

    public function update(Request $request, $id)
    {
        $coupon = Coupon::findOrFail($id);
        $data = $request->validate([
            'code' => 'sometimes|string|max:40|unique:coupons,code,' . $id,
            'type' => 'sometimes|in:percent,fixed',
            'value' => 'sometimes|numeric|min:0',
            'min_order' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'usage_limit' => 'nullable|integer|min:1',
            'used_count' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        if (! empty($data['code'])) {
            $data['code'] = Str::upper($data['code']);
        }
        $coupon->update($data);
        return response()->json(['message' => 'Coupon updated.', 'coupon' => $coupon]);
    }

    public function destroy($id)
    {
        Coupon::findOrFail($id)->delete();
        return response()->json(['message' => 'Coupon deleted.']);
    }
}
