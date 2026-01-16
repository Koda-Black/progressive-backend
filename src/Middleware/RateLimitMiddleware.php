<?php
/**
 * Rate Limiting Middleware
 * Prevents abuse by limiting requests per IP
 */

declare(strict_types=1);

namespace ProgressiveBar\Middleware;

use ProgressiveBar\Core\Request;
use ProgressiveBar\Core\Response;
use ProgressiveBar\Core\App;

class RateLimitMiddleware
{
    private int $maxRequests;
    private int $windowSeconds;
    private string $cacheDir;

    public function __construct()
    {
        $app = App::getInstance();
        $this->maxRequests = $app->config('security.rate_limit_requests', 100);
        $this->windowSeconds = $app->config('security.rate_limit_window', 60);
        $this->cacheDir = sys_get_temp_dir() . '/progressive-bar-rate-limit';
        
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    public function handle(Request $request): Request
    {
        $clientIp = $request->getClientIp();
        $key = $this->getKey($clientIp);
        
        $rateData = $this->getRateData($key);
        
        // Clean old entries
        $now = time();
        $windowStart = $now - $this->windowSeconds;
        $rateData = array_filter($rateData, fn($timestamp) => $timestamp > $windowStart);
        
        // Check if limit exceeded
        if (count($rateData) >= $this->maxRequests) {
            $oldestRequest = min($rateData);
            $retryAfter = $oldestRequest + $this->windowSeconds - $now;
            
            Response::tooManyRequests(max(1, $retryAfter));
            return $request->withAttribute('terminate', true);
        }
        
        // Add current request
        $rateData[] = $now;
        $this->saveRateData($key, $rateData);
        
        // Add rate limit headers
        $remaining = $this->maxRequests - count($rateData);
        header("X-RateLimit-Limit: {$this->maxRequests}");
        header("X-RateLimit-Remaining: $remaining");
        header("X-RateLimit-Reset: " . ($now + $this->windowSeconds));
        
        return $request;
    }

    private function getKey(string $ip): string
    {
        // Sanitize IP for filename
        return preg_replace('/[^a-zA-Z0-9]/', '_', $ip);
    }

    private function getRateData(string $key): array
    {
        $file = $this->cacheDir . '/' . $key . '.json';
        
        if (!file_exists($file)) {
            return [];
        }
        
        $data = file_get_contents($file);
        return json_decode($data, true) ?? [];
    }

    private function saveRateData(string $key, array $data): void
    {
        $file = $this->cacheDir . '/' . $key . '.json';
        file_put_contents($file, json_encode($data), LOCK_EX);
    }
}
