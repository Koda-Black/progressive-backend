<?php

declare(strict_types=1);

namespace ProgressiveBar\Models;

use ProgressiveBar\Core\Database;

class MenuItem
{
    public static function findAll(array $filters = []): array
    {
        $sql = "SELECT * FROM menu_items WHERE 1=1";
        $params = [];

        if (isset($filters['category'])) {
            $sql .= " AND category = :category";
            $params['category'] = $filters['category'];
        }

        if (isset($filters['available'])) {
            $sql .= " AND available = :available";
            $params['available'] = $filters['available'] ? 1 : 0;
        }

        $sql .= " ORDER BY category, name";

        $stmt = Database::query($sql, $params);
        $items = $stmt->fetchAll();

        return array_map([self::class, 'format'], $items);
    }

    public static function findById(string $id): ?array
    {
        $sql = "SELECT * FROM menu_items WHERE id = :id LIMIT 1";
        $stmt = Database::query($sql, ['id' => $id]);
        $item = $stmt->fetch();

        return $item ? self::format($item) : null;
    }

    public static function findByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "SELECT * FROM menu_items WHERE id IN ({$placeholders})";
        
        $stmt = Database::query($sql, array_values($ids));
        $items = $stmt->fetchAll();

        return array_map([self::class, 'format'], $items);
    }

    public static function getCategories(): array
    {
        $sql = "SELECT DISTINCT category FROM menu_items ORDER BY category";
        $stmt = Database::query($sql);
        return array_column($stmt->fetchAll(), 'category');
    }

    public static function search(string $query): array
    {
        $sql = "SELECT * FROM menu_items 
                WHERE available = 1 
                AND (name LIKE :query OR description LIKE :query OR tags LIKE :query)
                ORDER BY category, name";
        
        $stmt = Database::query($sql, ['query' => "%{$query}%"]);
        $items = $stmt->fetchAll();

        return array_map([self::class, 'format'], $items);
    }

    public static function create(array $data): string
    {
        $sql = "INSERT INTO menu_items (name, description, price, category, image, available, preparation_time, tags, created_at, updated_at)
                VALUES (:name, :description, :price, :category, :image, :available, :preparation_time, :tags, NOW(), NOW())";
        
        Database::query($sql, [
            'name' => $data['name'],
            'description' => $data['description'] ?? '',
            'price' => $data['price'],
            'category' => $data['category'],
            'image' => $data['image'] ?? '',
            'available' => ($data['available'] ?? true) ? 1 : 0,
            'preparation_time' => $data['preparationTime'] ?? 5,
            'tags' => json_encode($data['tags'] ?? []),
        ]);

        return Database::lastInsertId();
    }

    public static function update(string $id, array $data): bool
    {
        $fields = [];
        $params = ['id' => $id];

        foreach (['name', 'description', 'price', 'category', 'image'] as $field) {
            if (isset($data[$field])) {
                $fields[] = "{$field} = :{$field}";
                $params[$field] = $data[$field];
            }
        }

        if (isset($data['available'])) {
            $fields[] = "available = :available";
            $params['available'] = $data['available'] ? 1 : 0;
        }

        if (isset($data['preparationTime'])) {
            $fields[] = "preparation_time = :preparation_time";
            $params['preparation_time'] = $data['preparationTime'];
        }

        if (isset($data['tags'])) {
            $fields[] = "tags = :tags";
            $params['tags'] = json_encode($data['tags']);
        }

        if (empty($fields)) {
            return false;
        }

        $fields[] = "updated_at = NOW()";
        $sql = "UPDATE menu_items SET " . implode(', ', $fields) . " WHERE id = :id";
        
        Database::query($sql, $params);
        return true;
    }

    private static function format(array $row): array
    {
        return [
            'id' => (string) $row['id'],
            'name' => $row['name'],
            'description' => $row['description'],
            'price' => (float) $row['price'],
            'category' => $row['category'],
            'image' => $row['image'],
            'available' => (bool) $row['available'],
            'preparationTime' => (int) $row['preparation_time'],
            'tags' => json_decode($row['tags'] ?? '[]', true),
            'createdAt' => $row['created_at'],
            'updatedAt' => $row['updated_at'],
        ];
    }
}
