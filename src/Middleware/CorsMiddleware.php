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
    private bool $allowAllOrigins = false;
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
        $origins = $app->config('security.cors_origins', ['http://localhost:3000']);
        
        // Check if wildcard is set - allow all origins
        if (in_array('*', $origins, true)) {
            $this->allowAllOrigins = true;
            $this->allowedOrigins = [];
        } else {
            $this->allowedOrigins = $origins;
        }
    }

    public function handle(Request $request): Request
    {
        $origin = $request->getHeader('Origin');

        // Check if origin is allowed and set CORS headers
        if ($origin && $this->isOriginAllowed($origin)) {
            header("Access-Control-Allow-Origin: $origin");
            header('Vary: Origin');
            
            // Handle preflight requests - must be inside the allowed origin block
            if ($request->getMethod() === 'OPTIONS') {
                header('Access-Control-Allow-Methods: ' . implode(', ', $this->allowedMethods));
                header('Access-Control-Allow-Headers: ' . implode(', ', $this->allowedHeaders));
                header('Access-Control-Max-Age: ' . $this->maxAge);
                header('Content-Length: 0');
                http_response_code(204);
                exit(); // Immediately terminate for preflight
            }
        } elseif ($request->getMethod() === 'OPTIONS') {
            // Even if origin check fails, we need to handle OPTIONS gracefully
            // Log the failed origin for debugging
            error_log("CORS: Rejected origin: " . ($origin ?? 'null'));
            http_response_code(403);
            exit();
        }

        return $request;
    }

    private function isOriginAllowed(string $origin): bool
    {
        // If wildcard is enabled, allow all origins
        if ($this->allowAllOrigins) {
            return true;
        }
        
        // ALWAYS allow Vercel domains (regardless of APP_ENV)
        if (preg_match('/^https:\/\/[a-z0-9-]+\.vercel\.app$/', $origin)) {
            return true;
        }
        
        // ALWAYS allow localhost variations (for development)
        if (preg_match('/^https?:\/\/(localhost|127\.0\.0\.1)(:\d+)?$/', $origin)) {
            return true;
        }
        
        // ALWAYS allow koyeb.app domains (for testing)
        if (preg_match('/^https:\/\/[a-z0-9-]+\.koyeb\.app$/', $origin)) {
            return true;
        }

        // Check configured origins
        return in_array($origin, $this->allowedOrigins, true);
    }
}
