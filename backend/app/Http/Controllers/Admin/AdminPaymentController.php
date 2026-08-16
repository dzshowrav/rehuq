<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class AdminPaymentController extends Controller
{
    public function index()
    {
        return response()->json(['methods' => PaymentMethod::orderBy('sort_order')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:40|unique:payment_methods,code',
            'name' => 'required|string|max:120',
            'description' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);
        $method = PaymentMethod::create($data);
        return response()->json(['message' => 'Payment method created.', 'method' => $method], 201);
    }

    public function update(Request $request, $id)
    {
        $method = PaymentMethod::findOrFail($id);
        $data = $request->validate([
            'code' => 'sometimes|string|max:40|unique:payment_methods,code,' . $id,
            'name' => 'sometimes|string|max:120',
            'description' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);
        $method->update($data);
        return response()->json(['message' => 'Payment method updated.', 'method' => $method]);
    }

    public function destroy($id)
    {
        PaymentMethod::findOrFail($id)->delete();
        return response()->json(['message' => 'Payment method deleted.']);
    }
}
