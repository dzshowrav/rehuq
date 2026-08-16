<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('q')) {
            $q = trim($request->input('q'));
            $query->where(fn ($q2) => $q2->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%")->orWhere('phone', 'like', "%{$q}%"));
        }
        if ($request->filled('role')) {
            $query->where('is_admin', $request->input('role') === 'admin');
        }

        $users = $query->withCount('orders')->orderByDesc('id')->paginate(15);

        return response()->json([
            'users' => $users->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'phone' => $u->phone,
                'avatar' => $u->avatar,
                'is_admin' => $u->is_admin,
                'is_active' => $u->is_active,
                'orders_count' => $u->orders_count,
                'joined' => $u->created_at?->toDateString(),
            ]),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $data = $request->validate([
            'name' => 'sometimes|string|max:120',
            'phone' => 'nullable|string|max:40',
            'is_admin' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'password' => 'nullable|string|min:6',
        ]);
        if (! empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }
        $user->update($data);
        return response()->json(['message' => 'User updated.', 'user' => $user]);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        if ($user->is_admin && User::where('is_admin', true)->count() <= 1) {
            return response()->json(['message' => 'Cannot delete the last admin account.'], 422);
        }
        $user->delete();
        return response()->json(['message' => 'User deleted.']);
    }
}
