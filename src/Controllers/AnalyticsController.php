<?php
/**
 * Analytics Controller - Dashboard analytics
 */

declare(strict_types=1);

namespace ProgressiveBar\Controllers;

use ProgressiveBar\Core\Request;
use ProgressiveBar\Core\Response;
use ProgressiveBar\Models\Order;

class AnalyticsController
{
    /**
     * GET /api/admin/analytics
     * Dashboard overview analytics
     */
    public function dashboard(Request $request): void
    {
        Response::success([
            'pendingOrders' => Order::countByStatus('pending'),
            'preparingOrders' => Order::countByStatus('preparing'),
            'completedToday' => Order::countTodayCompleted(),
            'averageWaitTime' => Order::getAverageWaitTime(),
            'totalRevenue' => Order::getTodayRevenue(),
        ]);
    }

    /**
     * GET /api/admin/analytics/orders
     * Order analytics with date range
     */
    public function orders(Request $request): void
    {
        $startDate = $request->getQuery('start');
        $endDate = $request->getQuery('end');
        
        // Default to today if no dates provided
        if (!$startDate) {
            $startDate = date('Y-m-d');
        }
        if (!$endDate) {
            $endDate = date('Y-m-d');
        }
        
        Response::success([
            'dateRange' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
            'totalOrders' => Order::countByDateRange($startDate, $endDate),
            'completedOrders' => Order::countByStatusAndDateRange('delivered', $startDate, $endDate),
            'cancelledOrders' => Order::countByStatusAndDateRange('cancelled', $startDate, $endDate),
            'revenue' => Order::getRevenueByDateRange($startDate, $endDate),
        ]);
    }
}
