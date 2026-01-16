<?php

declare(strict_types=1);

namespace ProgressiveBar\Models;

use ProgressiveBar\Core\Database;

class Admin
{
    public static function findByEmail(string $email): ?array
    {
        $sql = "SELECT * FROM admins WHERE email = :email LIMIT 1";
        $stmt = Database::query($sql, ['email' => $email]);
        $admin = $stmt->fetch();

        return $admin ?: null;
    }

    public static function findById(string $id): ?array
    {
        $sql = "SELECT * FROM admins WHERE id = :id LIMIT 1";
        $stmt = Database::query($sql, ['id' => $id]);
        $admin = $stmt->fetch();

        return $admin ?: null;
    }

    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public static function create(array $data): string
    {
        $sql = "INSERT INTO admins (email, password_hash, name, role, created_at, updated_at)
                VALUES (:email, :password_hash, :name, :role, NOW(), NOW())";
        
        Database::query($sql, [
            'email' => $data['email'],
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            'name' => $data['name'],
            'role' => $data['role'] ?? 'admin',
        ]);

        return Database::lastInsertId();
    }

    public static function format(array $row): array
    {
        return [
            'id' => (string) $row['id'],
            'email' => $row['email'],
            'name' => $row['name'],
            'role' => $row['role'],
            'createdAt' => $row['created_at'],
        ];
    }
}
