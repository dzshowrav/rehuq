<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingMethod;
use Illuminate\Http\Request;

class AdminShippingController extends Controller
{
    public function index()
    {
        return response()->json(['methods' => ShippingMethod::orderBy('sort_order')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'description' => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0',
            'estimated_days' => 'nullable|integer|min:1',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);
        $method = ShippingMethod::create($data);
        return response()->json(['message' => 'Shipping method created.', 'method' => $method], 201);
    }

    public function update(Request $request, $id)
    {
        $method = ShippingMethod::findOrFail($id);
        $data = $request->validate([
            'name' => 'sometimes|string|max:120',
            'description' => 'nullable|string|max:500',
            'price' => 'sometimes|numeric|min:0',
            'estimated_days' => 'nullable|integer|min:1',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);
        $method->update($data);
        return response()->json(['message' => 'Shipping method updated.', 'method' => $method]);
    }

    public function destroy($id)
    {
        ShippingMethod::findOrFail($id)->delete();
        return response()->json(['message' => 'Shipping method deleted.']);
    }
}
