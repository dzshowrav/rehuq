<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(['addresses' => $request->user()->addresses()->orderByDesc('is_default')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'label' => 'nullable|string|max:60',
            'recipient_name' => 'required|string|max:120',
            'phone' => 'required|string|max:40',
            'line1' => 'required|string|max:190',
            'line2' => 'nullable|string|max:190',
            'city' => 'required|string|max:90',
            'state' => 'nullable|string|max:90',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:60',
            'is_default' => 'nullable|boolean',
        ]);

        if (! empty($data['is_default'])) {
            $request->user()->addresses()->update(['is_default' => false]);
        }

        $address = $request->user()->addresses()->create(array_merge($data, [
            'is_default' => $data['is_default'] ?? $request->user()->addresses()->count() === 0,
        ]));

        return response()->json(['address' => $address, 'message' => 'Address saved.'], 201);
    }

    public function update(Request $request, $id)
    {
        $address = $request->user()->addresses()->findOrFail($id);
        $data = $request->validate([
            'label' => 'nullable|string|max:60',
            'recipient_name' => 'sometimes|string|max:120',
            'phone' => 'sometimes|string|max:40',
            'line1' => 'sometimes|string|max:190',
            'line2' => 'nullable|string|max:190',
            'city' => 'sometimes|string|max:90',
            'state' => 'nullable|string|max:90',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:60',
            'is_default' => 'nullable|boolean',
        ]);

        if (! empty($data['is_default'])) {
            $request->user()->addresses()->where('id', '!=', $id)->update(['is_default' => false]);
        }

        $address->update($data);
        return response()->json(['address' => $address, 'message' => 'Address updated.']);
    }

    public function destroy(Request $request, $id)
    {
        $request->user()->addresses()->findOrFail($id)->delete();
        return response()->json(['message' => 'Address deleted.']);
    }
}
