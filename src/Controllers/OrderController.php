<?php
/**
 * Order Controller - Public endpoints
 */

declare(strict_types=1);

namespace ProgressiveBar\Controllers;

use ProgressiveBar\Core\Request;
use ProgressiveBar\Core\Response;
use ProgressiveBar\Models\Order;
use ProgressiveBar\Validators\OrderValidator;

class OrderController
{
    /**
     * POST /api/order
     * Create a new order
     */
    public function create(Request $request): void
    {
        try {
            $body = $request->getBody();
            
            // Validate input
            $validator = new OrderValidator();
            $errors = $validator->validate($body);
            
            if (!empty($errors)) {
                Response::validationError($errors);
                return;
            }
            
            // Validate table number server-side
            if (!$this->isValidTableNumber($body['tableNumber'])) {
                Response::error('Invalid table number', 400);
                return;
            }
            
            // Calculate estimated wait time based on queue
            $queueDepth = Order::getQueueDepth();
            $waitTime = min(5 + ($queueDepth * 2), 30); // 5 min base + 2 min per order, max 30 min
            
            // Create order
            $orderData = [
                'tableNumber' => strtoupper($body['tableNumber']),
                'items' => $body['items'],
                'subtotal' => (float) $body['subtotal'],
                'tax' => (float) $body['tax'],
                'total' => (float) $body['total'],
                'notes' => $body['notes'] ?? null,
                'estimatedWaitTime' => $waitTime,
            ];
            
            $orderId = Order::create($orderData);
            
            if (!$orderId) {
                Response::serverError('Failed to create order');
                return;
            }
            
            $order = Order::findById($orderId);
            
            Response::created([
                'order' => $order,
                'estimatedWaitTime' => $waitTime,
                'queuePosition' => $queueDepth + 1,
            ], 'Order placed successfully');
        } catch (\Exception $e) {
            error_log("Order creation error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            Response::serverError('Failed to create order: ' . $e->getMessage());
        }
    }

    /**
     * GET /api/order/{id}
     * Get order status
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
     * GET /api/order/wait-time
     * Get current estimated wait time
     */
    public function getWaitTime(Request $request): void
    {
        $queueDepth = Order::getQueueDepth();
        $waitTime = min(5 + ($queueDepth * 2), 30);
        
        Response::success([
            'waitTime' => $waitTime,
            'queueDepth' => $queueDepth,
        ]);
    }

    /**
     * Validate table number format
     * Format: T01 - T99
     */
    private function isValidTableNumber(string $tableNumber): bool
    {
        $pattern = '/^T\d{2}$/i';
        
        if (!preg_match($pattern, $tableNumber)) {
            return false;
        }
        
        $number = (int) substr($tableNumber, 1);
        return $number >= 1 && $number <= 99;
    }
}
