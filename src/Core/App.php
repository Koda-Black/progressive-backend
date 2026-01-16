<?php
/**
 * Core Application Class
 */

declare(strict_types=1);

namespace ProgressiveBar\Core;

class App
{
    private static ?App $instance = null;
    private array $config = [];

    public function __construct()
    {
        self::$instance = $this;
        $this->loadConfig();
    }

    public static function getInstance(): App
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function loadConfig(): void
    {
        $this->config = [
            'app' => [
                'env' => $_ENV['APP_ENV'] ?? 'production',
                'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'url' => $_ENV['APP_URL'] ?? 'http://localhost:8000',
                'frontend_url' => $_ENV['FRONTEND_URL'] ?? 'http://localhost:5173',
            ],
            'db' => [
                'host' => $_ENV['DB_HOST'] ?? 'localhost',
                'port' => $_ENV['DB_PORT'] ?? '8889',
                'database' => $_ENV['DB_DATABASE'] ?? 'progressive_bar',
                'username' => $_ENV['DB_USERNAME'] ?? 'root',
                'password' => $_ENV['DB_PASSWORD'] ?? 'root',
            ],
            'jwt' => [
                'secret' => $_ENV['JWT_SECRET'] ?? 'change-this-secret',
                'expiry' => (int) ($_ENV['JWT_EXPIRY'] ?? 86400),
            ],
            'security' => [
                'cors_origins' => explode(',', $_ENV['CORS_ALLOWED_ORIGINS'] ?? 'http://localhost:5173,http://localhost:5174,http://localhost:3001'),
                'rate_limit_requests' => (int) ($_ENV['RATE_LIMIT_REQUESTS'] ?? 100),
                'rate_limit_window' => (int) ($_ENV['RATE_LIMIT_WINDOW'] ?? 60),
            ],
            'qr' => [
                'base_url' => $_ENV['QR_BASE_URL'] ?? 'http://localhost:5173',
                'table_param' => $_ENV['QR_TABLE_PARAM'] ?? 'table',
            ],
        ];
    }

    public function config(string $key, mixed $default = null): mixed
    {
        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    public function isDebug(): bool
    {
        return $this->config('app.debug', false);
    }

    public function isProduction(): bool
    {
        return $this->config('app.env') === 'production';
    }
}
