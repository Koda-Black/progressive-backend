<?php
/**
 * Order Queue Service
 * Handles wait time calculations with kitchen capacity
 */

declare(strict_types=1);

namespace ProgressiveBar\Services;

use ProgressiveBar\Core\App;
use ProgressiveBar\Models\Order;

class OrderQueueService
{
    private Order $orderModel;
    private int $maxWaitTime;
    private int $kitchenCapacity;

    public function __construct()
    {
        $app = App::getInstance();
        $this->orderModel = new Order();
        $this->maxWaitTime = $app->config('order.max_wait_time', 10);
        $this->kitchenCapacity = $app->config('order.kitchen_capacity', 5);
    }

    /**
     * Calculate estimated wait time based on queue depth and kitchen capacity
     * 
     * @return int Wait time in minutes (max 10)
     */
    public function calculateWaitTime(): int
    {
        $queueDepth = $this->getQueueDepth();
        
        if ($queueDepth === 0) {
            return 3; // Minimum prep time
        }
        
        // Calculate based on items ahead and kitchen capacity
        // Each batch of kitchenCapacity orders takes ~3-4 minutes
        $batchTime = 4; // minutes per batch
        $batches = ceil($queueDepth / $this->kitchenCapacity);
        
        $estimatedTime = (int)($batches * $batchTime);
        
        // Cap at max wait time
        return min($estimatedTime, $this->maxWaitTime);
    }

    /**
     * Get number of orders currently in queue
     */
    public function getQueueDepth(): int
    {
        return $this->orderModel->getPendingOrdersCount();
    }

    /**
     * Get position in queue for new order
     */
    public function getQueuePosition(): int
    {
        return $this->getQueueDepth() + 1;
    }

    /**
     * Get queue statistics
     */
    public function getQueueStats(): array
    {
        $recentOrders = $this->orderModel->getRecentOrders(30);
        
        $statusCounts = [
            'pending' => 0,
            'confirmed' => 0,
            'preparing' => 0,
            'ready' => 0,
            'delivered' => 0,
        ];
        
        foreach ($recentOrders as $order) {
            $status = $order['status'] ?? 'pending';
            if (isset($statusCounts[$status])) {
                $statusCounts[$status]++;
            }
        }
        
        return [
            'queueDepth' => $this->getQueueDepth(),
            'estimatedWaitTime' => $this->calculateWaitTime(),
            'statusBreakdown' => $statusCounts,
            'totalRecent' => count($recentOrders),
            'kitchenCapacity' => $this->kitchenCapacity,
        ];
    }
}
