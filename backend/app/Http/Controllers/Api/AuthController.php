<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:190|unique:users,email',
            'password' => 'required|string|min:6',
            'phone' => 'nullable|string|max:40',
        ]);

        $user = User::create($data);
        $token = $user->createToken('storefront')->plainTextToken;

        $this->mergeGuestCart($request, $user);

        return response()->json(['token' => $token, 'user' => $user->only('id', 'name', 'email', 'phone', 'avatar', 'is_admin')], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        if (! $user->is_active) {
            return response()->json(['message' => 'Your account has been disabled.'], 403);
        }

        $token = $user->createToken('storefront')->plainTextToken;
        $this->mergeGuestCart($request, $user);

        return response()->json(['token' => $token, 'user' => $this->userPayload($user)]);
    }

    public function adminLogin(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        if (! $user->is_admin) {
            return response()->json(['message' => 'This account does not have admin access.'], 403);
        }

        $token = $user->createToken('admin-panel', ['admin'])->plainTextToken;

        return response()->json(['token' => $token, 'user' => $this->userPayload($user)]);
    }

    public function me(Request $request)
    {
        return response()->json(['user' => $this->userPayload($request->user())]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out.']);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => 'sometimes|string|max:120',
            'phone' => 'nullable|string|max:40',
            'avatar' => 'nullable|string|max:500',
        ]);
        $user->update($data);
        return response()->json(['user' => $this->userPayload($user)]);
    }

    public function changePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6',
        ]);

        $user = $request->user();
        if (! Hash::check($data['current_password'], $user->password)) {
            return response()->json(['message' => 'Current password is incorrect.'], 422);
        }
        $user->update(['password' => $data['new_password']]);
        return response()->json(['message' => 'Password updated.']);
    }

    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'avatar' => $user->avatar,
            'is_admin' => $user->is_admin,
            'joined' => $user->created_at?->toDateString(),
        ];
    }

    private function mergeGuestCart(Request $request, User $user): void
    {
        $token = $request->header('X-Session-Token') ?? $request->input('session_token');
        if (! $token) {
            return;
        }
        $guestCart = Cart::where('session_token', $token)->whereNull('user_id')->first();
        if (! $guestCart) {
            return;
        }
        $userCart = Cart::firstOrCreate(['user_id' => $user->id]);
        foreach ($guestCart->items as $item) {
            $existing = $userCart->items()->where('product_id', $item->product_id)->first();
            if ($existing) {
                $existing->update(['qty' => min(99, $existing->qty + $item->qty)]);
            } else {
                $userCart->items()->create(['product_id' => $item->product_id, 'qty' => $item->qty]);
            }
        }
        $guestCart->items()->delete();
        $guestCart->delete();
    }
}
