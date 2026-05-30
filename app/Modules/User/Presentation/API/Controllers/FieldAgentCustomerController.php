<?php

namespace App\Modules\User\Presentation\API\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Order;
use App\Models\User;
use App\Modules\Order\Presentation\API\Resources\OrderResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FieldAgentCustomerController extends Controller
{
    /**
     * GET /api/v1/field-agent/customers
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::where('role', 'customer')
            ->withCount('orders')
            ->with(['orders' => fn ($q) => $q->latest()->limit(1)]);

        if ($search = $request->input('search')) {
            $query->where(fn ($q) => $q
                ->where('name', 'ilike', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
            );
        }

        $perPage = min((int) $request->input('per_page', 20), 100);
        $users   = $query->orderBy('name')->paginate($perPage);

        $data = $users->map(fn (User $u) => [
            'id'            => $u->id,
            'name'          => $u->name,
            'phone'         => $u->phone,
            'address_count' => $u->addresses()->count(),
            'last_order_at' => $u->orders->first()?->created_at?->toIso8601String(),
            'total_orders'  => $u->orders_count,
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
     * POST /api/v1/field-agent/customers
     */
    public function store(Request $request): JsonResponse
    {
        $v = $request->validate([
            'name'  => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone'],
        ]);

        $customer = User::create([
            'name'      => $v['name'],
            'phone'     => $v['phone'],
            'role'      => 'customer',
            'is_active' => true,
        ]);

        return response()->json([
            'id'         => $customer->id,
            'name'       => $customer->name,
            'phone'      => $customer->phone,
            'created_at' => $customer->created_at->toIso8601String(),
        ], 201);
    }

    /**
     * GET /api/v1/field-agent/customers/{id}
     */
    public function show(int $id): JsonResponse
    {
        $customer = User::where('role', 'customer')->findOrFail($id);

        return response()->json([
            'id'            => $customer->id,
            'name'          => $customer->name,
            'phone'         => $customer->phone,
            'email'         => $customer->email,
            'is_active'     => $customer->is_active,
            'total_orders'  => $customer->orders()->count(),
            'last_order_at' => $customer->orders()->latest()->value('created_at')?->toIso8601String(),
            'created_at'    => $customer->created_at->toIso8601String(),
        ]);
    }

    /**
     * GET /api/v1/field-agent/customers/{id}/orders
     */
    public function orders(int $id): JsonResponse
    {
        $customer = User::where('role', 'customer')->findOrFail($id);

        $orders = Order::with(['items.product', 'address'])
            ->where('customer_id', $customer->id)
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json(
            OrderResource::collection($orders)->response()->getData(true)
        );
    }

    /**
     * GET /api/v1/field-agent/customers/{id}/addresses
     */
    public function addresses(int $id): JsonResponse
    {
        $customer  = User::where('role', 'customer')->findOrFail($id);
        $addresses = $customer->addresses()->orderByDesc('is_default')->get();

        $data = $addresses->map(fn (Address $a) => [
            'id'         => $a->id,
            'label'      => $a->title,
            'address'    => $a->full_address,
            'district'   => $a->district,
            'city'       => $a->city,
            'is_default' => $a->is_default,
        ]);

        return response()->json(['data' => $data]);
    }

    /**
     * POST /api/v1/field-agent/customers/{id}/addresses
     */
    public function addAddress(Request $request, int $id): JsonResponse
    {
        $customer = User::where('role', 'customer')->findOrFail($id);

        $v = $request->validate([
            'label'      => ['required', 'string', 'max:50'],
            'address'    => ['required', 'string', 'max:500'],
            'district'   => ['nullable', 'string', 'max:100'],
            'city'       => ['nullable', 'string', 'max:100'],
            'is_default' => ['boolean'],
        ]);

        if ($v['is_default'] ?? false) {
            $customer->addresses()->update(['is_default' => false]);
        }

        $address = $customer->addresses()->create([
            'title'        => $v['label'],
            'full_address' => $v['address'],
            'district'     => $v['district'] ?? null,
            'city'         => $v['city'] ?? null,
            'is_default'   => $v['is_default'] ?? false,
        ]);

        return response()->json([
            'id'         => $address->id,
            'label'      => $address->title,
            'address'    => $address->full_address,
            'district'   => $address->district,
            'city'       => $address->city,
            'is_default' => $address->is_default,
        ], 201);
    }
}
