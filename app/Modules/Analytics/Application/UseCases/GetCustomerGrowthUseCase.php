<?php

namespace App\Modules\Analytics\Application\UseCases;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class GetCustomerGrowthUseCase
{
    public function execute(int $months = 6): array
    {
        $result = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $start = Carbon::now()->subMonths($i)->startOfMonth();
            $end   = Carbon::now()->subMonths($i)->endOfMonth();
            $monthKey = $start->format('Y-m');

            $newCustomers = User::where('role', 'customer')
                ->whereBetween('created_at', [$start, $end])
                ->count();

            $totalCustomers = User::where('role', 'customer')
                ->where('created_at', '<=', $end)
                ->count();

            $activeCustomers = Order::whereBetween('created_at', [$start, $end])
                ->distinct('customer_id')
                ->count('customer_id');

            $result[] = [
                'month'           => $monthKey,
                'new_customers'   => $newCustomers,
                'total_customers' => $totalCustomers,
                'active_customers'=> $activeCustomers,
            ];
        }

        return $result;
    }
}
