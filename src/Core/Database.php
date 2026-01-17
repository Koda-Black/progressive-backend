<?php

declare(strict_types=1);

namespace ProgressiveBar\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $connection = null;

    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            // Check if we're in production or development
            $appEnv = $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?: 'development';
            $isProduction = $appEnv === 'production';
            
            if ($isProduction) {
                // Production: TiDB Cloud
                $host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'gateway01.eu-central-1.prod.aws.tidbcloud.com';
                $port = $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: '4000';
                $database = $_ENV['DB_DATABASE'] ?? getenv('DB_DATABASE') ?: 'progressive_bar';
                $username = $_ENV['DB_USERNAME'] ?? getenv('DB_USERNAME') ?: '';
                $password = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: '';
                $useSSL = true;
            } else {
                // Development: MAMP MySQL
                $host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost';
                $port = $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: '8889';
                $database = $_ENV['DB_DATABASE'] ?? getenv('DB_DATABASE') ?: 'progressive_bar';
                $username = $_ENV['DB_USERNAME'] ?? getenv('DB_USERNAME') ?: 'root';
                $password = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: 'root';
                $useSSL = false;
            }

            try {
                $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
                
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];
                
                // Enable SSL for production (TiDB Cloud requires SSL)
                if ($useSSL) {
                    // TiDB Cloud: Enable SSL but don't verify certificate (still encrypted)
                    $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
                    $options[PDO::MYSQL_ATTR_SSL_CA] = '';
                }
                
                self::$connection = new PDO($dsn, $username, $password, $options);
            } catch (PDOException $e) {
                throw new \RuntimeException('Database connection failed: ' . $e->getMessage());
            }
        }

        return self::$connection;
    }

    public static function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function lastInsertId(): string
    {
        return self::getConnection()->lastInsertId();
    }
}
