<?php

declare(strict_types=1);

namespace ProgressiveBar\Models;

use ProgressiveBar\Core\Database;

class Order
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_PREPARING = 'preparing';
    public const STATUS_READY = 'ready';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_CANCELLED = 'cancelled';

    public static function create(array $data): string
    {
        $sql = "INSERT INTO orders (table_number, items, subtotal, tax, total, status, estimated_wait_time, notes, created_at, updated_at)
                VALUES (:table_number, :items, :subtotal, :tax, :total, :status, :estimated_wait_time, :notes, NOW(), NOW())";
        
        Database::query($sql, [
            'table_number' => $data['tableNumber'],
            'items' => json_encode($data['items']),
            'subtotal' => $data['subtotal'],
            'tax' => $data['tax'],
            'total' => $data['total'],
            'status' => self::STATUS_PENDING,
            'estimated_wait_time' => $data['estimatedWaitTime'] ?? 5,
            'notes' => $data['notes'] ?? null,
        ]);

        return Database::lastInsertId();
    }

    public static function findById(string $id): ?array
    {
        $sql = "SELECT * FROM orders WHERE id = :id LIMIT 1";
        $stmt = Database::query($sql, ['id' => $id]);
        $order = $stmt->fetch();

        return $order ? self::format($order) : null;
    }

    public static function findAll(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT * FROM orders WHERE 1=1";
        $params = [];

        if (isset($filters['status'])) {
            $sql .= " AND status = :status";
            $params['status'] = $filters['status'];
        }

        if (isset($filters['tableNumber'])) {
            $sql .= " AND table_number = :table_number";
            $params['table_number'] = $filters['tableNumber'];
        }

        $sql .= " ORDER BY created_at DESC LIMIT {$limit} OFFSET {$offset}";

        $stmt = Database::query($sql, $params);
        $orders = $stmt->fetchAll();

        return array_map([self::class, 'format'], $orders);
    }

    public static function updateStatus(string $id, string $status): bool
    {
        $validStatuses = [
            self::STATUS_PENDING,
            self::STATUS_CONFIRMED,
            self::STATUS_PREPARING,
            self::STATUS_READY,
            self::STATUS_DELIVERED,
            self::STATUS_CANCELLED,
        ];

        if (!in_array($status, $validStatuses, true)) {
            return false;
        }

        $sql = "UPDATE orders SET status = :status, updated_at = NOW() WHERE id = :id";
        Database::query($sql, ['id' => $id, 'status' => $status]);
        
        return true;
    }

    public static function countByStatus(string $status): int
    {
        $sql = "SELECT COUNT(*) as count FROM orders WHERE status = :status";
        $stmt = Database::query($sql, ['status' => $status]);
        return (int) $stmt->fetch()['count'];
    }

    public static function countTodayCompleted(): int
    {
        // Count both 'ready' and 'delivered' as completed
        $sql = "SELECT COUNT(*) as count FROM orders 
                WHERE status IN (:status1, :status2) 
                AND DATE(created_at) = CURDATE()";
        $stmt = Database::query($sql, [
            'status1' => self::STATUS_READY,
            'status2' => self::STATUS_DELIVERED
        ]);
        return (int) $stmt->fetch()['count'];
    }

    public static function getAverageWaitTime(): int
    {
        $sql = "SELECT AVG(estimated_wait_time) as avg_wait FROM orders 
                WHERE status IN ('pending', 'confirmed', 'preparing')";
        $stmt = Database::query($sql);
        $result = $stmt->fetch();
        return (int) ($result['avg_wait'] ?? 0);
    }

    public static function getQueueDepth(): int
    {
        $sql = "SELECT COUNT(*) as count FROM orders 
                WHERE status IN ('pending', 'confirmed', 'preparing')";
        $stmt = Database::query($sql);
        return (int) $stmt->fetch()['count'];
    }

    public static function getTodayRevenue(): float
    {
        $sql = "SELECT COALESCE(SUM(total), 0) as revenue FROM orders 
                WHERE status = :status AND DATE(created_at) = CURDATE()";
        $stmt = Database::query($sql, ['status' => self::STATUS_DELIVERED]);
        return (float) $stmt->fetch()['revenue'];
    }

    public static function countByDateRange(string $start, string $end): int
    {
        $sql = "SELECT COUNT(*) as count FROM orders 
                WHERE DATE(created_at) >= :start AND DATE(created_at) <= :end";
        $stmt = Database::query($sql, ['start' => $start, 'end' => $end]);
        return (int) $stmt->fetch()['count'];
    }

    public static function countByStatusAndDateRange(string $status, string $start, string $end): int
    {
        $sql = "SELECT COUNT(*) as count FROM orders 
                WHERE status = :status AND DATE(created_at) >= :start AND DATE(created_at) <= :end";
        $stmt = Database::query($sql, ['status' => $status, 'start' => $start, 'end' => $end]);
        return (int) $stmt->fetch()['count'];
    }

    public static function getRevenueByDateRange(string $start, string $end): float
    {
        $sql = "SELECT COALESCE(SUM(total), 0) as revenue FROM orders 
                WHERE status = :status AND DATE(created_at) >= :start AND DATE(created_at) <= :end";
        $stmt = Database::query($sql, ['status' => self::STATUS_DELIVERED, 'start' => $start, 'end' => $end]);
        return (float) $stmt->fetch()['revenue'];
    }

    private static function format(array $row): array
    {
        return [
            'id' => (string) $row['id'],
            'tableNumber' => $row['table_number'],
            'items' => json_decode($row['items'], true),
            'subtotal' => (float) $row['subtotal'],
            'tax' => (float) $row['tax'],
            'total' => (float) $row['total'],
            'status' => $row['status'],
            'estimatedWaitTime' => (int) $row['estimated_wait_time'],
            'notes' => $row['notes'],
            'createdAt' => $row['created_at'],
            'updatedAt' => $row['updated_at'],
        ];
    }
}
