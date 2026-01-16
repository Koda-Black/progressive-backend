<?php
/**
 * Admin Order Controller - Order management
 */

declare(strict_types=1);

namespace ProgressiveBar\Controllers;

use ProgressiveBar\Core\Request;
use ProgressiveBar\Core\Response;
use ProgressiveBar\Models\Order;

class AdminOrderController
{
    /**
     * GET /api/admin/orders
     * List all orders with filtering
     */
    public function index(Request $request): void
    {
        $status = $request->getQuery('status');
        $tableNumber = $request->getQuery('table');
        $page = max(1, (int) $request->getQuery('page', 1));
        $limit = min(50, max(1, (int) $request->getQuery('limit', 20)));
        
        $filters = [];
        
        if ($status) {
            $filters['status'] = $status;
        }
        
        if ($tableNumber) {
            $filters['tableNumber'] = strtoupper($tableNumber);
        }
        
        $offset = ($page - 1) * $limit;
        $orders = Order::findAll($filters, $limit, $offset);
        
        Response::success([
            'orders' => $orders,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
            ],
        ]);
    }

    /**
     * GET /api/admin/orders/{id}
     */
    public function show(Request $request, array $params): void
    {
        $id = $params['id'] ?? null;
        
        if (!$id) {
            Response::error('Order ID required', 400);
            return;
        }
        
        $order = Order::findById($id);
        
        if (!$order) {
            Response::notFound('Order not found');
            return;
        }
        
        Response::success($order);
    }

    /**
     * PATCH /api/admin/orders/{id}
     * Update order status
     */
    public function update(Request $request, array $params): void
    {
        $id = $params['id'] ?? null;
        
        if (!$id) {
            Response::error('Order ID required', 400);
            return;
        }
        
        $body = $request->getBody();
        $status = $body['status'] ?? null;
        
        $validStatuses = ['pending', 'confirmed', 'preparing', 'ready', 'delivered', 'cancelled'];
        
        if (!$status || !in_array($status, $validStatuses)) {
            Response::error('Valid status required', 400);
            return;
        }
        
        $order = Order::findById($id);
        
        if (!$order) {
            Response::notFound('Order not found');
            return;
        }
        
        $updated = Order::updateStatus($id, $status);
        
        if (!$updated) {
            Response::serverError('Failed to update order');
            return;
        }
        
        $updatedOrder = Order::findById($id);
        
        Response::success($updatedOrder, 'Order status updated');
    }

    /**
     * GET /api/admin/analytics
     * Get dashboard analytics
     */
    public function analytics(Request $request): void
    {
        Response::success([
            'pendingOrders' => Order::countByStatus('pending'),
            'preparingOrders' => Order::countByStatus('preparing'),
            'completedToday' => Order::countTodayCompleted(),
            'averageWaitTime' => Order::getAverageWaitTime(),
        ]);
    }
}
