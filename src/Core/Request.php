<?php
/**
 * HTTP Request Wrapper
 */

declare(strict_types=1);

namespace ProgressiveBar\Core;

class Request
{
    private string $method;
    private string $uri;
    private array $headers;
    private array $query;
    private array $body;
    private array $attributes = [];
    private ?string $rawBody = null;

    public function __construct(
        string $method,
        string $uri,
        array $headers = [],
        array $query = [],
        array $body = []
    ) {
        $this->method = strtoupper($method);
        $this->uri = $uri;
        $this->headers = $headers;
        $this->query = $query;
        $this->body = $body;
    }

    public static function createFromGlobals(): self
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        
        // Get headers
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $headerName = str_replace('_', '-', substr($key, 5));
                $headers[$headerName] = $value;
            }
        }
        
        // Add special headers
        if (isset($_SERVER['CONTENT_TYPE'])) {
            $headers['Content-Type'] = $_SERVER['CONTENT_TYPE'];
        }
        if (isset($_SERVER['CONTENT_LENGTH'])) {
            $headers['Content-Length'] = $_SERVER['CONTENT_LENGTH'];
        }

        // Parse body for POST/PUT/PATCH
        $body = [];
        $rawBody = file_get_contents('php://input');
        
        if ($rawBody && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $contentType = $headers['Content-Type'] ?? '';
            
            if (str_contains($contentType, 'application/json')) {
                $body = json_decode($rawBody, true) ?? [];
            } elseif (str_contains($contentType, 'application/x-www-form-urlencoded')) {
                parse_str($rawBody, $body);
            } else {
                $body = $_POST;
            }
        }

        $request = new self($method, $uri, $headers, $_GET, $body);
        $request->rawBody = $rawBody;
        
        return $request;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getHeader(string $name, $default = null): ?string
    {
        // Try case-insensitive match for stored headers
        foreach ($this->headers as $key => $value) {
            if (strcasecmp($key, $name) === 0) {
                return $value;
            }
        }
        // Also check with underscores (HTTP_* style)
        $normalizedName = strtoupper(str_replace('-', '_', $name));
        return $this->headers[$normalizedName] ?? $default;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getQuery(?string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->query;
        }
        return $this->query[$key] ?? $default;
    }

    public function getBody(?string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->body;
        }
        return $this->body[$key] ?? $default;
    }

    public function getRawBody(): ?string
    {
        return $this->rawBody;
    }

    public function input(string $key, $default = null)
    {
        return $this->body[$key] ?? $this->query[$key] ?? $default;
    }

    public function getAttribute(string $key, $default = null)
    {
        return $this->attributes[$key] ?? $default;
    }

    public function setAttribute(string $key, $value): self
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    public function withAttribute(string $key, $value): self
    {
        $clone = clone $this;
        $clone->attributes[$key] = $value;
        return $clone;
    }

    public function getClientIp(): string
    {
        $headers = ['X-Forwarded-For', 'X-Real-IP', 'Client-IP'];
        
        foreach ($headers as $header) {
            $value = $this->getHeader($header);
            if ($value) {
                // Take first IP if comma-separated
                $ips = array_map('trim', explode(',', $value));
                return $ips[0];
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    public function getBearerToken(): ?string
    {
        $auth = $this->getHeader('Authorization');
        
        if ($auth && preg_match('/Bearer\s+(.+)/i', $auth, $matches)) {
            return $matches[1];
        }
        
        return null;
    }

    public function isJson(): bool
    {
        $contentType = $this->getHeader('Content-Type', '');
        return str_contains($contentType, 'application/json');
    }

    public function expectsJson(): bool
    {
        $accept = $this->getHeader('Accept', '');
        return str_contains($accept, 'application/json') || str_contains($accept, '*/*');
    }
}
