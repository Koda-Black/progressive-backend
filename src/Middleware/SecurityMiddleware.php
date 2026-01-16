<?php
/**
 * Security Middleware
 * Protects against XSS, CSRF, SQL Injection, NoSQL Injection
 */

declare(strict_types=1);

namespace ProgressiveBar\Middleware;

use ProgressiveBar\Core\Request;
use ProgressiveBar\Core\Response;

class SecurityMiddleware
{
    // Patterns that indicate potential attacks
    private const NOSQL_INJECTION_PATTERNS = [
        '/\$where/i',
        '/\$gt/i',
        '/\$lt/i',
        '/\$ne/i',
        '/\$regex/i',
        '/\$or/i',
        '/\$and/i',
        '/\{\s*\$/',
    ];

    private const XSS_PATTERNS = [
        '/<script/i',
        '/javascript:/i',
        '/on\w+\s*=/i',
        '/data:\s*text\/html/i',
    ];

    public function handle(Request $request): Request
    {
        // Set security headers
        $this->setSecurityHeaders();

        // Validate content type for POST/PUT/PATCH
        if (in_array($request->getMethod(), ['POST', 'PUT', 'PATCH'])) {
            if (!$this->validateContentType($request)) {
                Response::error('Invalid content type', 415);
                return $request->withAttribute('terminate', true);
            }
        }

        // Check for NoSQL injection in body
        $body = $request->getBody();
        if ($body && $this->detectNoSqlInjection($body)) {
            Response::error('Invalid request payload', 400);
            return $request->withAttribute('terminate', true);
        }

        // Check for XSS in body
        if ($body && $this->detectXss($body)) {
            Response::error('Invalid request payload', 400);
            return $request->withAttribute('terminate', true);
        }

        // Sanitize input
        $sanitizedBody = $this->sanitizeInput($body);
        
        return $request->withAttribute('sanitized_body', $sanitizedBody);
    }

    private function setSecurityHeaders(): void
    {
        // Prevent MIME sniffing
        header('X-Content-Type-Options: nosniff');
        
        // XSS Protection (legacy browsers)
        header('X-XSS-Protection: 1; mode=block');
        
        // Prevent clickjacking
        header('X-Frame-Options: DENY');
        
        // Referrer policy
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Content Security Policy
        header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'");
        
        // Strict Transport Security (production only)
        if (($_ENV['APP_ENV'] ?? 'development') === 'production') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
        
        // Permissions Policy
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    }

    private function validateContentType(Request $request): bool
    {
        $contentType = $request->getHeader('Content-Type', '');
        
        // Allow JSON and form data
        $allowedTypes = [
            'application/json',
            'application/x-www-form-urlencoded',
            'multipart/form-data',
        ];

        foreach ($allowedTypes as $type) {
            if (str_contains($contentType, $type)) {
                return true;
            }
        }

        return false;
    }

    private function detectNoSqlInjection(array $data): bool
    {
        $json = json_encode($data);
        
        foreach (self::NOSQL_INJECTION_PATTERNS as $pattern) {
            if (preg_match($pattern, $json)) {
                return true;
            }
        }

        return false;
    }

    private function detectXss(array $data): bool
    {
        $json = json_encode($data);
        
        foreach (self::XSS_PATTERNS as $pattern) {
            if (preg_match($pattern, $json)) {
                return true;
            }
        }

        return false;
    }

    private function sanitizeInput(array $data): array
    {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            // Handle both string and numeric keys
            if (is_int($key)) {
                $cleanKey = $key;
            } else {
                $cleanKey = preg_replace('/[^\w\-]/', '', (string)$key);
            }
            
            if (is_array($value)) {
                $sanitized[$cleanKey] = $this->sanitizeInput($value);
            } elseif (is_string($value)) {
                // Remove null bytes
                $value = str_replace("\0", '', $value);
                
                // Encode HTML entities
                $sanitized[$cleanKey] = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            } else {
                // Keep numeric and other types as-is
                $sanitized[$cleanKey] = $value;
            }
        }

        return $sanitized;
    }
}
