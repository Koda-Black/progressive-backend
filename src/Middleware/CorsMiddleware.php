<?php
/**
 * CORS Middleware
 * Handles Cross-Origin Resource Sharing
 */

declare(strict_types=1);

namespace ProgressiveBar\Middleware;

use ProgressiveBar\Core\Request;
use ProgressiveBar\Core\Response;
use ProgressiveBar\Core\App;

class CorsMiddleware
{
    private array $allowedOrigins;
    private array $allowedMethods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];
    private array $allowedHeaders = [
        'Content-Type',
        'Authorization',
        'X-Requested-With',
        'X-CSRF-Token',
        'Accept',
        'Origin',
    ];
    private int $maxAge = 86400; // 24 hours

    public function __construct()
    {
        $app = App::getInstance();
        $this->allowedOrigins = $app->config('security.cors_origins', ['http://localhost:3000']);
    }

    public function handle(Request $request): Request
    {
        $origin = $request->getHeader('Origin');

        // Check if origin is allowed
        if ($origin && $this->isOriginAllowed($origin)) {
            header("Access-Control-Allow-Origin: $origin");
            header('Access-Control-Allow-Credentials: true');
            header('Vary: Origin');
        }

        // Handle preflight requests
        if ($request->getMethod() === 'OPTIONS') {
            $this->handlePreflight();
            return $request->withAttribute('terminate', true);
        }

        return $request;
    }

    private function isOriginAllowed(string $origin): bool
    {
        // In development, allow localhost variations
        if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
            if (preg_match('/^https?:\/\/(localhost|127\.0\.0\.1)(:\d+)?$/', $origin)) {
                return true;
            }
        }

        return in_array($origin, $this->allowedOrigins, true);
    }

    private function handlePreflight(): void
    {
        header('Access-Control-Allow-Methods: ' . implode(', ', $this->allowedMethods));
        header('Access-Control-Allow-Headers: ' . implode(', ', $this->allowedHeaders));
        header('Access-Control-Max-Age: ' . $this->maxAge);
        header('Content-Length: 0');
        http_response_code(204);
    }
}
