<?php

namespace App\Modules\Analytics\Presentation\API\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Analytics\Application\DTOs\DateRangeDTO;
use App\Modules\Analytics\Application\UseCases\GetDashboardStatsUseCase;
use App\Modules\Analytics\Application\UseCases\GetOrderStatusBreakdownUseCase;
use App\Modules\Analytics\Application\UseCases\GetRevenueReportUseCase;
use App\Modules\Analytics\Application\UseCases\GetTopCustomersUseCase;
use App\Modules\Analytics\Application\UseCases\GetTopProductsUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    /**
     * GET /api/v1/admin/analytics/dashboard
     */
    public function dashboard(GetDashboardStatsUseCase $useCase): JsonResponse
    {
        $stats = $useCase->execute();

        return response()->json(['data' => [
            'today' => [
                'orders'        => $stats->todayOrders,
                'revenue'       => $stats->todayRevenue,
                'new_customers' => $stats->todayNewCustomers,
            ],
            'month' => [
                'orders'  => $stats->monthOrders,
                'revenue' => $stats->monthRevenue,
            ],
            'all_time' => [
                'customers' => $stats->totalCustomers,
                'orders'    => $stats->totalOrders,
                'revenue'   => $stats->totalRevenue,
            ],
            'active' => [
                'pending_orders'   => $stats->pendingOrders,
                'low_stock_products' => $stats->lowStockProducts,
            ],
        ]]);
    }

    /**
     * GET /api/v1/admin/analytics/revenue?from=&to=
     */
    public function revenue(Request $request, GetRevenueReportUseCase $useCase): JsonResponse
    {
        $range = DateRangeDTO::fromStrings(
            $request->query('from'),
            $request->query('to'),
        );

        return response()->json([
            'data'  => $useCase->execute($range),
            'range' => [
                'from' => $range->from->toDateString(),
                'to'   => $range->to->toDateString(),
            ],
        ]);
    }

    /**
     * GET /api/v1/admin/analytics/top-products?from=&to=&limit=
     */
    public function topProducts(Request $request, GetTopProductsUseCase $useCase): JsonResponse
    {
        $range = DateRangeDTO::fromStrings(
            $request->query('from'),
            $request->query('to'),
        );
        $limit = min((int) ($request->query('limit', 10)), 50);

        return response()->json(['data' => $useCase->execute($range, $limit)]);
    }

    /**
     * GET /api/v1/admin/analytics/top-customers?from=&to=&limit=
     */
    public function topCustomers(Request $request, GetTopCustomersUseCase $useCase): JsonResponse
    {
        $range = DateRangeDTO::fromStrings(
            $request->query('from'),
            $request->query('to'),
        );
        $limit = min((int) ($request->query('limit', 10)), 50);

        return response()->json(['data' => $useCase->execute($range, $limit)]);
    }

    /**
     * GET /api/v1/admin/analytics/order-statuses?from=&to=
     */
    public function orderStatuses(Request $request, GetOrderStatusBreakdownUseCase $useCase): JsonResponse
    {
        $range = $request->hasAny(['from', 'to'])
            ? DateRangeDTO::fromStrings($request->query('from'), $request->query('to'))
            : null;

        return response()->json(['data' => $useCase->execute($range)]);
    }
}
