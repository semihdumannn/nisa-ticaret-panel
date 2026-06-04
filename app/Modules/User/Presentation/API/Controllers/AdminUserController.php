<?php

namespace App\Modules\User\Presentation\API\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Modules\Order\Presentation\API\Resources\OrderResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    /**
     * GET /api/v1/admin/users
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::withCount('orders')
            ->withSum('orders as total_spent', 'total');

        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        if ($search = $request->input('search')) {
            $query->where(fn ($q) => $q
                ->where('name', 'ilike', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('email', 'ilike', "%{$search}%")
            );
        }

        $perPage = min((int) $request->input('per_page', 20), 100);
        $users   = $query->orderByDesc('created_at')->paginate($perPage);

        $data = $users->map(fn (User $u) => [
            'id'            => $u->id,
            'name'          => $u->name,
            'phone'         => $u->phone,
            'email'         => $u->email,
            'role'          => $u->role,
            'is_active'     => $u->is_active,
            'last_login_at' => $u->last_login_at?->toIso8601String(),
            'total_orders'  => $u->orders_count,
            'total_spent'   => (float) ($u->total_spent ?? 0),
            'created_at'    => $u->created_at->toIso8601String(),
        ]);

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page'    => $users->lastPage(),
                'per_page'     => $users->perPage(),
                'total'        => $users->total(),
            ],
        ]);
    }

    /**
     * PUT /api/v1/admin/users/{id}/status
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $v    = $request->validate(['is_active' => ['required', 'boolean']]);
        $user = User::findOrFail($id);

        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'Kendi hesabınızı pasife alamazsınız.'], 403);
        }

        $user->update(['is_active' => $v['is_active']]);

        return response()->json([
            'id'        => $user->id,
            'is_active' => $user->is_active,
            'message'   => $user->is_active ? 'Kullanıcı aktif edildi.' : 'Kullanıcı pasife alındı.',
        ]);
    }

    /**
     * PUT /api/v1/admin/users/{id}/role
     */
    public function updateRole(Request $request, int $id): JsonResponse
    {
        $v = $request->validate([
            'role' => ['required', 'string', 'in:customer,field_agent,delivery,admin'],
        ]);

        $user = User::findOrFail($id);

        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'Kendi rolünüzü değiştiremezsiniz.'], 403);
        }

        $user->update(['role' => $v['role']]);
        $user->syncRoles([$v['role']]);

        return response()->json([
            'id'   => $user->id,
            'role' => $user->role,
        ]);
    }

    /**
     * POST /api/v1/admin/users/{id}/toggle-block
     */
    public function toggleBlock(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'Kendinizi engelleyemezsiniz.'], 403);
        }

        $user->update(['is_active' => ! $user->is_active]);

        return response()->json([
            'id'        => $user->id,
            'is_active' => $user->is_active,
            'message'   => $user->is_active ? 'Kullanıcı engeli kaldırıldı.' : 'Kullanıcı engellendi.',
        ]);
    }

    /**
     * GET /api/v1/admin/users/{id}/orders
     */
    public function orders(int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $orders = Order::with(['items.product', 'address'])
            ->where('customer_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json(
            OrderResource::collection($orders)->response()->getData(true)
        );
    }
}
